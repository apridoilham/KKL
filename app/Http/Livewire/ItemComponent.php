<?php

namespace App\Http\Livewire;

use App\Models\Item;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
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
    public ?string $code = null, $name = null;
    public $harga_beli = null, $harga_jual = null;
    public int $stok_minimum = 0;
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
        $title = match ($this->filterType) {
            'barang_mentah' => 'Manajemen Bahan Mentah',
            'barang_jadi' => 'Manajemen Barang Jadi',
            default => 'Manajemen Semua Barang',
        };
        $this->data = ['title' => $title, 'urlPath' => 'item'];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function resetInputFields(): void
    {
        $this->reset(['id', 'code', 'name', 'harga_beli', 'harga_jual', 'stok_minimum']);
    }

    public function create(): void
    {
        Gate::authorize('manage-items');
        $this->resetInputFields();
        $this->stok_minimum = 0;
        $this->item_type = $this->filterType ?? 'barang_mentah';
        $this->isModalOpen = true;
    }

    public function store(): void
    {
        Gate::authorize('manage-items');

        $validatedData = $this->validate([
            'name' => 'required|string|max:255',
            'code' => ['nullable','string','max:50', Rule::unique('items')->ignore($this->id)],
            'harga_beli' => 'nullable|numeric|min:0',
            'harga_jual' => 'nullable|numeric|min:0',
            'stok_minimum' => 'required|integer|min:0',
        ], [
            'harga_beli.numeric' => 'Harga beli harus berupa angka.',
            'harga_beli.min' => 'Harga beli tidak boleh negatif.',
            'harga_jual.numeric' => 'Harga jual harus berupa angka.',
            'harga_jual.min' => 'Harga jual tidak boleh negatif.',
            'stok_minimum.required' => 'Stok minimum wajib diisi (minimal 0).',
            'stok_minimum.integer' => 'Stok minimum harus berupa angka.',
            'stok_minimum.min' => 'Stok minimum tidak boleh negatif.',
        ]);

        $dataToSave = $validatedData;
        $dataToSave['item_type'] = $this->item_type;

        $dataToSave['harga_beli'] = !empty($this->harga_beli) ? (float)$this->harga_beli : null;
        $dataToSave['harga_jual'] = !empty($this->harga_jual) ? (float)$this->harga_jual : null;

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
        $this->name = $item->name;
        $this->harga_beli = $item->harga_beli;
        $this->harga_jual = $item->harga_jual;
        $this->stok_minimum = $item->stok_minimum;
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
                ->orWhere('name', 'like', '%' . $this->search . '%'));

        if ($this->filterType) {
            $itemsQuery->where('item_type', $this->filterType);
        }

        $items = $itemsQuery->latest()->paginate($this->perPage);

        return view('livewire.item', ['items' => $items])->layout('components.layouts.app', ['data' => $this->data]);
    }
}