<div class="container-fluid px-4 md:px-6 py-6">

    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800">Transaksi</h1>
            <p class="mt-1 text-slate-600">Catatan semua pergerakan inventaris dalam sistem Anda.</p>
        </div>
        <div class="flex items-center space-x-3 mt-4 md:mt-0 w-full md:w-auto">
            <div class="relative w-full md:w-48">
                <select wire:model.live="filterType" class="block w-full pl-3 pr-8 py-2.5 border border-slate-300 rounded-lg leading-5 bg-white text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm appearance-none transition">
                    <option value="all">Semua Status</option>
                    <option value="in">Masuk</option>
                    <option value="out">Keluar</option>
                    <option value="damaged">Rusak</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                    <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                </div>
            </div>
            <div class="relative w-full md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-slate-400"></i>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg leading-5 bg-white placeholder-slate-500 focus:outline-none focus:placeholder-slate-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition" placeholder="Cari...">
            </div>
            <button wire:click="create" class="inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-plus mr-2"></i>
                Tambah Transaksi
            </button>
        </div>
    </div>
    
    @if (session()->has('dataSession'))
        <div class="bg-{{ session('dataSession')['status'] == 'success' ? 'green' : 'red' }}-100 border-l-4 border-{{ session('dataSession')['status'] == 'success' ? 'green' : 'red' }}-500 text-{{ session('dataSession')['status'] == 'success' ? 'green' : 'red' }}-700 p-4 mb-6 rounded-md" role="alert">
            <p class="font-bold">{{ ucfirst(session('dataSession')['status']) }}</p>
            <p>{{ session('dataSession')['message'] }}</p>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-4">Nama Barang</th>
                        <th scope="col" class="px-6 py-4">Status</th> <th scope="col" class="px-6 py-4">Kuantitas</th>
                        <th scope="col" class="px-6 py-4">Deskripsi</th>
                        <th scope="col" class="px-6 py-4">Tanggal</th>
                        <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($transactions as $transaction)
                        <tr class="hover:bg-slate-50 transition-colors duration-200">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $transaction->item->name }}</div>
                                <div class="text-xs text-slate-500">{{ $transaction->item->category }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $typeClass = '';
                                    $statusText = '';
                                    if ($transaction->type == 'in') {
                                        $typeClass = 'bg-green-100 text-green-800';
                                        $statusText = 'Masuk';
                                    } elseif ($transaction->type == 'out') {
                                        $typeClass = 'bg-yellow-100 text-yellow-800';
                                        $statusText = 'Keluar';
                                    } else {
                                        $typeClass = 'bg-red-100 text-red-800';
                                        $statusText = 'Rusak';
                                    }
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {{ $typeClass }}">
                                    {{ $statusText }} </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-lg text-slate-800">{{ $transaction->quantity }}</td>
                            <td class="px-6 py-4">{{ $transaction->description ?: '-' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button wire:click="edit({{ $transaction->id }})" class="p-2 rounded-full text-slate-400 cursor-not-allowed" title="Ubah Dinonaktifkan" disabled>
                                        <i class="fas fa-pen fa-sm"></i>
                                    </button>
                                    @if(auth()->user()->role == 'admin')
                                        <button wire:click="delete({{ $transaction->id }})" wire:confirm="Anda yakin? Stok barang akan dikembalikan." class="p-2 rounded-full text-red-500 hover:bg-red-100 transition-colors duration-200" title="Hapus Transaksi">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-16 px-4"><svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg><h3 class="mt-2 text-lg font-semibold text-slate-800">Transaksi Tidak Ditemukan</h3><p class="mt-1 text-sm text-slate-500">Mulai dengan menambahkan transaksi pertama Anda.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $transactions->links() }}
    </div>

    @if ($isModalOpen)
        <div x-data="{ show: @entangle('isModalOpen') }" x-show="show" x-transition.opacity.duration.300ms class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
            <div x-show="show" x-transition.scale.duration.300ms @click.away="show = false" class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
                <form wire:submit.prevent="store">
                    <div class="p-6 bg-indigo-600 text-white flex items-center justify-between"><h3 class="text-xl font-bold flex items-center"><i class="fas fa-plus-circle mr-3"></i> Tambah Transaksi Baru</h3><button type="button" @click="show = false" class="text-indigo-200 hover:text-white text-3xl">&times;</button></div>
                    <div class="p-8 space-y-6">
                        <div>
                            <label for="itemId" class="text-xs font-semibold text-slate-500 uppercase">Barang <span class="text-red-500">*</span></label>
                            <select wire:model="itemId" id="itemId" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500" required>
                                <option value="">-- Pilih Barang --</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} (Stok: {{ $item->quantity }})</option>
                                @endforeach
                            </select>
                            @error('itemId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label for="type" class="text-xs font-semibold text-slate-500 uppercase">Status <span class="text-red-500">*</span></label>
                                <select wire:model="type" id="type" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="in">Masuk</option>
                                    <option value="out">Keluar</option>
                                    <option value="damaged">Rusak</option>
                                </select>
                                @error('type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="quantity" class="text-xs font-semibold text-slate-500 uppercase">Kuantitas <span class="text-red-500">*</span></label>
                                <input wire:model="quantity" type="number" id="quantity" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500" required min="1">
                                @error('quantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label for="description" class="text-xs font-semibold text-slate-500 uppercase">Deskripsi</label>
                            <input wire:model="description" type="text" id="description" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500" placeholder="Catatan (opsional)...">
                        </div>
                    </div>
                    <div class="p-6 bg-slate-50 rounded-b-xl flex justify-end space-x-3 border-t">
                        <button type="button" @click="show = false" class="px-4 py-2.5 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700">Simpan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>