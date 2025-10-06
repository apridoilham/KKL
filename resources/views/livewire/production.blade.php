<div>
    <div class="container-fluid px-4 md:px-6 py-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800">{{ $data['title'] }}</h1>
                <p class="mt-1 text-slate-600">Pilih barang jadi, definisikan resepnya, lalu catat proses produksi.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Kolom Kiri: Pemilihan Barang Jadi --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6 space-y-6">
                    <h3 class="text-xl font-bold text-slate-800 border-b border-slate-200 pb-4">Pilih / Buat Barang Jadi</h3>
                    <div>
                        <label for="finishedGood" class="block text-sm font-medium text-slate-700">Pilih Barang Jadi</label>
                        <div class="flex items-center space-x-2 mt-1">
                            <select wire:model.live="selectedFinishedGoodId" id="finishedGood" class="block w-full border border-slate-300 rounded-lg py-2 px-3 text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Pilih Barang --</option>
                                @foreach($finishedGoods as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="$set('isNewItemModalOpen', true)" class="flex-shrink-0 px-3 py-2 bg-indigo-100 text-indigo-700 rounded-lg text-sm hover:bg-indigo-200">
                                <i class="fas fa-plus"></i> Buat Baru
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Form Resep & Form Produksi --}}
            <div class="lg:col-span-2">
                @if($selectedFinishedGoodId)
                    {{-- Form untuk Membuat Resep (BOM) --}}
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                        <h3 class="text-xl font-bold text-slate-800 border-b border-slate-200 pb-4">Resep (Bill of Materials)</h3>
                        <div class="mt-4">
                            @if($bom->isNotEmpty())
                                <ul class="space-y-2 mb-4">
                                    @foreach($bom as $material)
                                        <li class="flex justify-between items-center p-2 bg-slate-50 rounded">
                                            <span>{{ $material->name }}</span>
                                            <div class="flex items-center space-x-4">
                                                <span class="font-semibold">{{ floatval($material->pivot->quantity_required) }} unit</span>
                                                <button wire:click="removeMaterialFromBom({{ $material->id }})" class="text-red-500 hover:text-red-700">&times;</button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-center text-slate-500 py-4">Resep masih kosong. Silakan tambah bahan mentah di bawah.</p>
                            @endif

                            <div class="flex items-end space-x-2 border-t pt-4">
                                <div class="flex-grow">
                                    <label class="text-xs font-semibold text-slate-500">Pilih Barang Mentah</label>
                                    <select wire:model="selectedRawMaterialId" class="mt-1 block w-full border border-slate-300 rounded-lg py-2 px-3 text-slate-700">
                                        <option value="">-- Pilih Bahan --</option>
                                        @foreach($allRawMaterials as $raw)
                                            <option value="{{ $raw->id }}">{{ $raw->name }} (Stok: {{ floatval($raw->quantity) }})</option>
                                        @endforeach
                                    </select>
                                    @error('selectedRawMaterialId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Jumlah Dibutuhkan</label>
                                    <input wire:model="rawMaterialQuantity" type="number" step="0.01" class="mt-1 block w-32 border border-slate-300 rounded-lg py-2 px-3 text-slate-700">
                                    @error('rawMaterialQuantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <button type="button" wire:click="addMaterialToBom" class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600">Tambah</button>
                            </div>
                        </div>
                    </div>

                    {{-- Form untuk Menjalankan Produksi --}}
                    @if($bom->isNotEmpty())
                        <div class="bg-white rounded-xl shadow-lg p-6">
                             <h3 class="text-xl font-bold text-slate-800 border-b border-slate-200 pb-4">Jalankan Produksi</h3>
                             <div class="mt-4">
                                <form wire:submit.prevent="produce">
                                    <div class="mb-6">
                                        <label for="quantity" class="block text-sm font-medium text-slate-700">Jumlah yang Akan Diproduksi</label>
                                        <input wire:model.live="quantityToProduce" type="number" min="1" id="quantity" class="mt-1 block w-full border border-slate-300 rounded-lg py-2 px-3 text-slate-700">
                                    </div>

                                    <div class="space-y-4 mb-6">
                                        <h4 class="text-sm font-semibold text-slate-800">Kalkulasi Kebutuhan Bahan:</h4>
                                        @php $canProduce = true; @endphp
                                        @foreach($bom as $material)
                                            @php
                                                $quantityNeeded = $material->pivot->quantity_required * ($quantityToProduce > 0 ? $quantityToProduce : 1);
                                                $hasStock = $material->quantity >= $quantityNeeded;
                                                if (!$hasStock) {
                                                    $canProduce = false;
                                                }
                                            @endphp
                                            <div class="flex justify-between items-center p-3 rounded-lg {{ $hasStock ? 'bg-slate-50' : 'bg-red-100 border border-red-200' }}">
                                                <div>
                                                    <p class="font-semibold text-slate-800">{{ $material->name }}</p>
                                                    <p class="text-xs text-slate-500">Dibutuhkan: <span class="font-bold">{{ floatval($quantityNeeded) }}</span> / Stok: {{ floatval($material->quantity) }}</p>
                                                </div>
                                                @if(!$hasStock)
                                                    <div class="text-right">
                                                        <p class="font-bold text-sm text-red-600">Stok Kurang!</p>
                                                        <p class="text-xs text-red-500">Butuh {{ floatval($quantityNeeded - $material->quantity) }} lagi</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    @if(!$canProduce)
                                        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                                            <span class="font-medium">Peringatan!</span> Stok satu atau lebih bahan mentah tidak mencukupi untuk jumlah produksi ini.
                                        </div>
                                    @endif

                                    <div class="pt-2">
                                        <button type="submit" @if(!$canProduce) disabled @endif wire:loading.attr="disabled" wire:target="produce" class="w-full inline-flex items-center justify-center px-4 py-3 border text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-300 disabled:cursor-not-allowed">
                                            <span wire:loading.remove wire:target="produce"><i class="fas fa-cogs mr-2"></i> Catat Produksi</span>
                                            <span wire:loading wire:target="produce">Memproses...</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                        <i class="fas fa-info-circle text-slate-400 text-3xl"></i>
                        <p class="mt-2 text-slate-500">Silakan pilih atau buat barang jadi terlebih dahulu.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal untuk Membuat Barang Jadi Baru --}}
    @if ($isNewItemModalOpen)
        <div x-data="{ show: @entangle('isNewItemModalOpen') }" x-show="show" x-transition.opacity.duration.300ms class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
            <div x-show="show" x-transition.scale.duration.300ms @click.away="show = false" class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
                <form wire:submit.prevent="saveNewFinishedGood">
                    <div class="p-6 bg-indigo-600 text-white flex items-center justify-between">
                        <h3 class="text-xl font-bold"><i class="fas fa-plus-circle mr-3"></i>Buat Barang Jadi Baru</h3>
                        <button type="button" @click="show = false" class="text-indigo-200 hover:text-white text-3xl">&times;</button>
                    </div>
                    <div class="p-8 space-y-6">
                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase">Nama Barang Jadi <span class="text-red-500">*</span></label>
                            <input wire:model="newItemName" type="text" class="mt-1 block w-full bg-transparent border-0 border-b-2 p-0 pb-2 focus:ring-0 focus:border-indigo-500" required>
                            @error('newItemName') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase">Kode Barang</label>
                            <input wire:model="newItemCode" type="text" class="mt-1 block w-full bg-transparent border-0 border-b-2 p-0 pb-2 focus:ring-0 focus:border-indigo-500">
                            @error('newItemCode') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase">Kategori</label>
                            <input wire:model="newItemCategory" type="text" class="mt-1 block w-full bg-transparent border-0 border-b-2 p-0 pb-2 focus:ring-0 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="p-6 bg-slate-50 rounded-b-xl flex justify-end space-x-3 border-t">
                        <button type="button" @click="show = false" class="px-4 py-2.5 border rounded-lg text-sm font-medium">Batal</button>
                        <button type="submit" class="inline-flex items-center px-4 py-2.5 border rounded-lg text-sm font-medium text-white bg-indigo-600">Simpan Barang</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>