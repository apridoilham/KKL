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
                        <option value="masuk_mentah">Masuk (Bahan Mentah)</option>
                        <option value="masuk_jadi">Masuk (Barang Jadi)</option>
                        <option value="produksi_jadi">Produksi (Barang Jadi)</option>
                    </optgroup>
                    <optgroup label="Barang Keluar">
                         <option value="produksi_terpakai">Produksi (Terpakai)</option>
                        <option value="keluar_mentah">Keluar (Dikirim - Bahan Mentah)</option>
                        <option value="keluar_dikirim">Keluar (Dikirim - Barang Jadi)</option>
                    </optgroup>
                    <optgroup label="Barang Rusak">
                        <option value="rusak_mentah">Rusak (Bahan Mentah)</option>
                        <option value="rusak_jadi">Rusak (Barang Jadi)</option>
                    </optgroup>
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
                    <input wire:model.live="filterDate" type="date" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                @elseif($filterDateType === 'monthly')
                    <label class="text-xs font-semibold text-slate-500">Pilih Bulan & Tahun</label>
                    <div class="flex items-center space-x-2">
                        <select wire:model.live="filterSelectedMonth" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                            @endfor
                        </select>
                        <select wire:model.live="filterSelectedYear" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            @php $currentYear = date('Y'); @endphp
                            @for ($year = $currentYear - 5; $year <= $currentYear + 1; $year++)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                @elseif($filterDateType === 'yearly')
                    <label class="text-xs font-semibold text-slate-500">Masukkan Tahun</label>
                    <input wire:model.live="filterYear" type="number" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" placeholder="Tahun...">
                @endif
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Pencarian</label>
                <input wire:model.live.debounce.300ms="search" type="text" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2 px-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" placeholder="Cari nama barang/deskripsi/supplier/customer/SJ...">
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
                        <th scope="col" class="px-6 py-4 font-medium">Detail Tambahan</th>
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
                                <div class="text-xs text-slate-500">{{ $transaction->item->code ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $types = [
                                        'masuk_mentah' => ['text' => 'Masuk (Bahan Mentah)', 'class' => 'bg-green-100 text-green-800'],
                                        'masuk_jadi' => ['text' => 'Masuk (Barang Jadi)', 'class' => 'bg-blue-100 text-blue-800'],
                                        'produksi_jadi' => ['text' => 'Produksi (Barang Jadi)', 'class' => 'bg-sky-100 text-sky-800'],
                                        'produksi_terpakai' => ['text' => 'Produksi (Terpakai)', 'class' => 'bg-orange-100 text-orange-800'],
                                        'keluar_dikirim' => ['text' => 'Keluar (Kirim - Jadi)', 'class' => 'bg-yellow-100 text-yellow-800'],
                                        'keluar_mentah' => ['text' => 'Keluar (Kirim - Mentah)', 'class' => 'bg-yellow-100 text-yellow-800'],
                                        'rusak_mentah' => ['text' => 'Rusak (Bahan Mentah)', 'class' => 'bg-red-100 text-red-800'],
                                        'rusak_jadi' => ['text' => 'Rusak (Barang Jadi)', 'class' => 'bg-red-100 text-red-800'],
                                    ];
                                    $typeInfo = $types[$transaction->type] ?? ['text' => ucfirst(str_replace('_', ' ', $transaction->type)), 'class' => 'bg-slate-100 text-slate-800'];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $typeInfo['class'] }}">
                                    {{ $typeInfo['text'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-extrabold text-xl text-slate-700">{{ floatval($transaction->quantity) }}</td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                @if($transaction->nama_supplier)
                                    <div>Supp: <span class="font-medium text-slate-700">{{ $transaction->nama_supplier }}</span></div>
                                @endif
                                @if($transaction->nama_customer)
                                     <div>Cust: <span class="font-medium text-slate-700">{{ $transaction->nama_customer }}</span></div>
                                @endif
                                @if($transaction->nomor_surat_jalan)
                                    <div>SJ: <span class="font-medium text-slate-700">{{ $transaction->nomor_surat_jalan }}</span></div>
                                @endif
                                 @if($transaction->tanggal_surat_jalan)
                                    <div>Tgl. SJ: <span class="font-medium text-slate-700">{{ $transaction->tanggal_surat_jalan->format('d M Y') }}</span></div>
                                @endif
                                 @if($transaction->description)
                                    <div>Desc: <span class="font-medium text-slate-700">{{ Str::limit($transaction->description, 30) }}</span></div>
                                @endif
                                @if(!$transaction->nama_supplier && !$transaction->nama_customer && !$transaction->nomor_surat_jalan && !$transaction->description)
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                            @can('manage-transactions')
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    @php
                                        $isLockedForEdit = in_array($transaction->type, ['produksi_jadi', 'produksi_terpakai']);
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
        <div x-data="{ show: @entangle('isModalOpen'), transactionType: @entangle('type') }" x-show="show" x-transition.opacity.duration-300ms class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm overflow-y-auto" x-cloak>
            <div x-show="show" x-transition.scale.duration-300ms @click.away="show = false" class="w-full max-w-lg overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl my-auto">
                <form wire:submit.prevent="store" novalidate>
                    <div class="flex items-center justify-between border-b border-slate-200 p-6">
                         <h3 class="flex items-center text-xl font-bold text-slate-800">
                            <i class="fas {{ $id ? 'fa-pencil-alt' : 'fa-plus-circle' }} mr-3 text-slate-400"></i>
                            <span>{{ $id ? 'Edit Transaksi' : 'Tambah Transaksi Manual' }}</span>
                        </h3>
                        <button type="button" @click="show = false" class="text-3xl text-slate-400 hover:text-slate-600">&times;</button>
                    </div>
                    <div class="space-y-6 p-8 max-h-[70vh] overflow-y-auto">
                        <div>
                            <label for="type" class="text-sm font-medium text-slate-700">Tipe Transaksi <span class="text-red-500">*</span></label>
                            <select wire:model.live="type" id="type" class="mt-1 block w-full appearance-none rounded-lg border border-slate-300 bg-white py-2.5 px-3 text-slate-800 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" required>
                                <option value="">-- Pilih Tipe --</option>
                                <optgroup label="Barang Masuk">
                                    <option value="masuk_mentah">Masuk (Bahan Mentah)</option>
                                    <option value="masuk_jadi">Masuk (Barang Jadi)</option>
                                </optgroup>
                                <optgroup label="Barang Keluar">
                                    <option value="keluar_mentah">Keluar (Dikirim - Bahan Mentah)</option>
                                    <option value="keluar_dikirim">Keluar (Dikirim - Barang Jadi)</option>
                                </optgroup>
                                <optgroup label="Barang Rusak">
                                    <option value="rusak_mentah">Rusak (Bahan Mentah)</option>
                                    <option value="rusak_jadi">Rusak (Barang Jadi)</option>
                                </optgroup>
                            </select>
                            @error('type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        @if($type)
                            <div>
                                <label for="itemId" class="text-sm font-medium text-slate-700">Barang <span class="text-red-500">*</span></label>
                                <select wire:model="itemId" id="itemId" class="mt-1 block w-full appearance-none rounded-lg border border-slate-300 bg-white py-2.5 px-3 text-slate-800 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" required>
                                    <option value="">-- Pilih Barang --</option>
                                    @forelse($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} (Stok: {{ floatval($item->quantity) }})</option>
                                    @empty
                                        <option disabled>Tidak ada barang yang sesuai</option>
                                    @endforelse
                                </select>
                                @error('itemId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="quantity" class="text-sm font-medium text-slate-700">Kuantitas <span class="text-red-500">*</span></label>
                                <div class="relative mt-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-hashtag text-slate-400"></i>
                                    </div>
                                    <input wire:model="quantity" id="quantity" type="number" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" min="1" required>
                                </div>
                                @error('quantity')<span class="text-xs text-red-500 mt-1">{{$message}}</span>@enderror
                            </div>

                            <div x-show="transactionType === 'masuk_mentah' || transactionType === 'masuk_jadi'" x-transition class="space-y-6 border-t border-slate-200 pt-6">
                                <div>
                                    <label for="nama_supplier" class="text-sm font-medium text-slate-700">Nama Supplier <span class="text-red-500">*</span></label>
                                    <input wire:model="nama_supplier" id="nama_supplier" type="text" placeholder="Masukkan nama supplier..." class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2.5 px-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                    @error('nama_supplier') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label for="nomor_surat_jalan_in" class="text-sm font-medium text-slate-700">No. Surat Jalan <span class="text-red-500">*</span></label>
                                        <input wire:model="nomor_surat_jalan" id="nomor_surat_jalan_in" type="text" placeholder="cth: SJ/SUP/001" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2.5 px-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                        @error('nomor_surat_jalan') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="tanggal_surat_jalan_in" class="text-sm font-medium text-slate-700">Tgl. Surat Jalan <span class="text-red-500">*</span></label>
                                        <input wire:model="tanggal_surat_jalan" id="tanggal_surat_jalan_in" type="date" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2.5 px-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                        @error('tanggal_surat_jalan') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div x-show="transactionType === 'keluar_dikirim' || transactionType === 'keluar_mentah'" x-transition class="space-y-6 border-t border-slate-200 pt-6">
                                <div>
                                    <label for="nama_customer" class="text-sm font-medium text-slate-700">Nama Customer <span class="text-red-500">*</span></label>
                                    <input wire:model="nama_customer" id="nama_customer" type="text" placeholder="Masukkan nama customer..." class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2.5 px-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                    @error('nama_customer') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label for="nomor_surat_jalan_out" class="text-sm font-medium text-slate-700">No. Surat Jalan <span class="text-red-500">*</span></label>
                                        <input wire:model="nomor_surat_jalan" id="nomor_surat_jalan_out" type="text" placeholder="cth: SJ/CUST/001" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2.5 px-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                        @error('nomor_surat_jalan') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="tanggal_surat_jalan_out" class="text-sm font-medium text-slate-700">Tgl. Surat Jalan <span class="text-red-500">*</span></label>
                                        <input wire:model="tanggal_surat_jalan" id="tanggal_surat_jalan_out" type="date" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2.5 px-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                        @error('tanggal_surat_jalan') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                             </div>

                            <div class="border-t border-slate-200 pt-6">
                                <label for="description" class="text-sm font-medium text-slate-700">Deskripsi</label>
                                <div class="relative mt-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-pencil-alt text-slate-400"></i>
                                    </div>
                                    <input wire:model="description" id="description" type="text" placeholder="Catatan (opsional)..." class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                </div>
                                @error('description') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                    <div class="flex justify-end space-x-3 rounded-b-xl border-t border-slate-200 bg-slate-50 p-6">
                        <button type="button" @click="show = false" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="store" class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800 disabled:bg-slate-400">
                            <span wire:loading.remove wire:target="store">{{ $id ? 'Simpan Perubahan' : 'Simpan Transaksi' }}</span>
                            <span wire:loading wire:target="store">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>