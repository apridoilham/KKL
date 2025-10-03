<?php

namespace App\Livewire;

use App\Models\Item;
use Livewire\Component;
use Livewire\WithPagination;

class ItemComponent extends Component
{
    use WithPagination;

    // Menggunakan tema paginasi kustom
    protected $paginationTheme = 'tailwind-custom';

    // Properti untuk data halaman
    public array $data;

    // Properti untuk fungsionalitas tabel
    public string $search = '';
    public int $perPage = 10;

    // Properti untuk form binding
    public ?int $id = null;
    public ?string $code = null, $category = null, $name = null;

    // Properti untuk manajemen modal
    public bool $isModalOpen = false;

    // Mengikat properti ke query string URL
    protected array $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    /**
     * Inisialisasi komponen.
     */
    public function mount(): void
    {
        $this->data = [
            'title' => 'Manajemen Barang',
            'urlPath' => 'item'
        ];
    }

    /**
     * Mereset halaman ke 1 setiap kali pencarian diubah.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Membersihkan field input form.
     */
    public function resetInputFields(): void
    {
        $this->reset(['id', 'code', 'category', 'name']);
    }

    /**
     * Menyiapkan modal untuk membuat data baru.
     */
    public function create(): void
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    /**
     * Menyimpan data baru atau memperbarui data yang ada.
     */
    public function store(): void
    {
        $validatedData = $this->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:items,code,' . $this->id,
        ]);
        
        // Menggunakan updateOrCreate untuk menyederhanakan logika simpan/update
        $item = Item::updateOrCreate(
            ['id' => $this->id],
            $validatedData
        );

        // Saat membuat item baru, inisialisasi quantity dan status
        if (!$this->id) {
            $item->quantity = 0;
            $item->status = 'out';
            $item->save();
        }

        // Mengirim notifikasi toast ke frontend
        $this->dispatch('toast', [
            'status' => 'success',
            'message' => $this->id ? 'Barang berhasil diperbarui.' : 'Barang baru berhasil dibuat.'
        ]);

        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    /**
     * Mengisi form dengan data yang akan di-edit.
     */
    public function edit(int $id): void
    {
        $item = Item::findOrFail($id);
        $this->id = $item->id;
        $this->code = $item->code;
        $this->category = $item->category;
        $this->name = $item->name;
        $this->isModalOpen = true;
    }

    /**
     * Menghapus data barang.
     */
    public function delete(int $id): void
    {
        if (auth()->user()->role !== 'admin') {
            $this->dispatch('toast', ['status' => 'failed', 'message' => 'Anda tidak memiliki otorisasi.']);
            return;
        }

        try {
            Item::findOrFail($id)->delete();
            $this->dispatch('toast', ['status' => 'success', 'message' => 'Data berhasil dihapus.']);
        } catch (\Exception $e) {
            // Menangkap error jika item terhubung dengan transaksi
            $this->dispatch('toast', ['status' => 'failed', 'message' => 'Gagal! Barang terhubung dengan transaksi.']);
        }
    }

    /**
     * Merender tampilan komponen.
     */
    public function render()
    {
        $items = Item::query()
            // Membungkus query pencarian dalam closure untuk keamanan dan akurasi
            ->where(function ($query) {
                $query->where('code', 'like', '%' . $this->search . '%')
                    ->orWhere('category', 'like', '%' . $this->search . '%')
                    ->orWhere('name', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.item', [
            'items' => $items,
        ])->layout('components.layouts.app', ['data' => $this->data]);
    }
}