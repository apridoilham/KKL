<?php

namespace App\Http\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ProductionComponent extends Component
{
    public array $data;
    public ?int $selectedFinishedGoodId = null;
    public $quantityToProduce = 1;
    public bool $isNewItemModalOpen = false;
    public string $newItemName = '', $newItemCategory = '', $newItemCode = '';
    public ?int $selectedRawMaterialId = null;
    public $rawMaterialQuantity = 1;

    public ?int $editingBomItemId = null;
    public $editingBomItemQuantity = 1;

    public function mount(): void
    {
        if (Gate::denies('manage-production')) {
            abort(403);
        }
        $this->data = ['title' => 'Produksi Barang Jadi', 'urlPath' => 'production'];
    }

    public function updatedQuantityToProduce($value): void
    {
        if (empty($value) || !is_numeric($value) || $value < 1) {
            $this->quantityToProduce = 1;
            $this->dispatch('toast', status: 'failed', message: 'Jumlah produksi minimal harus 1.');
        }
    }

    public function editBomItem(int $itemId, int $currentQuantity): void
    {
        $this->editingBomItemId = $itemId;
        $this->editingBomItemQuantity = $currentQuantity;
    }

    public function cancelEditBomItem(): void
    {
        $this->reset(['editingBomItemId', 'editingBomItemQuantity']);
    }

    public function saveBomItem(int $itemId): void
    {
        $this->validate([
            'editingBomItemQuantity' => 'required|integer|min:1',
        ]);

        $finishedGood = Item::find($this->selectedFinishedGoodId);
        $finishedGood->bomRawMaterials()->updateExistingPivot($itemId, [
            'quantity_required' => $this->editingBomItemQuantity
        ]);

        $this->cancelEditBomItem();
    }

    public function saveNewFinishedGood(): void
    {
        $this->validate([
            'newItemName' => 'required|string|max:255|unique:items,name',
            'newItemCategory' => 'nullable|string|max:255',
            'newItemCode' => 'nullable|string|max:50|unique:items,code',
        ]);
        $newItem = Item::create([
            'name' => $this->newItemName,
            'category' => $this->newItemCategory,
            'code' => $this->newItemCode,
            'item_type' => 'barang_jadi',
            'quantity' => 0,
            'status' => 'out',
        ]);
        $this->isNewItemModalOpen = false;
        $this->reset(['newItemName', 'newItemCategory', 'newItemCode']);
        $this->selectedFinishedGoodId = $newItem->id;
        $this->dispatch('toast', status: 'success', message: 'Barang jadi baru berhasil dibuat.');
    }

    public function addMaterialToBom(): void
    {
        $this->validate([
            'selectedRawMaterialId' => 'required|exists:items,id',
            'rawMaterialQuantity' => 'required|integer|min:1',
        ]);
        
        $finishedGood = Item::find($this->selectedFinishedGoodId);
        $finishedGood->bomRawMaterials()->syncWithoutDetaching([
            $this->selectedRawMaterialId => ['quantity_required' => $this->rawMaterialQuantity]
        ]);

        $this->reset(['selectedRawMaterialId', 'rawMaterialQuantity']);
        $this->rawMaterialQuantity = 1;
    }

    public function removeMaterialFromBom($rawMaterialId): void
    {
        $finishedGood = Item::find($this->selectedFinishedGoodId);
        $finishedGood->bomRawMaterials()->detach($rawMaterialId);
    }

    public function produce(): mixed
    {
        $this->validate([
            'selectedFinishedGoodId' => 'required|exists:items,id',
            'quantityToProduce' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () {
                $finishedGood = Item::with('bomRawMaterials')->find($this->selectedFinishedGoodId);
                if ($finishedGood->bomRawMaterials->isEmpty()) {
                    throw new \Exception('Barang Jadi ini tidak memiliki resep (Bill of Materials).');
                }

                foreach ($finishedGood->bomRawMaterials as $rawMaterial) {
                    $quantityNeeded = $rawMaterial->pivot->quantity_required * $this->quantityToProduce;
                    $rawMaterial->decreaseStock($quantityNeeded);

                    Transaction::create([
                        'item_id' => $rawMaterial->id,
                        'type' => 'keluar_terpakai',
                        'quantity' => $quantityNeeded,
                        'description' => 'Digunakan untuk produksi ' . $finishedGood->name,
                    ]);
                }

                $finishedGood->increaseStock($this->quantityToProduce);
                
                Transaction::create([
                    'item_id' => $finishedGood->id,
                    'type' => 'masuk_jadi',
                    'quantity' => $this->quantityToProduce,
                    'description' => 'Hasil produksi',
                ]);
            });

            return redirect('/production')->with('status', 'Produksi berhasil dicatat.');

        } catch (\Exception $e) {
            $this->dispatch('toast', status: 'failed', message: $e->getMessage());
            return null;
        }
    }

    public function render()
    {
        $finishedGoods = Item::where('item_type', 'barang_jadi')->orderBy('name')->get();
        $allRawMaterials = Item::where('item_type', 'barang_mentah')->orderBy('name')->get();
        
        $bom = collect();
        if ($this->selectedFinishedGoodId) {
            $item = Item::with('bomRawMaterials')->find($this->selectedFinishedGoodId);
            if ($item) {
                $bom = $item->bomRawMaterials;
            }
        }

        return view('livewire.production', [
            'finishedGoods' => $finishedGoods,
            'allRawMaterials' => $allRawMaterials,
            'bom' => $bom,
        ])->layout('components.layouts.app', ['data' => $this->data]);
    }
}