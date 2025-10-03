<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use App\Services\TransactionService; // Mengimpor Service Class
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionComponent extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind-custom';

    // Properti untuk data halaman
    public array $data;

    // Properti fungsionalitas tabel
    public string $search = '';
    public int $perPage = 10;
    public string $filterType = 'all';

    // Properti konfigurasi
    public int $lockedTime = 10; // Waktu dalam menit sebelum transaksi terkunci

    // Properti untuk form binding
    public ?int $id = null, $itemId = null;
    public ?string $type = null, $description = null;
    public int $quantity = 1;

    // Properti untuk data dropdown dan state management
    public $items = [];
    public bool $isModalOpen = false;
    private bool $itemsLoaded = false;

    // Mengikat properti ke query string di URL
    protected array $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'filterType' => ['except' => 'all'],
    ];

    /**
     * Metode yang dijalankan saat komponen pertama kali di-mount.
     */
    public function mount(): void
    {
        $this->data = [
            'title' => 'Transaksi',
            'urlPath' => 'transaction'
        ];
    }

    /**
     * Dijalankan setiap kali properti 'search' diperbarui.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Memuat data item untuk dropdown, dioptimalkan agar hanya berjalan sekali.
     */
    private function loadItems(): void
    {
        if (!$this->itemsLoaded) {
            $this->items = Item::orderBy('name')->get();
            $this->itemsLoaded = true;
        }
    }

    /**
     * Membersihkan semua field input pada form.
     */
    public function resetInputFields(): void
    {
        $this->reset(['id', 'itemId', 'type', 'description']);
        $this->quantity = 1;
    }

    /**
     * Menyiapkan dan membuka modal untuk membuat transaksi baru.
     */
    public function create(): void
    {
        $this->resetInputFields();
        $this->loadItems();
        $this->isModalOpen = true;
    }

    /**
     * Menyimpan transaksi baru dengan mendelegasikan logika ke TransactionService.
     */
    public function store(TransactionService $transactionService): void
    {
        $validatedData = $this->validate([
            'itemId' => 'required|exists:items,id',
            'type' => 'required|in:in,out,damaged',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            // Panggil service untuk menangani semua logika bisnis
            $transactionService->createTransaction([
                'item_id' => $validatedData['itemId'],
                'type' => $validatedData['type'],
                'quantity' => $validatedData['quantity'],
                'description' => $validatedData['description'],
            ]);

            $this->dispatch('toast', ['status' => 'success', 'message' => 'Transaksi berhasil dibuat.']);
            $this->isModalOpen = false;
            $this->resetInputFields();

        } catch (\Exception $e) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Menghapus transaksi dan mengembalikan stok secara otomatis.
     */
    public function delete(int $id): void
    {
        if (auth()->user()->role !== 'admin') {
            $this->dispatch('toast', ['status' => 'failed', 'message' => 'Anda tidak memiliki otorisasi.']);
            return;
        }

        $transaction = Transaction::findOrFail($id);
        
        if (Carbon::parse($transaction->created_at)->diffInMinutes(Carbon::now()) > $this->lockedTime) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => "Transaksi terkunci setelah {$this->lockedTime} menit."]);
            return;
        }

        $item = $transaction->item;
        try {
            if ($transaction->type == 'in') {
                $item->decreaseStock($transaction->quantity);
            } else {
                $item->increaseStock($transaction->quantity);
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => $e->getMessage()]);
            return;
        }
        
        $transaction->delete();
        $this->dispatch('toast', ['status' => 'success', 'message' => 'Transaksi dihapus, stok dikembalikan.']);
    }

    /**
     * Merender view komponen dengan data yang diperlukan.
     */
    public function render()
    {
        $transactions = Transaction::with('item')
            ->where(function ($query) {
                $query->whereHas('item', function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('category', 'like', '%' . $this->search . '%');
                })
                ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterType !== 'all', function ($query) {
                return $query->where('type', $this->filterType);
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.transaction', [
            'transactions' => $transactions,
        ])->layout('components.layouts.app', ['data' => $this->data]);
    }
}