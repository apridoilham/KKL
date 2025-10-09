<?php

namespace App\Http\Livewire;

use App\Models\Item;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class ItemComponent extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind-custom';
    public array $data;
    public string $search = '';
    public int $perPage = 10;
    public ?int $id = null;
    public ?string $code = null, $category = null, $name = null;
    public bool $isModalOpen = false;
    public string $item_type = 'barang_mentah';

    public ?string $filterType = null;

    protected array $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'filterType' => ['as' => 'type', 'except' => '']
    ];

    public function mount(): void
    {
        $title = 'Manajemen Semua Barang';
        if ($this->filterType === 'barang_mentah') {
            $title = 'Manajemen Bahan Mentah';
        } elseif ($this->filterType === 'barang_jadi') {
            $title = 'Manajemen Barang Jadi';
        }
        $this->data = ['title' => $title, 'urlPath' => 'item'];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function resetInputFields(): void
    {
        $this->reset(['id', 'code', 'category', 'name']);
    }

    public function create(): void
    {
        Gate::authorize('manage-items');
        $this->resetInputFields();

        $this->item_type = $this->filterType ?? 'barang_mentah';

        $this->isModalOpen = true;
    }

    public function store(): void
    {
        Gate::authorize('manage-items');

        $validatedData = $this->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:items,code,' . $this->id,
        ]);

        $dataToSave = $validatedData;
        $dataToSave['item_type'] = $this->item_type;

        if (!$this->id) {
            $dataToSave['quantity'] = 0;
            $dataToSave['status'] = 'out';
        }

        Item::updateOrCreate(['id' => $this->id], $dataToSave);
        Cache::flush();

        $this->dispatch(
            'toast',
            status: 'success',
            message: $this->id ? 'Item berhasil diperbarui.' : 'Item baru berhasil dibuat.'
        );
        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    public function edit(int $id): void
    {
        Gate::authorize('manage-items');
        $item = Item::findOrFail($id);
        $this->id = $item->id;
        $this->code = $item->code;
        $this->category = $item->category;
        $this->name = $item->name;
        $this->item_type = $item->item_type;
        $this->isModalOpen = true;
    }

    public function delete(int $id): void
    {
        Gate::authorize('manage-items');
        try {
            Item::findOrFail($id)->delete();
            Cache::flush();
            $this->dispatch('toast', status: 'success', message: 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            $this->dispatch('toast', status: 'failed', message: 'Gagal! Item terhubung dengan transaksi.');
        }
    }

    public function render()
    {
        $itemsQuery = Item::query()
            ->where(fn ($query) => $query->where('code', 'like', '%' . $this->search . '%')
                ->orWhere('category', 'like', '%' . $this->search . '%')
                ->orWhere('name', 'like', '%' . $this->search . '%'));

        if ($this->filterType) {
            $itemsQuery->where('item_type', $this->filterType);
        }

        $items = $itemsQuery->latest()->paginate($this->perPage);

        return view('livewire.item', ['items' => $items])->layout('components.layouts.app', ['data' => $this->data]);
    }
}