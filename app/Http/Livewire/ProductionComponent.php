<?php

namespace App\Http\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ProductionComponent extends Component
{
    public array $data;
    public ?int $selectedFinishedGoodId = null;
    public $quantityToProduce = 1;
    public bool $isNewItemModalOpen = false;
    public string $newItemName = '', /* $newItemCategory = '', */ $newItemCode = ''; // Hapus category
    public ?int $selectedRawMaterialId = null;
    public $rawMaterialQuantity = 1;

    public ?int $editingBomItemId = null;
    public $editingBomItemQuantity = 1;

    // Tambahkan properti harga
    public $newItemHargaBeli = null;
    public $newItemHargaJual = null;

    public function mount(): void
    {
        if (Gate::denies('manage-production')) {
            abort(403);
        }
        $this->data = ['title' => 'Produksi Barang Jadi', 'urlPath' => 'production'];
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

        if($this->selectedFinishedGoodId){ // Tambah pengecekan null
            $finishedGood = Item::find($this->selectedFinishedGoodId);
            if ($finishedGood) { // Pastikan item ditemukan
                 $finishedGood->bomRawMaterials()->updateExistingPivot($itemId, [
                    'quantity_required' => $this->editingBomItemQuantity
                ]);
            }
        }
        $this->cancelEditBomItem();
    }

    public function saveNewFinishedGood(): void
    {
        $this->validate([
            'newItemName' => 'required|string|max:255|unique:items,name',
            // 'newItemCategory' => 'nullable|string|max:255', // Hapus category
            'newItemCode' => 'nullable|string|max:50|unique:items,code',
            'newItemHargaBeli' => 'nullable|numeric|min:0', // Tambah validasi harga
            'newItemHargaJual' => 'nullable|numeric|min:0', // Tambah validasi harga
        ]);
        $newItem = Item::create([
            'name' => $this->newItemName,
            // 'category' => $this->newItemCategory, // Hapus category
            'code' => $this->newItemCode,
            'item_type' => 'barang_jadi',
            'harga_beli' => !empty($this->newItemHargaBeli) ? (float)$this->newItemHargaBeli : null, // Simpan harga
            'harga_jual' => !empty($this->newItemHargaJual) ? (float)$this->newItemHargaJual : null, // Simpan harga
            'quantity' => 0,
            'status' => 'out', // Status out karena quantity 0
        ]);
        $this->isNewItemModalOpen = false;
        // Hapus category, tambah harga
        $this->reset(['newItemName', /* 'newItemCategory', */ 'newItemCode', 'newItemHargaBeli', 'newItemHargaJual']);
        $this->selectedFinishedGoodId = $newItem->id;
        $this->dispatch('toast', status: 'success', message: 'Barang jadi baru berhasil dibuat.');
    }

    public function addMaterialToBom(): void
    {
        $this->validate([
            'selectedRawMaterialId' => 'required|exists:items,id',
            'rawMaterialQuantity' => 'required|integer|min:1',
        ]);

        if($this->selectedFinishedGoodId){ // Tambah pengecekan null
            $finishedGood = Item::find($this->selectedFinishedGoodId);
             if ($finishedGood) { // Pastikan item ditemukan
                $finishedGood->bomRawMaterials()->syncWithoutDetaching([
                    $this->selectedRawMaterialId => ['quantity_required' => $this->rawMaterialQuantity]
                ]);
            }
        }

        $this->reset(['selectedRawMaterialId', 'rawMaterialQuantity']);
        $this->rawMaterialQuantity = 1;
    }

    public function removeMaterialFromBom($rawMaterialId): void
    {
         if($this->selectedFinishedGoodId){ // Tambah pengecekan null
            $finishedGood = Item::find($this->selectedFinishedGoodId);
            if ($finishedGood) { // Pastikan item ditemukan
                $finishedGood->bomRawMaterials()->detach($rawMaterialId);
            }
        }
    }

    public function produce(): void
    {
        try {
            $this->validate(
                [
                    'selectedFinishedGoodId' => 'required|exists:items,id',
                    'quantityToProduce' => 'required|integer|min:1',
                ],
                [
                    'quantityToProduce.required' => 'Jumlah produksi tidak boleh kosong.',
                    'quantityToProduce.min' => 'Jumlah produksi minimal harus 1.',
                ]
            );
        } catch (ValidationException $e) {
            $this->dispatch('toast', status: 'failed', message: $e->validator->errors()->first());
            return;
        }

        try {
            DB::transaction(function () {
                $finishedGood = Item::with('bomRawMaterials')->find($this->selectedFinishedGoodId);
                if (!$finishedGood) { // Handle jika item tidak ditemukan
                    throw new \Exception('Barang Jadi tidak ditemukan.');
                }
                if ($finishedGood->bomRawMaterials->isEmpty()) {
                    throw new \Exception('Barang Jadi ini tidak memiliki resep (Bill of Materials).');
                }

                foreach ($finishedGood->bomRawMaterials as $rawMaterial) {
                    $quantityNeeded = $rawMaterial->pivot->quantity_required * $this->quantityToProduce;
                    $rawMaterial->decreaseStock($quantityNeeded);

                    Transaction::create([
                        'item_id' => $rawMaterial->id,
                        'type' => 'produksi_terpakai', // Gunakan nama tipe baru
                        'quantity' => $quantityNeeded,
                        'description' => 'Digunakan untuk produksi ' . $finishedGood->name,
                    ]);
                }

                $finishedGood->increaseStock($this->quantityToProduce);

                Transaction::create([
                    'item_id' => $finishedGood->id,
                    'type' => 'produksi_jadi', // Gunakan nama tipe baru
                    'quantity' => $this->quantityToProduce,
                    'description' => 'Hasil produksi',
                ]);
            });

            Cache::flush();
            $this->dispatch('toast', status: 'success', message: 'Produksi berhasil dicatat.');
            $this->reset(['selectedFinishedGoodId', 'quantityToProduce']);
            $this->quantityToProduce = 1;
        } catch (\Exception $e) {
            $this->dispatch('toast', status: 'failed', message: $e->getMessage());
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