<?php

namespace App\Livewire;

use App\Models\Item;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
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
    protected array $queryString = ['search' => ['except' => ''], 'perPage' => ['except' => 10]];

    public function mount(): void
    {
        $this->data = ['title' => 'Manajemen Barang', 'urlPath' => 'item'];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function resetInputFields(): void
    {
        $this->reset(['id', 'code', 'category', 'name']);
    }

    private function clearStatsCache(): void
    {
        Cache::forget('stats:total_items');
        Cache::forget('stats:total_stock');
    }

    public function create(): void
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    public function store(): void
    {
        $validatedData = $this->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:items,code,' . $this->id,
        ]);
        $item = Item::updateOrCreate(['id' => $this->id], $validatedData);
        if (!$this->id) {
            $item->quantity = 0;
            $item->status = 'out';
            $item->save();
        }
        $this->clearStatsCache();
        $this->dispatch('toast', [
            'status' => 'success',
            'message' => $this->id ? 'Barang berhasil diperbarui.' : 'Barang baru berhasil dibuat.'
        ]);
        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    public function edit(int $id): void
    {
        $item = Item::findOrFail($id);
        $this->id = $item->id;
        $this->code = $item->code;
        $this->category = $item->category;
        $this->name = $item->name;
        $this->isModalOpen = true;
    }

    public function delete(int $id): void
    {
        try {
            $this->authorize('delete-item');
        } catch (AuthorizationException $e) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => 'Anda tidak memiliki otorisasi.']);
            return;
        }
        try {
            Item::findOrFail($id)->delete();
            $this->clearStatsCache();
            $this->dispatch('toast', ['status' => 'success', 'message' => 'Data berhasil dihapus.']);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => 'Gagal! Barang terhubung dengan transaksi.']);
        }
    }

    public function render()
    {
        $items = Item::query()
            ->where(fn($query) => $query->where('code', 'like', '%' . $this->search . '%')
                ->orWhere('category', 'like', '%' . $this->search . '%')
                ->orWhere('name', 'like', '%' . $this->search . '%'))
            ->latest()
            ->paginate($this->perPage);
        return view('livewire.item', ['items' => $items])->layout('components.layouts.app', ['data' => $this->data]);
    }
}