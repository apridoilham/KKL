<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionComponent extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind-custom';

    public $data;
    public $search = '';
    public $perPage = 10;
    public $page;
    public $filterType = 'all';
    public $lockedTime = 10;

    public $id, $itemId, $type, $description;
    public $quantity = 0;
    public $items = [];

    public $isModalOpen = false;
    private $itemsLoaded = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'page' => ['except' => 1],
        'filterType' => ['except' => 'all'],
    ];


    public function mount()
    {
        $this->data = [
            'title' => 'Transaksi',
            'urlPath' => 'transaction'
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    private function loadItems()
    {
        if (!$this->itemsLoaded) {
            $this->items = Item::orderBy('name')->get();
        }
        $this->itemsLoaded = true;
    }


    public function resetInputFields()
    {
        $this->reset(['id', 'itemId', 'type', 'description', 'quantity']);
    }
    public function create()
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
        $this->loadItems();
    }

    public function store()
    {
        $this->validate([
            'itemId' => 'required',
            'type' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);

        $item = Item::findOrFail($this->itemId);

        if ($this->type == 'out' || $this->type == 'damaged') {
            if ($item->quantity < $this->quantity) {
                session()->flash('dataSession', ['status' => 'failed', 'message' => 'Stok tidak mencukupi untuk transaksi ini.']);
                return;
            }
            $newStock = $item->quantity - $this->quantity;
        } else {
            $newStock = $item->quantity + $this->quantity;
        }

        Transaction::create([
            'item_id' => $this->itemId,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'description' => $this->description,
        ]);

        $item->update(['quantity' => $newStock, 'status' => $newStock < 1 ? 'out' : 'available']);
        
        session()->flash('dataSession', ['status' => 'success', 'message' => 'Transaksi berhasil dibuat.']);

        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    public function edit($id)
    {
        session()->flash('dataSession', ['status' => 'failed', 'message' => 'Mengedit transaksi dinonaktifkan untuk menjaga integritas data.']);
    }

    public function delete($id)
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('dataSession', ['status' => 'failed', 'message' => 'Anda tidak memiliki otorisasi untuk melakukan aksi ini.']);
            return;
        }

        $transaction = Transaction::findOrFail($id);
        $createdAt = Carbon::parse($transaction->created_at);
        
        if ($createdAt->diffInMinutes(Carbon::now()) > $this->lockedTime) {
            session()->flash('dataSession', ['status' => 'failed', 'message' => "Transaksi terkunci dan tidak bisa dihapus setelah {$this->lockedTime} menit."]);
            return;
        }

        $item = Item::findOrFail($transaction->item_id);

        if ($transaction->type == 'in') {
            $stock = $item->quantity - $transaction->quantity;
            if ($stock < 0) {
                session()->flash('dataSession', ['status' => 'failed', 'message' => 'Gagal! Menghapus transaksi MASUK ini akan menghasilkan stok negatif.']);
                return;
            }
        } else {
            $stock = $item->quantity + $transaction->quantity;
        }

        $item->update(['quantity' => $stock, 'status' => $stock < 1 ? 'out' : 'available']);
        $transaction->delete();
        session()->flash('dataSession', ['status' => 'success', 'message' => 'Transaksi berhasil dihapus, stok telah dikembalikan.']);
    }

    public function render()
    {
        $transactions = Transaction::with('item')
            ->where(function ($query) {
                $query->whereHas('item', function ($subQuery) {
                    $subQuery->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('category', 'like', '%'.$this->search.'%');
                })
                ->orWhere('description', 'like', '%'.$this->search.'%');
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