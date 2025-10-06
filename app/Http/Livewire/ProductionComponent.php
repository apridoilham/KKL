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
    public $finishedGoods = [];
    public $allRawMaterials = [];
    public $bom;

    public ?int $selectedFinishedGoodId = null;
    public int $quantityToProduce = 1;

    // Properti untuk modal Buat Barang Jadi
    public bool $isNewItemModalOpen = false;
    public string $newItemName = '', $newItemCategory = '', $newItemCode = '';

    // Properti untuk form tambah resep
    public ?int $selectedRawMaterialId = null;
    public $rawMaterialQuantity = 1;

    public function mount(): void
    {
        if (Gate::denies('manage-production')) {
            abort(403);
        }
        $this->data = ['title' => 'Produksi Barang Jadi', 'urlPath' => 'production'];
        $this->loadFinishedGoods();
        $this->allRawMaterials = Item::where('item_type', 'barang_mentah')->orderBy('name')->get();
        $this->bom = collect();
    }

    public function loadFinishedGoods(): void
    {
        $this->finishedGoods = Item::where('item_type', 'barang_jadi')->orderBy('name')->get();
    }

    public function updatedSelectedFinishedGoodId($id): void
    {
        if ($id) {
            $this->loadBom();
        } else {
            $this->bom = collect();
        }
    }
    
    public function loadBom(): void
    {
        if ($this->selectedFinishedGoodId) {
            $finishedGood = Item::with('bomRawMaterials')->find($this->selectedFinishedGoodId);
            $this->bom = $finishedGood->bomRawMaterials;
        }
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
        $this->loadFinishedGoods();
        
        $this->selectedFinishedGoodId = $newItem->id;
        $this->updatedSelectedFinishedGoodId($newItem->id);
        
        $this->dispatch('toast', ['status' => 'success', 'message' => 'Barang jadi baru berhasil dibuat.']);
    }

    public function addMaterialToBom(): void
    {
        $this->validate([
            'selectedRawMaterialId' => 'required|exists:items,id',
            'rawMaterialQuantity' => 'required|numeric|min:0.01',
        ]);
        $finishedGood = Item::find($this->selectedFinishedGoodId);
        $finishedGood->bomRawMaterials()->syncWithoutDetaching([
            $this->selectedRawMaterialId => ['quantity_required' => $this->rawMaterialQuantity]
        ]);
        $this->loadBom();
        $this->reset(['selectedRawMaterialId', 'rawMaterialQuantity']);
        $this->rawMaterialQuantity = 1;
    }

    public function removeMaterialFromBom($rawMaterialId): void
    {
        $finishedGood = Item::find($this->selectedFinishedGoodId);
        $finishedGood->bomRawMaterials()->detach($rawMaterialId);
        $this->loadBom();
    }

    public function produce(): void
    {
        $this->validate([
            'selectedFinishedGoodId' => 'required|exists:items,id',
            'quantityToProduce' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () {
                $finishedGood = Item::with('bomRawMaterials')->find($this->selectedFinishedGoodId);
                $bom = $finishedGood->bomRawMaterials;

                if ($bom->isEmpty()) {
                    throw new \Exception('Barang Jadi ini tidak memiliki resep (Bill of Materials).');
                }

                foreach ($bom as $rawMaterial) {
                    $quantityNeeded = $rawMaterial->pivot->quantity_required * $this->quantityToProduce;
                    $rawMaterial->decreaseStock($quantityNeeded);

                    Transaction::create([
                        'item_id' => $rawMaterial->id,
                        'type' => 'produksi_keluar',
                        'quantity' => $quantityNeeded,
                        'description' => 'Digunakan untuk produksi ' . $finishedGood->name,
                    ]);
                }

                $finishedGood->increaseStock($this->quantityToProduce);
                
                Transaction::create([
                    'item_id' => $finishedGood->id,
                    'type' => 'produksi_masuk',
                    'quantity' => $this->quantityToProduce,
                    'description' => 'Hasil produksi',
                ]);
            });

            $this->dispatch('toast', ['status' => 'success', 'message' => 'Produksi berhasil dicatat.']);
            $this->reset(['selectedFinishedGoodId', 'quantityToProduce']);
            $this->bom = collect();

        } catch (\Exception $e) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.production')->layout('components.layouts.app', ['data' => $this->data]);
    }
}