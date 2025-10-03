<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
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

    // Properti form binding
    public ?int $id = null, $itemId = null;
    public ?string $type = null, $description = null;
    public int $quantity = 1;

    // Data untuk dropdown
    public $items = [];

    // Manajemen modal
    public bool $isModalOpen = false;
    private bool $itemsLoaded = false;

    // Binding ke query string URL
    protected array $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'filterType' => ['except' => 'all'],
    ];

    /**
     * Inisialisasi komponen.
     */
    public function mount(): void
    {
        $this->data = [
            'title' => 'Transaksi',
            'urlPath' => 'transaction'
        ];
    }

    /**
     * Reset halaman jika ada pencarian baru.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Memuat data item untuk dropdown, hanya sekali.
     */
    private function loadItems(): void
    {
        if (!$this->itemsLoaded) {
            $this->items = Item::orderBy('name')->get();
            $this->itemsLoaded = true;
        }
    }

    /**
     * Membersihkan field input form.
     */
    public function resetInputFields(): void
    {
        $this->reset(['id', 'itemId', 'type', 'description', 'quantity']);
        $this->quantity = 1;
    }

    /**
     * Membuka modal untuk membuat transaksi baru.
     */
    public function create(): void
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
        $this->loadItems();
    }

    /**
     * Menyimpan transaksi baru.
     */
    public function store(): void
    {
        $this->validate([
            'itemId' => 'required|exists:items,id',
            'type' => 'required|in:in,out,damaged',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string'
        ]);

        $item = Item::findOrFail($this->itemId);

        // Memindahkan logika stok ke model
        try {
            if ($this->type == 'in') {
                $item->increaseStock($this->quantity);
            } else {
                $item->decreaseStock($this->quantity);
            }
        } catch (\Exception $e) {
            session()->flash('dataSession', ['status' => 'failed', 'message' => $e->getMessage()]);
            return;
        }

        Transaction::create([
            'item_id' => $this->itemId,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'description' => $this->description,
        ]);

        session()->flash('dataSession', ['status' => 'success', 'message' => 'Transaksi berhasil dibuat.']);
        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    /**
     * Menghapus transaksi dan mengembalikan stok.
     */
    public function delete(int $id): void
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('dataSession', ['status' => 'failed', 'message' => 'Anda tidak memiliki otorisasi untuk melakukan aksi ini.']);
            return;
        }

        $transaction = Transaction::findOrFail($id);
        
        if (Carbon::parse($transaction->created_at)->diffInMinutes(Carbon::now()) > $this->lockedTime) {
            session()->flash('dataSession', ['status' => 'failed', 'message' => "Transaksi terkunci dan tidak bisa dihapus setelah {$this->lockedTime} menit."]);
            return;
        }

        $item = $transaction->item;

        // Memindahkan logika pengembalian stok ke model
        try {
            if ($transaction->type == 'in') {
                $item->decreaseStock($transaction->quantity);
            } else {
                $item->increaseStock($transaction->quantity);
            }
        } catch (\Exception $e) {
            session()->flash('dataSession', ['status' => 'failed', 'message' => $e->getMessage()]);
            return;
        }
        
        $transaction->delete();
        session()->flash('dataSession', ['status' => 'success', 'message' => 'Transaksi berhasil dihapus, stok telah dikembalikan.']);
    }

    /**
     * Merender tampilan.
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