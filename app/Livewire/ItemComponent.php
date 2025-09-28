<?php

namespace App\Livewire;

use App\Models\Item;
use Livewire\Component;
use Livewire\WithPagination;

class ItemComponent extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind-custom';

    public $data;
    public $search = '';
    public $perPage = 10;
    public $page;

    public $id, $code, $category, $name;

    public $isModalOpen = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->data = [
            'title' => 'Manajemen Barang',
            'urlPath' => 'item'
        ];
    }
    
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function resetInputFields(){
        $this->id = '';
        $this->code = '';
        $this->category = '';
        $this->name = '';
    }
    
    public function create(){
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    public function store(){
        $this->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);

        if ($this->id) {
            $item = Item::findOrFail($this->id);
            $item->update([
                'code' => $this->code,
                'category' => $this->category,
                'name' => $this->name,
            ]);
            $message = 'Barang berhasil diperbarui.';
        } else {
            Item::create([
                'code' => $this->code,
                'category' => $this->category,
                'name' => $this->name,
                'quantity' => 0,
                'status' => 'out',
            ]);
            $message = 'Barang baru berhasil dibuat.';
        }
        
        session()->flash('dataSession', [
            'status' => 'success',
            'message' => $message
        ]);

        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    public function edit($id){
        $item = Item::findOrFail($id);
        $this->id = $item->id;
        $this->code = $item->code;
        $this->category = $item->category;
        $this->name = $item->name;
        $this->isModalOpen = true;
    }

    public function delete($id){
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

    public function render()
    {
        $items = Item::query()
            ->where('code', 'like', '%'.$this->search.'%')
            ->orWhere('category', 'like', '%'.$this->search.'%')
            ->orWhere('name', 'like', '%'.$this->search.'%')
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.item',[
            'items' => $items,
        ])->layout('components.layouts.app',['data' => $this->data]);
    }
}