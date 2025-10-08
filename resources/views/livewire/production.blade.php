<div>
    <div class="container mx-auto px-4 py-6 md:px-6">
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900">Produksi Barang Jadi</h1>
            <p class="mt-1 text-slate-500">Pilih barang jadi, definisikan resepnya, lalu catat proses produksi.</p>
        </div>

        <div class="w-full space-y-8">
            
            <div class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-800">Langkah 1: Pilih Barang Jadi</h3>
                </div>
                <div class="p-6">
                    <label for="finishedGood" class="text-sm font-medium text-slate-700">Pilih Barang Jadi yang Akan Diproduksi</label>
                    <div class="mt-2">
                        <div class="relative w-full">
                            <select wire:model.live="selectedFinishedGoodId" id="finishedGood" class="block w-full appearance-none rounded-lg border border-slate-300 bg-white py-3 px-4 text-slate-800 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" style="background-image: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 20 20%27%3e%3cpath stroke=%27%236b7280%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%271.5%27 d=%27m6 8 4 4 4-4%27/%3e%3c/svg%3e'); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em;">
                                <option value="">-- Pilih Barang --</option>
                                @foreach($finishedGoods as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} (Stok: {{ floatval($item->quantity) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            @if($selectedFinishedGoodId)
                <div class="rounded-xl border border-slate-200 bg-white">
                    <div class="border-b border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-slate-800">Langkah 2: Atur Resep & Jalankan Produksi</h3>
                    </div>

                    <div class="divide-y divide-slate-200">
                        <div class="p-6">
                            <h4 class="text-base font-semibold text-slate-700 mb-4">Resep (Bill of Materials)</h4>
                            @if($bom->isNotEmpty())
                                <ul class="mb-6 space-y-3">
                                    @foreach($bom as $material)
                                        <li class="flex items-center justify-between rounded-lg bg-slate-50 p-3 border border-slate-200">
                                            <span class="text-slate-700">{{ $material->name }}</span>
                                            
                                            @if($editingBomItemId === $material->id)
                                                <div class="flex items-center space-x-2">
                                                    <input wire:model="editingBomItemQuantity" wire:keydown.enter="saveBomItem({{ $material->id }})" type="number" step="1" class="block w-24 rounded-md border-slate-300 py-1 px-2 text-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                                                    <button wire:click="saveBomItem({{ $material->id }})" class="p-2 rounded text-green-500 hover:bg-green-100">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button wire:click="cancelEditBomItem" class="p-2 rounded text-slate-400 hover:bg-slate-200">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <div class="flex items-center space-x-4">
                                                    <span class="font-semibold text-slate-800">{{ $material->pivot->quantity_required }} unit</span>
                                                    <button wire:click="editBomItem({{ $material->id }}, {{ $material->pivot->quantity_required }})" class="p-2 rounded text-slate-400 hover:text-amber-500 hover:bg-amber-50">
                                                        <i class="fas fa-pen fa-xs"></i>
                                                    </button>
                                                    <button wire:click="removeMaterialFromBom({{ $material->id }})" class="text-slate-400 hover:text-red-500">&times;</button>
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="py-4 text-center text-sm text-slate-500">Resep masih kosong.</p>
                            @endif

                            <div class="flex items-end space-x-2 border-t border-slate-200 pt-6">
                                <div class="flex-grow">
                                    <label class="text-xs font-semibold text-slate-500">Tambah Bahan Mentah</label>
                                    <select wire:model="selectedRawMaterialId" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                        <option value="">-- Pilih Bahan --</option>
                                        @foreach($allRawMaterials as $raw)
                                            <option value="{{ $raw->id }}">{{ $raw->name }} (Stok: {{ floatval($raw->quantity) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Jumlah</label>
                                    <input wire:model="rawMaterialQuantity" type="number" step="1" class="mt-1 block w-32 rounded-lg border border-slate-300 py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                </div>
                                <button type="button" wire:click="addMaterialToBom" class="flex-shrink-0 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Tambah</button>
                            </div>
                                @error('selectedRawMaterialId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                @error('rawMaterialQuantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        @if($bom->isNotEmpty())
                            <div class="p-6 bg-slate-50">
                                <h4 class="text-base font-semibold text-slate-700 mb-4">Jalankan Produksi</h4>
                                <form wire:submit.prevent="produce">
                                    <div class="mb-6">
                                        <label for="quantity" class="text-sm font-medium text-slate-700">Jumlah yang Akan Diproduksi</label>
                                        <input wire:model.live="quantityToProduce" type="number" min="1" step="1" id="quantity" class="mt-1 block w-full rounded-lg border border-slate-300 py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                    </div>

                                    <div class="space-y-4 mb-6">
                                        <h5 class="text-sm font-semibold text-slate-800">Kalkulasi Kebutuhan Bahan:</h5>
                                        @php $canProduce = true; @endphp
                                        @foreach($bom as $material)
                                            @php
                                                $quantityToProduceSafe = is_numeric($quantityToProduce) && $quantityToProduce > 0 ? $quantityToProduce : 1;
                                                $quantityNeeded = $material->pivot->quantity_required * $quantityToProduceSafe;
                                                $hasStock = $material->quantity >= $quantityNeeded;
                                                if (!$hasStock) { $canProduce = false; }
                                            @endphp
                                            <div class="flex items-center justify-between rounded-lg p-3 {{ $hasStock ? 'bg-white border' : 'bg-red-50 border border-red-200' }}">
                                                <div>
                                                    <p class="font-semibold text-slate-800">{{ $material->name }}</p>
                                                    <p class="text-xs text-slate-500">Dibutuhkan: <span class="font-bold">{{ $quantityNeeded }}</span> / Stok: {{ $material->quantity }}</p>
                                                </div>
                                                @if(!$hasStock)
                                                    <div class="text-right">
                                                        <p class="font-bold text-sm text-red-600">Stok Kurang!</p>
                                                        <p class="text-xs text-red-500">Butuh {{ $quantityNeeded - $material->quantity }} lagi</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    @if(!$canProduce)
                                        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                                            <span class="font-medium">Peringatan!</span> Stok bahan mentah tidak mencukupi.
                                        </div>
                                    @endif

                                    <button type="submit" @if(!$canProduce) disabled @endif wire:loading.attr="disabled" wire:target="produce" class="w-full inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800 disabled:bg-slate-400 disabled:cursor-not-allowed">
                                        <span wire:loading.remove wire:target="produce"><i class="fas fa-cogs mr-2"></i> CATAT PRODUKSI SEBANYAK {{ $quantityToProduce > 0 ? $quantityToProduce : '' }} UNIT</span>
                                        <span wire:loading wire:target="produce">Memproses...</span>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="rounded-xl border-2 border-dashed border-slate-200 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-semibold text-slate-800">Mulai Proses Produksi</h3>
                    <p class="mt-1 text-sm text-slate-500">Pilih barang jadi pada panel di atas untuk mengatur resep dan mencatat produksi.</p>
                </div>
            @endif
        </div>
    </div>
</div>