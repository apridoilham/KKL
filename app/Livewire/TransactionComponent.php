<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache; // Import Cache
use Illuminate\Auth\Access\AuthorizationException; // Import Exception Otorisasi
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

    public function mount(): void
    {
        $this->data = [
            'title' => 'Transaksi',
            'urlPath' => 'transaction'
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    private function loadItems(): void
    {
        if (!$this->itemsLoaded) {
            $this->items = Item::orderBy('name')->get();
            $this->itemsLoaded = true;
        }
    }

    public function resetInputFields(): void
    {
        $this->reset(['id', 'itemId', 'type', 'description']);
        $this->quantity = 1;
    }

    public function create(): void
    {
        $this->resetInputFields();
        $this->loadItems();
        $this->isModalOpen = true;
    }

    /**
     * Membersihkan cache statistik setiap ada perubahan data.
     */
    private function clearStatsCache(): void
    {
        Cache::forget('stats:total_stock');
        Cache::forget('stats:total_in');
        Cache::forget('stats:total_out');
        Cache::forget('stats:total_damaged');
    }

    public function store(TransactionService $transactionService): void
    {
        $validatedData = $this->validate([
            'itemId' => 'required|exists:items,id',
            'type' => 'required|in:in,out,damaged',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $transactionService->createTransaction([
                'item_id' => $validatedData['itemId'],
                'type' => $validatedData['type'],
                'quantity' => $validatedData['quantity'],
                'description' => $validatedData['description'],
            ]);

            $this->clearStatsCache(); // Membersihkan cache
            $this->dispatch('toast', ['status' => 'success', 'message' => 'Transaksi berhasil dibuat.']);
            $this->isModalOpen = false;
            $this->resetInputFields();

        } catch (\Exception $e) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => $e->getMessage()]);
        }
    }

    public function delete(int $id): void
    {
        try {
            $this->authorize('delete-transaction'); // Menggunakan Gate untuk otorisasi
        } catch (AuthorizationException $e) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => 'Anda tidak memiliki otorisasi.']);
            return;
        }

        $transaction = Transaction::findOrFail($id);
        $lockTime = config('inventory.transaction_lock_time', 10);
        
        if (Carbon::parse($transaction->created_at)->diffInMinutes(Carbon::now()) > $lockTime) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => "Transaksi terkunci setelah {$lockTime} menit."]);
            return;
        }

        try {
            $item = $transaction->item;
            if ($transaction->type == 'in') {
                $item->decreaseStock($transaction->quantity);
            } else {
                $item->increaseStock($transaction->quantity);
            }
            
            $transaction->delete();
            $this->clearStatsCache(); // Membersihkan cache
            $this->dispatch('toast', ['status' => 'success', 'message' => 'Transaksi dihapus, stok dikembalikan.']);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => $e->getMessage()]);
        }
    }

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