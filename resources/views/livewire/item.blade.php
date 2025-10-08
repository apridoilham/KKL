<div class="container mx-auto px-4 py-6 md:px-6">
    <div class="mb-8 flex flex-col items-start justify-between gap-y-4 md:flex-row md:items-center">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">{{ $data['title'] }}</h1>
            <p class="mt-1 text-slate-500">
                @if($items->total() > 0)
                    Menampilkan {{ $items->firstItem() }}-{{ $items->lastItem() }} dari {{ $items->total() }} item.
                @else
                    Belum ada data untuk tipe ini.
                @endif
            </p>
        </div>
        <div class="flex w-full items-center space-x-3 md:w-auto">
            <div class="relative w-full md:w-64">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fas fa-search text-slate-400"></i>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" placeholder="Cari...">
            </div>
            @can('manage-items')
            <button wire:click="create" class="inline-flex flex-shrink-0 items-center justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800">
                <i class="fas fa-plus mr-2"></i>
                Tambah Item
            </button>
            @endcan
        </div>
    </div>
    
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-medium">#</th>
                        <th scope="col" class="px-6 py-4 font-medium">Kode</th>
                        <th scope="col" class="px-6 py-4 font-medium">Nama</th>
                        <th scope="col" class="px-6 py-4 font-medium">Kategori</th>
                        <th scope="col" class="px-6 py-4 font-medium">Tipe</th>
                        <th scope="col" class="px-6 py-4 font-medium">Kuantitas</th>
                        <th scope="col" class="px-6 py-4 font-medium">Status</th>
                        <th scope="col" class="px-6 py-4 font-medium">Tgl. Input</th>
                        @can('manage-items')
                        <th scope="col" class="px-6 py-4 text-center font-medium">Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($items as $index => $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-slate-500">{{ $items->firstItem() + $index }}</td>
                            <td class="px-6 py-4 font-mono text-slate-500">{{ $item->code ?: '-' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $item->name }}</td>
                            <td class="px-6 py-4">{{ $item->category ?: '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded px-2 py-1 text-xs font-medium {{ $item->item_type == 'barang_jadi' ? 'bg-cyan-50 text-cyan-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $item->item_type == 'barang_jadi' ? 'Barang Jadi' : 'Bahan Mentah' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-extrabold text-xl text-slate-700">{{ floatval($item->quantity) }}</td>
                            <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $item->status == 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}"><span class="mr-2 h-2 w-2 rounded-full {{ $item->status == 'available' ? 'bg-green-500' : 'bg-red-500' }}"></span>{{ $item->status == 'available' ? 'Tersedia' : 'Habis' }}</span></td>
                            <td class="px-6 py-4">{{ $item->created_at->format('d M Y, H:i') }}</td>
                            @can('manage-items')
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button wire:click="edit({{ $item->id }})" class="p-2 rounded-full text-slate-400 hover:text-amber-500" title="Ubah Data"><i class="fas fa-pen fa-sm"></i></button>
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="Anda yakin ingin menghapus item ini?" class="p-2 rounded-full text-slate-400 hover:text-red-500" title="Hapus Data"><i class="fas fa-trash fa-sm"></i></button>
                                </div>
                            </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Gate::check('manage-items') ? '9' : '8' }}" class="px-4 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
                                <h3 class="mt-2 text-lg font-semibold text-slate-800">Item Tidak Ditemukan</h3>
                                <p class="mt-1 text-sm text-slate-500">Tidak ada data yang cocok dengan pencarian Anda.</p>
                                @can('manage-items')
                                <div class="mt-6">
                                    <button wire:click="create" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800"><i class="fas fa-plus mr-2"></i>Tambah Item</button>
                                </div>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $items->links('vendor.livewire.tailwind-custom') }}</div>

    @if ($isModalOpen)
        <div x-data="{ show: @entangle('isModalOpen') }" x-show="show" x-transition.opacity.duration-300ms class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm" x-cloak>
            <div x-show="show" x-transition.scale.duration-300ms @click.away="show = false" class="w-full max-w-lg overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl">
                <form wire:submit.prevent="store" novalidate>
                    <div class="flex items-center justify-between border-b border-slate-200 p-6">
                        <h3 class="flex items-center text-xl font-bold text-slate-800"><i class="fas {{ $id ? 'fa-pencil-alt' : 'fa-plus-circle' }} mr-3 text-slate-400"></i><span>{{ $id ? 'Ubah' : 'Tambah' }} {{ $item_type == 'barang_jadi' ? 'Barang Jadi' : 'Bahan Mentah' }}</span></h3>
                        <button type="button" @click="show = false" class="text-3xl text-slate-400 hover:text-slate-600">&times;</button>
                    </div>
                    <div class="space-y-6 p-8">
                        <div>
                            <label for="name" class="text-sm font-medium text-slate-700">Nama Item <span class="text-red-500">*</span></label>
                            <div class="relative mt-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="fas fa-tag text-slate-400"></i>
                                </div>
                                <input wire:model="name" id="name" type="text" placeholder="cth: Tepung Terigu" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" required>
                            </div>
                            @error('name') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="category" class="text-sm font-medium text-slate-700">Kategori</label>
                            <div class="relative mt-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="fas fa-bookmark text-slate-400"></i>
                                </div>
                                <input wire:model="category" type="text" id="category" placeholder="cth: Bahan Kue" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            </div>
                        </div>
                        <div>
                            <label for="code" class="text-sm font-medium text-slate-700">Kode Item</label>
                             <div class="relative mt-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="fas fa-barcode text-slate-400"></i>
                                </div>
                                <input wire:model="code" type="text" id="code" placeholder="cth: TPG-001" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 rounded-b-xl border-t border-slate-200 bg-slate-50 p-6">
                        <button type="button" @click="show = false" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800">Simpan Item</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>