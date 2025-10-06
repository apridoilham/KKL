<?php

namespace App\Http\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionComponent extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind-custom';
    public array $data;
    public string $search = '';
    public int $perPage = 10;
    public string $filterType = 'all';
    public ?int $id = null, $itemId = null;
    public ?string $type = null, $description = null;
    public int $quantity = 1;
    public $items = [];
    public bool $isModalOpen = false;

    public string $filterDateType = 'all_time';
    public string $filterDate;
    public string $filterMonth;
    public string $filterYear;

    protected array $queryString = [
        'search' => ['except' => ''], 'perPage' => ['except' => 10], 'filterType' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        $this->data = ['title' => 'Transaksi', 'urlPath' => 'transaction'];
        $this->resetDateFilters(false);
        $this->loadItems();
    }

    public function resetDateFilters($resetPage = true): void
    {
        $this->filterDateType = 'all_time';
        $this->filterDate = now()->format('Y-m-d');
        $this->filterMonth = now()->format('Y-m');
        $this->filterYear = now()->format('Y');
        if ($resetPage) {
            $this->resetPage();
        }
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'filterType', 'filterDateType', 'filterDate', 'filterMonth', 'filterYear'])) {
            $this->resetPage();
        }
    }

    public function updatedType($value): void
    {
        $this->loadItems($value);
    }

    public function loadItems($type = null): void
    {
        $query = Item::query();
        if ($type === 'pembelian_masuk') {
            $query->where('item_type', 'barang_mentah');
        } elseif ($type === 'pengiriman_keluar') {
            $query->where('item_type', 'barang_jadi');
        }
        $this->items = $query->orderBy('name')->get();
    }

    public function resetInputFields(): void
    {
        $this->reset(['id', 'itemId', 'type', 'description']);
        $this->quantity = 1;
        $this->loadItems();
    }

    public function create(): void
    {
        Gate::authorize('manage-transactions');
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    private function clearStatsCache(): void
    {
        // Menghapus cache spesifik, bukan flush semua
        // Kunci cache ini harus konsisten dengan yang ada di HomeComponent
        Cache::forget('dashboard-stats-all_time-');
    }

    public function store(): void
    {
        Gate::authorize('manage-transactions');
        $this->validate([
            'itemId' => 'required|exists:items,id',
            'type' => 'required|in:pembelian_masuk,pengiriman_keluar,rusak',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string'
        ]);
        $item = Item::findOrFail($this->itemId);
        try {
            if ($this->type == 'pembelian_masuk') {
                $item->increaseStock($this->quantity);
            } else {
                $item->decreaseStock($this->quantity);
            }
            Transaction::create([
                'item_id' => $this->itemId, 'type' => $this->type,
                'quantity' => $this->quantity, 'description' => $this->description,
            ]);
            $this->clearStatsCache();
            $this->dispatch('toast', ['status' => 'success', 'message' => 'Transaksi berhasil dibuat.']);
            $this->isModalOpen = false;
            $this->resetInputFields();
        } catch (\Exception $e) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => $e->getMessage()]);
        }
    }

    public function delete(int $id): void
    {
        Gate::authorize('manage-transactions');
        $transaction = Transaction::findOrFail($id);
        $lockTime = config('inventory.transaction_lock_time', 10);
        if (in_array($transaction->type, ['produksi_masuk', 'produksi_keluar'])) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => 'Transaksi produksi tidak dapat dihapus manual.']);
            return;
        }
        if (Carbon::parse($transaction->created_at)->diffInMinutes(Carbon::now()) > $lockTime) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => "Transaksi terkunci setelah {$lockTime} menit."]);
            return;
        }
        try {
            $item = $transaction->item;
            if ($transaction->type == 'pembelian_masuk') {
                $item->decreaseStock($transaction->quantity);
            } else {
                $item->increaseStock($transaction->quantity);
            }
            $transaction->delete();
            $this->clearStatsCache();
            $this->dispatch('toast', ['status' => 'success', 'message' => 'Transaksi dihapus, stok dikembalikan.']);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $transactionsQuery = Transaction::with('item')
            ->where(fn($query) => $query->whereHas('item', fn($subQuery) => $subQuery->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('category', 'like', '%' . $this->search . '%'))
                ->orWhere('description', 'like', '%' . $this->search . '%'))
            ->when($this->filterType !== 'all', fn($query) => $query->where('type', $this->filterType));

        if ($this->filterDateType !== 'all_time') {
            switch ($this->filterDateType) {
                case 'daily':
                    $transactionsQuery->whereDate('created_at', $this->filterDate);
                    break;
                case 'monthly':
                    $date = Carbon::parse($this->filterMonth);
                    $transactionsQuery->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month);
                    break;
                case 'yearly':
                    $transactionsQuery->whereYear('created_at', $this->filterYear);
                    break;
            }
        }
        $transactions = $transactionsQuery->latest()->paginate($this->perPage);
        return view('livewire.transaction', ['transactions' => $transactions])->layout('components.layouts.app', ['data' => $this->data]);
    }
}