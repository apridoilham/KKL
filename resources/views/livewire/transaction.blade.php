<div class="container-fluid px-4 md:px-6 py-6">

    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800">Transaksi</h1>
            <p class="mt-1 text-slate-600">Catatan semua pergerakan inventaris dalam sistem Anda.</p>
        </div>
        @can('manage-transactions')
        <div class="flex items-center space-x-3 mt-4 md:mt-0">
            <button wire:click="create" class="inline-flex items-center justify-center px-4 py-2.5 border text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i> Tambah Transaksi
            </button>
        </div>
        @endcan
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label class="text-xs font-semibold text-slate-500">Filter Tipe</label>
                <select wire:model.live="filterType" class="mt-1 block w-full border border-slate-300 rounded-lg py-2 px-3 text-slate-700">
                    <option value="all">Semua Tipe</option>
                    <option value="pembelian_masuk">Pembelian Masuk</option>
                    <option value="produksi_masuk">Produksi Selesai</option>
                    <option value="produksi_keluar">Produksi Dipakai</option>
                    <option value="pengiriman_keluar">Pengiriman Keluar</option>
                    <option value="rusak">Barang Rusak</option>
                </select>
            </div>
             <div>
                <label class="text-xs font-semibold text-slate-500">Filter Waktu</label>
                <select wire:model.live="filterDateType" class="mt-1 block w-full border border-slate-300 rounded-lg py-2 px-3 text-slate-700">
                    <option value="all_time">Semua Waktu</option>
                    <option value="daily">Harian</option>
                    <option value="monthly">Bulanan</option>
                    <option value="yearly">Tahunan</option>
                </select>
            </div>
            <div>
                @if($filterDateType === 'daily')
                    <label class="text-xs font-semibold text-slate-500">Pilih Tanggal</label>
                    <input wire:model.live="filterDate" type="date" class="mt-1 block w-full border border-slate-300 rounded-lg py-2 px-3 text-slate-700">
                @elseif($filterDateType === 'monthly')
                    <label class="text-xs font-semibold text-slate-500">Pilih Bulan</label>
                    <input wire:model.live="filterMonth" type="month" class="mt-1 block w-full border border-slate-300 rounded-lg py-2 px-3 text-slate-700">
                @elseif($filterDateType === 'yearly')
                    <label class="text-xs font-semibold text-slate-500">Masukkan Tahun</label>
                    <input wire:model.live="filterYear" type="number" class="mt-1 block w-full border border-slate-300 rounded-lg py-2 px-3 text-slate-700" placeholder="Tahun...">
                @endif
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Pencarian</label>
                <input wire:model.live.debounce.300ms="search" type="text" class="mt-1 block w-full border border-slate-300 rounded-lg py-2 px-3" placeholder="Cari...">
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b">
                    <tr>
                        <th scope="col" class="px-6 py-4">Nama Barang</th>
                        <th scope="col" class="px-6 py-4">Tipe Transaksi</th>
                        <th scope="col" class="px-6 py-4">Kuantitas</th>
                        <th scope="col" class="px-6 py-4">Deskripsi</th>
                        <th scope="col" class="px-6 py-4">Tanggal</th>
                        @can('manage-transactions')
                        <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($transactions as $transaction)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $transaction->item->name }}</div>
                                <div class="text-xs text-slate-500">{{ $transaction->item->category }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $types = [
                                        'pembelian_masuk' => ['text' => 'Pembelian Masuk', 'class' => 'bg-green-100 text-green-800'],
                                        'produksi_masuk' => ['text' => 'Produksi Selesai', 'class' => 'bg-sky-100 text-sky-800'],
                                        'produksi_keluar' => ['text' => 'Produksi Dipakai', 'class' => 'bg-orange-100 text-orange-800'],
                                        'pengiriman_keluar' => ['text' => 'Pengiriman Keluar', 'class' => 'bg-yellow-100 text-yellow-800'],
                                        'rusak' => ['text' => 'Rusak', 'class' => 'bg-red-100 text-red-800'],
                                    ];
                                    $typeInfo = $types[$transaction->type] ?? ['text' => ucfirst($transaction->type), 'class' => 'bg-gray-100 text-gray-800'];
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {{ $typeInfo['class'] }}">
                                    {{ $typeInfo['text'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-lg text-slate-800">{{ floatval($transaction->quantity) }}</td>
                            <td class="px-6 py-4">{{ $transaction->description ?: '-' }}</td>
                            <td class="px-6 py-4">{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                            @can('manage-transactions')
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    @php
                                        $isLocked = in_array($transaction->type, ['produksi_masuk', 'produksi_keluar']);
                                    @endphp
                                    <button class="p-2 rounded-full text-slate-400 cursor-not-allowed" title="Ubah Dinonaktifkan" disabled>
                                        <i class="fas fa-pen fa-sm"></i>
                                    </button>
                                    <button wire:click="delete({{ $transaction->id }})" wire:confirm="Anda yakin? Stok barang akan dikembalikan." 
                                            class="p-2 rounded-full {{ $isLocked ? 'text-slate-400 cursor-not-allowed' : 'text-red-500 hover:bg-red-100' }}" 
                                            title="{{ $isLocked ? 'Transaksi produksi tidak bisa dihapus' : 'Hapus Transaksi' }}" @if($isLocked) disabled @endif>
                                        <i class="fas fa-trash fa-sm"></i>
                                    </button>
                                </div>
                            </td>
                            @endcan
                        </tr>
                    @empty
                        <tr><td colspan="{{ Gate::check('manage-transactions') ? '6' : '5' }}" class="text-center py-16">Tidak ada transaksi ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $transactions->links() }}</div>

    @if ($isModalOpen)
        <div x-data="{ show: @entangle('isModalOpen') }" x-show="show" x-transition.opacity.duration.300ms class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
            <div x-show="show" x-transition.scale.duration.300ms @click.away="show = false" class="bg-white rounded-xl w-full max-w-lg">
                <form wire:submit.prevent="store">
                    <div class="p-6 bg-indigo-600 text-white flex items-center justify-between"><h3 class="text-xl font-bold">Tambah Transaksi Manual</h3></div>
                    <div class="p-8 space-y-6">
                        <div>
                            <label class="text-xs font-semibold">Tipe Transaksi</label>
                            <select wire:model.live="type" class="mt-1 block w-full border-0 border-b-2 p-0 pb-2 focus:ring-0">
                                <option value="">-- Pilih Tipe --</option>
                                <option value="pembelian_masuk">Pembelian Masuk (Barang Mentah)</option>
                                <option value="pengiriman_keluar">Pengiriman Keluar (Barang Jadi)</option>
                                <option value="rusak">Barang Rusak</option>
                            </select>
                            @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if($type)
                        <div>
                            <label class="text-xs font-semibold">Barang</label>
                            <select wire:model="itemId" class="mt-1 block w-full border-0 border-b-2 p-0 pb-2 focus:ring-0">
                                <option value="">-- Pilih Barang --</option>
                                @forelse($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} (Stok: {{ floatval($item->quantity) }})</option>
                                @empty
                                    <option disabled>
                                        @if($type === 'pembelian_masuk')
                                            Tidak ada Barang Mentah
                                        @elseif($type === 'pengiriman_keluar')
                                            Tidak ada Barang Jadi
                                        @else
                                            Tidak ada barang
                                        @endif
                                    </option>
                                @endforelse
                            </select>
                            @error('itemId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="text-xs font-semibold">Kuantitas</label>
                                <input wire:model="quantity" type="number" class="mt-1 block w-full border-0 border-b-2 p-0 pb-2 focus:ring-0" min="1">
                                @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Deskripsi</label>
                            <input wire:model="description" type="text" class="mt-1 block w-full border-0 border-b-2 p-0 pb-2 focus:ring-0" placeholder="Catatan (opsional)...">
                        </div>
                        @endif
                    </div>
                    <div class="p-6 bg-slate-50 flex justify-end space-x-3"><button type="button" @click="show = false" class="px-4 py-2.5 rounded-lg text-sm">Batal</button><button type="submit" class="px-4 py-2.5 rounded-lg text-sm text-white bg-indigo-600">Simpan Transaksi</button></div>
                </form>
            </div>
        </div>
    @endif
</div>