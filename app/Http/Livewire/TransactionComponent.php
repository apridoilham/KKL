<?php

namespace App\Http\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

    public float $originalQuantity = 0;

    public string $filterDateType = 'all_time';
    public string $filterDate;
    public string $filterMonth;
    public string $filterYear;
    public string $filterSelectedMonth;
    public string $filterSelectedYear;

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
        $this->filterYear = now()->format('Y');
        $this->filterSelectedMonth = now()->format('m');
        $this->filterSelectedYear = now()->format('Y');
        $this->filterMonth = now()->format('Y-m');
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

    public function updatedFilterSelectedMonth(): void
    {
        $this->filterMonth = $this->filterSelectedYear . '-' . $this->filterSelectedMonth;
    }

    public function updatedFilterSelectedYear(): void
    {
        $this->filterMonth = $this->filterSelectedYear . '-' . $this->filterSelectedMonth;
    }

    public function updatedType($value): void
    {
        $this->loadItems($value);
    }

    public function loadItems($type = null): void
    {
        $query = Item::query();

        match ($type) {
            'masuk_mentah', 'keluar_mentah', 'rusak_mentah' => $query->where('item_type', 'barang_mentah'),
            'masuk_jadi', 'keluar_dikirim', 'rusak_jadi' => $query->where('item_type', 'barang_jadi'),
            default => null,
        };

        $this->items = $query->orderBy('name')->get();
    }

    public function resetInputFields(): void
    {
        $this->reset(['id', 'itemId', 'type', 'description', 'originalQuantity']);
        $this->quantity = 1;
        $this->loadItems();
    }

    public function create(): void
    {
        Gate::authorize('manage-transactions');
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    public function edit(int $id): void
    {
        Gate::authorize('edit-transactions');
        $transaction = Transaction::findOrFail($id);

        if (in_array($transaction->type, ['masuk_jadi', 'keluar_terpakai'])) {
            $this->dispatch('toast', status: 'failed', message: 'Transaksi produksi tidak dapat diedit.');
            return;
        }

        $this->id = $transaction->id;
        $this->itemId = $transaction->item_id;
        $this->type = $transaction->type;
        $this->quantity = $transaction->quantity;
        $this->description = $transaction->description;
        $this->originalQuantity = $transaction->quantity;
        $this->loadItems($transaction->type);
        $this->isModalOpen = true;
    }

    public function store(): void
    {
        if ($this->id) {
            Gate::authorize('edit-transactions');
        } else {
            Gate::authorize('manage-transactions');
        }

        $this->validate([
            'itemId' => 'required|exists:items,id',
            'type' => 'required|in:masuk_mentah,masuk_jadi,keluar_dikirim,keluar_mentah,rusak_mentah,rusak_jadi',
            'quantity' => 'required|numeric|min:1',
            'description' => 'nullable|string'
        ], [
            'quantity.required' => 'Kuantitas tidak boleh kosong.',
            'quantity.min' => 'Kuantitas minimal harus 1.',
            'itemId.required' => 'Anda harus memilih barang.',
        ]);

        try {
            DB::transaction(function () {
                $stockInTypes = ['masuk_mentah', 'masuk_jadi'];

                if ($this->id) {
                    $transaction = Transaction::findOrFail($this->id);
                    $oldItem = $transaction->item;

                    if (in_array($transaction->type, $stockInTypes)) {
                        $oldItem->decreaseStock($this->originalQuantity);
                    } else {
                        $oldItem->increaseStock($this->originalQuantity);
                    }

                    $newItem = Item::findOrFail($this->itemId);
                    if (in_array($this->type, $stockInTypes)) {
                        $newItem->increaseStock($this->quantity);
                    } else {
                        $newItem->decreaseStock($this->quantity);
                    }

                    $transaction->update([
                        'item_id' => $this->itemId,
                        'type' => $this->type,
                        'quantity' => $this->quantity,
                        'description' => $this->description,
                    ]);
                } else {
                    $item = Item::findOrFail($this->itemId);
                    if (in_array($this->type, $stockInTypes)) {
                        $item->increaseStock($this->quantity);
                    } else {
                        $item->decreaseStock($this->quantity);
                    }
                    Transaction::create([
                        'item_id' => $this->itemId,
                        'type' => $this->type,
                        'quantity' => $this->quantity,
                        'description' => $this->description,
                    ]);
                }
            });

            Cache::flush();
            $this->dispatch('toast', status: 'success', message: $this->id ? 'Transaksi berhasil diperbarui.' : 'Transaksi berhasil dibuat.');
            $this->isModalOpen = false;
            $this->resetInputFields();

        } catch (\Exception $e) {
            $this->dispatch('toast', status: 'failed', message: $e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        Gate::authorize('manage-transactions');
        $transaction = Transaction::findOrFail($id);

        if (in_array($transaction->type, ['masuk_jadi', 'keluar_terpakai'])) {
            $this->dispatch('toast', status: 'failed', message: 'Transaksi produksi tidak dapat dihapus manual.');
            return;
        }

        if (auth()->user()->role !== 'admin') {
            $lockTime = config('inventory.transaction_lock_time', 10);
            if (Carbon::parse($transaction->created_at)->diffInMinutes(Carbon::now()) > $lockTime) {
                $this->dispatch('toast', status: 'failed', message: "Transaksi terkunci setelah {$lockTime} menit.");
                return;
            }
        }

        try {
            DB::transaction(function () use ($transaction) {
                $item = $transaction->item;
                if (in_array($transaction->type, ['masuk_mentah', 'masuk_jadi'])) {
                    $item->decreaseStock($transaction->quantity);
                } else {
                    $item->increaseStock($transaction->quantity);
                }
                $transaction->delete();
            });

            Cache::flush();
            $this->dispatch('toast', status: 'success', message: 'Transaksi dihapus, stok dikembalikan.');
        } catch (\Exception $e) {
            $this->dispatch('toast', status: 'failed', message: $e->getMessage());
        }
    }

    public function render()
    {
        $transactionsQuery = Transaction::with('item')
            ->where(function ($query) {
                $query->whereHas('item', function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('category', 'like', '%' . $this->search . '%');
                })->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterType !== 'all', function ($query) {
                $query->where('type', $this->filterType);
            });

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