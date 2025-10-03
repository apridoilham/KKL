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
            'code' => 'nullable|string|max:50',
        ]);

        // Menggunakan updateOrCreate untuk menyederhanakan logika
        Item::updateOrCreate(
            ['id' => $this->id],
            array_merge($validatedData, [
                'quantity' => $this->id ? Item::find($this->id)->quantity : 0,
                'status' => $this->id ? Item::find($this->id)->status : 'out',
            ])
        );

        session()->flash('dataSession', [
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
            session()->flash('dataSession', [
                'status' => 'failed',
                'message' => 'Anda tidak memiliki otorisasi untuk melakukan aksi ini.'
            ]);
            return;
        }

        try {
            Item::findOrFail($id)->delete();
            session()->flash('dataSession', [
                'status' => 'success',
                'message' => 'Data berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            session()->flash('dataSession', [
                'status' => 'failed',
                'message' => 'Tidak dapat menghapus barang karena terhubung dengan transaksi lain.'
            ]);
        }
    }

    /**
     * Merender tampilan komponen.
     */
    public function render()
    {
        $items = Item::query()
            // Membungkus query pencarian dalam closure untuk keamanan
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