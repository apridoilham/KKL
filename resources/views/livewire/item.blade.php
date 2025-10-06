<div class="container-fluid px-4 md:px-6 py-6">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800">{{ $data['title'] }}</h1>
            <p class="mt-1 text-slate-600">
                @if($items->total() > 0)
                    Menampilkan daftar barang.
                @else
                    Belum ada data barang untuk tipe ini.
                @endif
            </p>
        </div>
        <div class="flex items-center space-x-3 mt-4 md:mt-0 w-full md:w-auto">
            <div class="relative w-full md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-slate-400"></i>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg bg-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" placeholder="Cari barang...">
            </div>
            @if(auth()->user()->role === 'admin')
            <button wire:click="create" class="inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i>
                Tambah Barang
            </button>
            @endif
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-4">#</th>
                        <th scope="col" class="px-6 py-4">Kode</th>
                        <th scope="col" class="px-6 py-4">Nama</th>
                        <th scope="col" class="px-6 py-4">Kategori</th>
                        <th scope="col" class="px-6 py-4">Tipe</th>
                        <th scope="col" class="px-6 py-4">Kuantitas</th>
                        <th scope="col" class="px-6 py-4">Status</th>
                        <th scope="col" class="px-6 py-4">Tgl. Input</th>
                        @if(auth()->user()->role === 'admin')
                        <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($items as $index => $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">{{ $items->firstItem() + $index }}</td>
                            <td class="px-6 py-4 font-mono">{{ $item->code ?: '-' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $item->name }}</td>
                            <td class="px-6 py-4">{{ $item->category ?: '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded {{ $item->item_type == 'barang_jadi' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $item->item_type == 'barang_jadi' ? 'Barang Jadi' : 'Barang Mentah' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-extrabold text-xl text-indigo-700">{{ floatval($item->quantity) }}</td>
                            <td class="px-6 py-4"><span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {{ $item->status == 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}"><span class="w-2 h-2 mr-2 rounded-full {{ $item->status == 'available' ? 'bg-green-500' : 'bg-red-500' }}"></span>{{ $item->status == 'available' ? 'Tersedia' : 'Habis' }}</span></td>
                            <td class="px-6 py-4">{{ $item->created_at->format('d M Y, H:i') }}</td>
                            @if(auth()->user()->role === 'admin')
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button wire:click="edit({{ $item->id }})" class="p-2 rounded-full text-blue-600 hover:bg-blue-100" title="Ubah Data"><i class="fas fa-pen fa-sm"></i></button>
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="Anda yakin ingin menghapus barang ini?" class="p-2 rounded-full text-red-600 hover:bg-red-100" title="Hapus Data"><i class="fas fa-trash fa-sm"></i></button>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'admin' ? '9' : '8' }}" class="text-center py-16 px-4">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
                                <h3 class="mt-2 text-lg font-semibold text-slate-800">Barang Tidak Ditemukan</h3>
                                <p class="mt-1 text-sm text-slate-500">Tidak ada data yang cocok dengan filter Anda.</p>
                                @if(auth()->user()->role === 'admin')
                                <div class="mt-6">
                                    <button wire:click="create" class="inline-flex items-center px-4 py-2.5 border text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700"><i class="fas fa-plus mr-2"></i>Tambah Barang</button>
                                </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $items->links() }}</div>

    @if ($isModalOpen)
        <div x-data="{ show: @entangle('isModalOpen') }" x-show="show" x-transition.opacity.duration.300ms class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
            <div x-show="show" x-transition.scale.duration.300ms @click.away="show = false" class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
                <form wire:submit.prevent="store">
                    <div class="p-6 bg-indigo-600 text-white flex items-center justify-between"><h3 class="text-xl font-bold flex items-center"><i class="fas {{ $id ? 'fa-pencil-alt' : 'fa-plus-circle' }} mr-3"></i><span>{{ $id ? 'Ubah Barang' : 'Tambah Barang Baru' }}</span></h3><button type="button" @click="show = false" class="text-indigo-200 hover:text-white text-3xl">&times;</button></div>
                    <div class="p-8 space-y-6">
                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase">Tipe Barang <span class="text-red-500">*</span></label>
                            <div class="mt-2 flex space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" wire:model="item_type" value="barang_mentah" class="form-radio h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-slate-700">Barang Mentah</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" wire:model="item_type" value="barang_jadi" class="form-radio h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-slate-700">Barang Jadi</span>
                                </label>
                            </div>
                        </div>
                        <hr/>
                        <div><label for="name" class="text-xs font-semibold text-slate-500 uppercase">Nama Barang <span class="text-red-500">*</span></label><input wire:model="name" type="text" id="name" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500" placeholder="cth: Laptop" required>@error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror</div>
                        <div><label for="category" class="text-xs font-semibold text-slate-500 uppercase">Kategori</label><input wire:model="category" type="text" id="category" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500" placeholder="cth: Elektronik"></div>
                        <div><label for="code" class="text-xs font-semibold text-slate-500 uppercase">Kode Barang</label><input wire:model="code" type="text" id="code" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500" placeholder="cth: BRG001"></div>
                    </div>
                    <div class="p-6 bg-slate-50 rounded-b-xl flex justify-end space-x-3 border-t"><button type="button" @click="show = false" class="px-4 py-2.5 border rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">Batal</button><button type="submit" class="inline-flex items-center px-4 py-2.5 border text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700">Simpan Barang</button></div>
                </form>
            </div>
        </div>
    @endif
</div>