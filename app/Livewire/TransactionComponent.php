<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use Carbon\Carbon;
// use Illuminate\Support\Facades\Log; // Dihapus karena tidak digunakan
use Livewire\Component;
use Livewire\WithPagination;

class TransactionComponent extends Component
{
    // ... sisa kode tidak berubah
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $data;
    public $search = ''; // Variabel untuk menyimpan kata kunci pencarian
    public $perPage = 10; // Jumlah data per halaman
    public $page;
    public $filterType = 'all'; // Untuk menampilkan semua kategori data yaitu in out dan damaged
    public $pageUrl = 'item';
    public $lockedTime = 10; // Waktu untuk locked delete dan update data dalam menit

    public $id, $itemId, $type, $description;
    public $quantity = 0;
    public $items = [];

    public $isModalOpen = false;
    private $itemsLoaded = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'page' => ['except' => 1],
    ];


    public function mount()
    {
        $this->data = [
            'title' => 'Manage Transaction',
            'urlPath' => 'transaction'
        ];
    }

    public function updatedSearch()
    {
         // Reset ke halaman pertama setiap ada perubahan pada pencarian
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

    /* Konsep transaction disini adalah
        - transaction in : akan menambahkan stock yang ada di items
        - transaction out : akan mengurangi stock yang ada di items
        - trasaction damage : akan mengurangi stock yang ada di items
    
    */

    public function store()
    {
        $this->validate([
            'itemId' => 'required',
            'type' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);


        $getStock = Item::where('id', $this->itemId)->first();

        /* Cek apakah id ada atau tidak, jika kosong maka create atau insert data. Jika ada, maka update
        */
        if ($this->id == '') {
            if ($this->type == 'out' || $this->type == 'damaged') {

                /* - Jika out, maka alurnya adalah mengurangi stock yang ada di items. Jadi harapannya adalah ketika stock 0 ya tidak bisa mengeksekusi dan akan menampilkan pesan error ini
                */
                if ($getStock->quantity - $this->quantity <  0) {
                    session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'There is no stock. Add stock first'
                    ]);
                } else {
                    Transaction::create(
                        [
                            'item_id' => $this->itemId,
                            'type' => $this->type,
                            'quantity' => $this->quantity,
                            'description' => $this->description,
                        ]
                    );

                    $stockNow = $getStock->quantity - $this->quantity;


                    /* - Alurnya adalah ketika ada transaksi di tabel transaction. Maka stok di tabel items selalu ada perubahan
                        - Lalu jika ternyata $stockNow itu bernilai 0, otomatis sesuai defaultnya yaitu items statusnya harus out. Dan sebaliknya jika ada ya available
                    */
                    Item::where('id', $this->itemId)
                        ->update([
                            'quantity' => $stockNow,
                            'status' => $stockNow < 1 ? 'out' : 'available'
                        ]);
                    session()->flash('dataSession', (object) [
                        'status' => 'success',
                        'message' => 'Item created successfully'
                    ]);
                }
            }else {
                Transaction::create(
                    [
                        'item_id' => $this->itemId,
                        'type' => $this->type,
                        'quantity' => $this->quantity,
                        'description' => $this->description,
                    ]
                );

                $stockNow = $getStock->quantity + $this->quantity;

                Item::where('id', $this->itemId)
                    ->update([
                        'quantity' => $stockNow,
                        'status' => $stockNow < 1 ? 'out' : 'available'
                    ]);
                session()->flash('dataSession', (object) [
                    'status' => 'success',
                    'message' => 'Item created successfully'
                ]);
            }
        } else {
            $getQty = Transaction::where('id', $this->id)->first();

            $stock = $getStock->quantity + $getQty->quantity;

            if ($this->type == 'out' || $this->type == 'damaged') {

                if ($stock - $this->quantity <  0) {
                    session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'There is no stock. Add stock first'
                    ]);
                } else {
                    Transaction::where('id', $this->id)->update(
                        [
                            'item_id' => $this->itemId,
                            'type' => $this->type,
                            'quantity' => $this->quantity,
                            'description' => $this->description,
                        ],
                    );

                    $stockNow = $stock - $this->quantity;

                    Item::where('id', $this->itemId)
                        ->update([
                            'quantity' => $stockNow,
                            'status' => $stockNow < 1 ? 'out' : 'available'
                        ]);

                    session()->flash('dataSession', (object) [
                        'status' => 'success',
                        'message' => 'Item updated successfully'
                    ]);
                }
            } else {

                /* - Ini untuk mencegah perubahan data ketika transaction in yaitu ketika ternyata setelah diupdate stock items itu minus, akan menampilkan pesan error
                    - Karena konsepnya in itu menambah data, dan jika diupdate dia harus mengurangi data di items terlebih dahulu
                */

                if (($this->quantity + $getStock->quantity) - $getQty->quantity < 0) {
                    session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'Failed, ambiguous data!!! Make sure the stock is not minus'
                    ]);
                } else {
                    Transaction::where('id', $this->id)->update(
                        [
                            'item_id' => $this->itemId,
                            'type' => $this->type,
                            'quantity' => $this->quantity,
                            'description' => $this->description,
                        ],
                    );

                    if ($this->quantity > $getQty->quantity) {

                        $stockNow = ($this->quantity - $getQty->quantity) + $getStock->quantity;
                        Item::where('id', $this->itemId)
                            ->update([
                                'quantity' => $stockNow,
                                'status' => $stockNow < 1 ? 'out' : 'available'
                            ]);
                    } else if ($this->quantity == $getQty->quantity) {
                        Item::where('id', $this->itemId)
                            ->update([
                                'quantity' => $getStock->quantity,
                                'status' => $getStock->quantity < 1 ? 'out' : 'available'
                            ]);
                    } else {
                        $stockNow = ($getStock->quantity + $this->quantity) - $getQty->quantity;
                        Item::where('id', $this->itemId)
                            ->update([
                                'quantity' => $stockNow,
                                'status' => $stockNow < 1 ? 'out' : 'available'
                            ]);
                    }

                    session()->flash('dataSession', (object) [
                        'status' => 'success',
                        'message' => 'Item updated successfully'
                    ]);
                }
            }
        }

        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $item = Transaction::findOrFail($id);
        $createdAt = Carbon::parse($item->created_at);
        $now = Carbon::now();

        /* - Fungsi dari $createdAt->diffInMinutes($now) > $this->lockedTime adalah untuk mencegah perubahan data setelah input transactions
            - Harapannya adalah ketika data sudah terinput yaitu disini property $this->lockedTime diset ke 10, maka setelah 10 menit data tidak bisa dirubah. Dan sebaliknya jika kurang dari, maka masih bisa
            - Edit saja durasi waktunya di property $lockedTime diatas
        */
        if ($createdAt->diffInMinutes($now) > $this->lockedTime) { // 1 menit batas waktu
            session()->flash('dataSession', (object) [
                'status' => 'failed',
                'message' => "Transaction is locked and cannot be edited after $this->lockedTime minute"
            ]);
        } else {
            $this->id = $item->id;
            $this->itemId = $item->item_id;
            $this->type = $item->type;
            $this->description = $item->description;
            $this->quantity = $item->quantity;
            $this->isModalOpen = true;
            $this->loadItems();
        }
    }


    public function delete($id)
    {
        $transaction = Transaction::where('id', $id)->first();
        $createdAt = Carbon::parse($transaction->created_at);
        $now = Carbon::now();

        /* Ini sama untuk mencegah perubahan data yaitu hapus dengan pembatasan limit waktu
        */
        if ($createdAt->diffInMinutes($now) > $this->lockedTime) { // 1 menit batas waktu
            session()->flash('dataSession', (object) [
                'status' => 'failed',
                'message' => "Transaction is locked and cannot be deleted after $this->lockedTime minute"
            ]);
        } else {

            $item = Item::where('id', $transaction->item_id)->first();

            if ($transaction->type == 'out' || $transaction->type == 'damaged') {
                $stock = $item->quantity + $transaction->quantity;

                Item::where('id', $transaction->item_id)
                    ->update([
                        'quantity' => $stock,
                        'status' => $stock < 1 ? 'out' : 'available'
                    ]);

                Transaction::find($id)->delete();

                session()->flash('dataSession', (object) [
                    'status' => 'success',
                    'message' => 'Deleted successfully'
                ]);
            } else {
                $stock = $item->quantity - $transaction->quantity;

                if ($stock < 0) {
                    session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'Failed, ambiguous data!!! Make sure the stock is not minus'
                    ]);
                } else {

                    Item::where('id', $transaction->item_id)
                        ->update([
                            'quantity' => $stock,
                            'status' => $stock < 1 ? 'out' : 'available'
                        ]);
                    Transaction::find($id)->delete();

                    session()->flash('dataSession', (object) [
                        'status' => 'success',
                        'message' => 'Deleted successfully'
                    ]);
                }
            }
        }
    }


    public function render()
    {
        $transactions = Transaction::query()
            ->with('item')
            ->where(function ($query) {
                $query->whereHas('item', function ($query) {
                    $query->where('category', 'like', '%' . $this->search . '%')
                        ->orWhere('name', 'like', '%' . $this->search . '%');
                })
                    ->orWhere('type', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });

        /* - Karena ada 3 data yaitu in, out dan damage, maka filterType digunakan untuk memfilter data sesuai yang dibutuhkan. Defaultnya adalah all dimana akan menampilkan semua data
            - Jika ingin mengganti default datanya bisa ubah property $filterType diatas
        */

        if ($this->filterType !== 'all') {
            $transactions->where('type', $this->filterType);
        }

        $transactions = $transactions->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.transaction', [
            'transactions' => $transactions,
            'no' => ($transactions->currentPage() - 1) * $this->perPage + 1
        ])->layout('components.layouts.app', ['data' => $this->data]);
    }
}