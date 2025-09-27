<?php

namespace App\Livewire;

use App\Models\Item;
use Livewire\Component;
use Livewire\WithPagination;

class ItemComponent extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $data;
    public $search = ''; // Variabel untuk menyimpan kata kunci pencarian
    public $perPage = 10; // Jumlah data per halaman
    public $page;
    public $pageUrl = 'item';

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
            'title' => 'Manage Items Page',
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
            'name' => 'required',
        ]);

        Item::updateOrCreate(
            ['id' => $this->id],
            [
                'code' => $this->code,
                'category' => $this->category,
                'name' => $this->name,
            ]
        );
        
        session()->flash('dataSession', (object) [
            'status' => 'success',
            'message' => $this->id ? 'Item updated successfully.' : 'Item created successfully'
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
        // Pengecekan Role ditambahkan di sini
        if (auth()->user()->role !== 'admin') {
            session()->flash('dataSession', (object) [
                'status' => 'failed',
                'message' => 'You are not authorized to perform this action.'
            ]);
            return;
        }

        try {
            Item::findOrFail($id)->delete();
            session()->flash('dataSession', (object) [
                'status' => 'success',
                'message' => 'Data deleted successfully'
            ]);
        } catch (\Exception $e) {
            session()->flash('dataSession', (object) [
                'status' => 'failed',
                'message' => 'Cannot delete items because they are related to other transactions.'
            ]);
        
        }
    }

    public function render()
    {
        $items = Item::query()
            ->where('code', 'like', '%'.$this->search.'%')
            ->orWhere('category', 'like', '%'.$this->search.'%')
            ->orWhere('name', 'like', '%'.$this->search.'%')
            ->paginate($this->perPage);

        return view('livewire.item',[
            'items' => $items,
            'no' => ($items->currentPage() - 1) * $this->perPage + 1
        ])->layout('components.layouts.app',['data' => $this->data]);
    }
}