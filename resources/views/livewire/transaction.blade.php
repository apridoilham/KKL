<div class="container mx-auto px-4 py-6 md:px-6">

    <div class="mb-8 flex flex-col items-start justify-between gap-y-4 md:flex-row md:items-center">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">Transaksi</h1>
            <p class="mt-1 text-slate-500">Catatan semua pergerakan inventaris dalam sistem Anda.</p>
        </div>
        @can('manage-transactions')
        <div class="flex items-center space-x-3">
            <button wire:click="create" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800">
                <i class="fas fa-plus mr-2"></i> Tambah Transaksi
            </button>
        </div>
        @endcan
    </div>
    
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 items-end">
            <div>
                <label class="text-xs font-semibold text-slate-500">Filter Tipe</label>
                <select wire:model.live="filterType" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    <option value="all">Semua Tipe</option>
                    <optgroup label="Barang Masuk">
                        <option value="masuk_mentah">Masuk (Mentah)</option>
                        <option value="masuk_jadi">Masuk (Jadi)</option>
                    </optgroup>
                    <optgroup label="Barang Keluar">
                        <option value="keluar_terpakai">Keluar (Terpakai)</option>
                        <option value="keluar_dikirim">Keluar (Dikirim - Jadi)</option>
                        <option value="keluar_mentah">Keluar (Dikirim - Mentah)</option>
                    </optgroup>
                    <option value="rusak">Rusak</option>
                </select>
            </div>
             <div>
                <label class="text-xs font-semibold text-slate-500">Filter Waktu</label>
                <select wire:model.live="filterDateType" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    <option value="all_time">Semua Waktu</option>
                    <option value="daily">Harian</option>
                    <option value="monthly">Bulanan</option>
                    <option value="yearly">Tahunan</option>
                </select>
            </div>
            <div>
                @if($filterDateType === 'daily')
                    <label class="text-xs font-semibold text-slate-500">Pilih Tanggal</label>
                    <input wire:model.blur="filterDate" type="date" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                @elseif($filterDateType === 'monthly')
                    <label class="text-xs font-semibold text-slate-500">Pilih Bulan</label>
                    <input wire:model.blur="filterMonth" type="month" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                @elseif($filterDateType === 'yearly')
                    <label class="text-xs font-semibold text-slate-500">Masukkan Tahun</label>
                    <input wire:model.blur="filterYear" type="number" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" placeholder="Tahun...">
                @endif
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Pencarian</label>
                <input wire:model.live.debounce.300ms="search" type="text" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2 px-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" placeholder="Cari nama barang/deskripsi...">
            </div>
        </div>
    </div>
    
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-medium">Nama Barang</th>
                        <th scope="col" class="px-6 py-4 font-medium">Tipe Transaksi</th>
                        <th scope="col" class="px-6 py-4 font-medium">Kuantitas</th>
                        <th scope="col" class="px-6 py-4 font-medium">Deskripsi</th>
                        <th scope="col" class="px-6 py-4 font-medium">Tanggal</th>
                        @can('manage-transactions')
                        <th scope="col" class="px-6 py-4 text-center font-medium">Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($transactions as $transaction)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $transaction->item->name }}</div>
                                <div class="text-xs text-slate-500">{{ $transaction->item->category }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $types = [
                                        'masuk_mentah' => ['text' => 'Masuk (Mentah)', 'class' => 'bg-green-100 text-green-800'],
                                        'masuk_jadi' => ['text' => 'Masuk (Jadi)', 'class' => 'bg-sky-100 text-sky-800'],
                                        'keluar_terpakai' => ['text' => 'Keluar (Terpakai)', 'class' => 'bg-orange-100 text-orange-800'],
                                        'keluar_dikirim' => ['text' => 'Keluar (Kirim - Jadi)', 'class' => 'bg-yellow-100 text-yellow-800'],
                                        'keluar_mentah' => ['text' => 'Keluar (Kirim - Mentah)', 'class' => 'bg-yellow-100 text-yellow-800'],
                                        'rusak' => ['text' => 'Rusak', 'class' => 'bg-red-100 text-red-800'],
                                    ];
                                    $typeInfo = $types[$transaction->type] ?? ['text' => ucfirst(str_replace('_', ' ', $transaction->type)), 'class' => 'bg-slate-100 text-slate-800'];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $typeInfo['class'] }}">
                                    {{ $typeInfo['text'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-extrabold text-xl text-slate-700">{{ floatval($transaction->quantity) }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $transaction->description ?: '-' }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                            @can('manage-transactions')
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    @php
                                        $isLockedForEdit = in_array($transaction->type, ['masuk_jadi', 'keluar_terpakai']);
                                    @endphp
                                    @can('edit-transactions')
                                        <button wire:click="edit({{ $transaction->id }})" class="{{ $isLockedForEdit ? 'text-slate-300 cursor-not-allowed' : 'text-slate-400 hover:text-amber-500' }} p-2 rounded-full" title="{{ $isLockedForEdit ? 'Transaksi produksi tidak bisa diedit' : 'Edit Transaksi' }}" @if($isLockedForEdit) disabled @endif>
                                            <i class="fas fa-pen fa-sm"></i>
                                        </button>
                                    @else
                                        <button class="p-2 rounded-full text-slate-300 cursor-not-allowed" title="Ubah Dinonaktifkan" disabled>
                                            <i class="fas fa-pen fa-sm"></i>
                                        </button>
                                    @endcan
                                    
                                    <button wire:click="delete({{ $transaction->id }})" wire:confirm="Anda yakin? Stok barang akan dikembalikan." 
                                            class="{{ $isLockedForEdit ? 'text-slate-300 cursor-not-allowed' : 'text-slate-400 hover:text-red-500' }} p-2 rounded-full" 
                                            title="{{ $isLockedForEdit ? 'Transaksi produksi tidak bisa dihapus' : 'Hapus Transaksi' }}" @if($isLockedForEdit) disabled @endif>
                                        <i class="fas fa-trash fa-sm"></i>
                                    </button>
                                </div>
                            </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Gate::check('manage-transactions') ? '6' : '5' }}" class="px-4 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                                <h3 class="mt-2 text-lg font-semibold text-slate-800">Transaksi Tidak Ditemukan</h3>
                                <p class="mt-1 text-sm text-slate-500">Tidak ada data yang cocok dengan filter Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $transactions->links('vendor.livewire.tailwind-custom') }}</div>

    @if ($isModalOpen)
        <div x-data="{ show: @entangle('isModalOpen') }" x-show="show" x-transition.opacity.duration-300ms class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm" x-cloak>
            <div x-show="show" x-transition.scale.duration-300ms @click.away="show = false" class="w-full max-w-lg overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl">
                <form wire:submit.prevent="store">
                    <div class="flex items-center justify-between border-b border-slate-200 p-6">
                        <h3 class="flex items-center text-xl font-bold text-slate-800">
                            <i class="fas {{ $id ? 'fa-pencil-alt' : 'fa-plus-circle' }} mr-3 text-slate-400"></i>
                            <span>{{ $id ? 'Edit Transaksi' : 'Tambah Transaksi Manual' }}</span>
                        </h3>
                        <button type="button" @click="show = false" class="text-3xl text-slate-400 hover:text-slate-600">&times;</button>
                    </div>
                    <div class="space-y-6 p-8">
                        <div class="relative">
                            <label for="type" class="absolute -top-2 left-2 bg-white px-1 text-xs font-semibold text-slate-500">Tipe Transaksi</label>
                            <select wire:model.live="type" id="type" class="block w-full appearance-none rounded-lg border border-slate-300 bg-white py-3 px-4 text-slate-800 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" style="background-image: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 20 20%27%3e%3cpath stroke=%27%236b7280%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%271.5%27 d=%27m6 8 4 4 4-4%27/%3e%3c/svg%3e'); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em;">
                                <option value="">-- Pilih Tipe --</option>
                                <optgroup label="Barang Masuk">
                                    <option value="masuk_mentah">Masuk (Barang Mentah)</option>
                                    <option value="masuk_jadi">Masuk (Barang Jadi)</option>
                                </optgroup>
                                <optgroup label="Barang Keluar">
                                    <option value="keluar_dikirim">Keluar (Dikirim - Barang Jadi)</option>
                                    <option value="keluar_mentah">Keluar (Dikirim - Barang Mentah)</option>
                                </optgroup>
                                <option value="rusak">Barang Rusak</option>
                            </select>
                            @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if($type)
                        <div class="relative">
                            <label for="itemId" class="absolute -top-2 left-2 bg-white px-1 text-xs font-semibold text-slate-500">Barang</label>
                            <select wire:model="itemId" id="itemId" class="block w-full appearance-none rounded-lg border border-slate-300 bg-white py-3 px-4 text-slate-800 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" style="background-image: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 20 20%27%3e%3cpath stroke=%27%236b7280%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%271.5%27 d=%27m6 8 4 4 4-4%27/%3e%3c/svg%3e'); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em;">
                                <option value="">-- Pilih Barang --</option>
                                @forelse($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} (Stok: {{ floatval($item->quantity) }})</option>
                                @empty
                                    <option disabled>Tidak ada barang yang sesuai</option>
                                @endforelse
                            </select>
                            @error('itemId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label for="quantity" class="text-xs font-semibold uppercase text-slate-500">Kuantitas</label>
                                <input wire:model="quantity" id="quantity" type="number" class="mt-1 block w-full border-0 border-b-2 border-slate-200 bg-transparent p-0 pb-2 text-slate-800 focus:border-amber-500 focus:ring-0" min="1">
                                @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label for="description" class="text-xs font-semibold uppercase text-slate-500">Deskripsi</label>
                            <input wire:model="description" id="description" type="text" class="mt-1 block w-full border-0 border-b-2 border-slate-200 bg-transparent p-0 pb-2 text-slate-800 focus:border-amber-500 focus:ring-0" placeholder="Catatan (opsional)...">
                        </div>
                        @endif
                    </div>
                    <div class="flex justify-end space-x-3 rounded-b-xl border-t border-slate-200 bg-slate-50 p-6">
                        <button type="button" @click="show = false" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800">{{ $id ? 'Simpan Perubahan' : 'Simpan Transaksi' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>