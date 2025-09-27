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
    // Ganti tema pagination ke kustom Tailwind
    protected $paginationTheme = 'tailwind-custom';

    public $data;
    public $search = '';
    public $perPage = 10;
    public $page;
    public $filterType = 'all';
    public $lockedTime = 10; // dalam menit

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
            'title' => 'Transactions', // Judul diperbarui
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
            $this->items = Item::all();
        }
        $this->itemsLoaded = true;
    }


    public function resetInputFields()
    {
        $this->id = '';
        $this->itemId = '';
        $this->type = '';
        $this->description = '';
        $this->quantity = 0;
    }
    public function create()
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
        $this->loadItems();
        $this->id = '';
    }

    public function store()
    {
        $this->validate([
            'itemId' => 'required',
            'type' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);

        $getStock = Item::where('id', $this->itemId)->first();

        if ($this->id == '') {
            // Logika Create
            if ($this->type == 'out' || $this->type == 'damaged') {
                if ($getStock->quantity - $this->quantity < 0) {
                    session()->flash('dataSession', ['status' => 'failed', 'message' => 'Stock is not enough for this transaction.']);
                    return;
                }
                Transaction::create([ 'item_id' => $this->itemId, 'type' => $this->type, 'quantity' => $this->quantity, 'description' => $this->description, ]);
                $stockNow = $getStock->quantity - $this->quantity;
            } else { // type 'in'
                Transaction::create([ 'item_id' => $this->itemId, 'type' => $this->type, 'quantity' => $this->quantity, 'description' => $this->description, ]);
                $stockNow = $getStock->quantity + $this->quantity;
            }
            Item::where('id', $this->itemId)->update(['quantity' => $stockNow, 'status' => $stockNow < 1 ? 'out' : 'available']);
            session()->flash('dataSession', ['status' => 'success', 'message' => 'Transaction created successfully']);
        } else {
            // Logika Update (jika diperlukan di masa depan, saat ini edit terkunci)
            session()->flash('dataSession', ['status' => 'failed', 'message' => 'Update feature is currently under review.']);
        }

        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    public function edit($id)
    {
        // Tambahan keamanan berbasis peran
        if (auth()->user()->role !== 'admin') {
            session()->flash('dataSession', ['status' => 'failed', 'message' => 'You are not authorized to perform this action.']);
            return;
        }

        $item = Transaction::findOrFail($id);
        $createdAt = Carbon::parse($item->created_at);
        $now = Carbon::now();

        if ($createdAt->diffInMinutes($now) > $this->lockedTime) {
            session()->flash('dataSession', ['status' => 'failed', 'message' => "Transaction is locked and cannot be edited after {$this->lockedTime} minutes."]);
            return;
        }
        
        session()->flash('dataSession', ['status' => 'failed', 'message' => 'Editing is currently disabled for data integrity. Please delete and create a new one if needed.']);
        // Baris di bawah dinonaktifkan untuk sementara
        // $this->id = $item->id;
        // $this->itemId = $item->item_id;
        // $this->type = $item->type;
        // $this->description = $item->description;
        // $this->quantity = $item->quantity;
        // $this->isModalOpen = true;
        // $this->loadItems();
    }


    public function delete($id)
    {
        // Tambahan keamanan berbasis peran
        if (auth()->user()->role !== 'admin') {
            session()->flash('dataSession', ['status' => 'failed', 'message' => 'You are not authorized to perform this action.']);
            return;
        }

        $transaction = Transaction::findOrFail($id);
        $createdAt = Carbon::parse($transaction->created_at);
        $now = Carbon::now();
        
        if ($createdAt->diffInMinutes($now) > $this->lockedTime) {
            session()->flash('dataSession', ['status' => 'failed', 'message' => "Transaction is locked and cannot be deleted after {$this->lockedTime} minutes."]);
            return;
        }

        $item = Item::findOrFail($transaction->item_id);

        if ($transaction->type == 'in') {
            $stock = $item->quantity - $transaction->quantity;
            if ($stock < 0) {
                session()->flash('dataSession', ['status' => 'failed', 'message' => 'Deletion failed! Deleting this IN transaction would result in negative stock.']);
                return;
            }
        } else { // out atau damaged
            $stock = $item->quantity + $transaction->quantity;
        }

        Item::where('id', $transaction->item_id)->update(['quantity' => $stock, 'status' => $stock < 1 ? 'out' : 'available']);
        $transaction->delete();
        session()->flash('dataSession', ['status' => 'success', 'message' => 'Transaction deleted successfully, stock has been restored.']);
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