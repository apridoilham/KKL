<div class="container-fluid px-4 md:px-6 py-6">

    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800">Manajemen Pengguna</h1>
            <p class="mt-1 text-slate-600">Tambah, ubah, atau hapus data pengguna sistem.</p>
        </div>
        <div class="flex items-center space-x-3 mt-4 md:mt-0 w-full md:w-auto">
            <div class="relative w-full md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-search text-slate-400"></i></div>
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg bg-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" placeholder="Cari pengguna...">
            </div>
            <button wire:click="create" class="inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i>
                Tambah Pengguna
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
                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b">
                    <tr>
                        <th scope="col" class="px-6 py-4">Nama</th>
                        <th scope="col" class="px-6 py-4">Username</th>
                        <th scope="col" class="px-6 py-4">Peran (Role)</th>
                        <th scope="col" class="px-6 py-4">Tanggal Bergabung</th>
                        <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $user->name }}</td>
                            <td class="px-6 py-4">{{ $user->username }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $user->role == 'admin' ? 'bg-indigo-100 text-indigo-800' : 'bg-sky-100 text-sky-800' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button wire:click="edit({{ $user->id }})" class="p-2 rounded-full text-blue-600 hover:bg-blue-100" title="Ubah Data"><i class="fas fa-pen fa-sm"></i></button>
                                    @if ($user->id != auth()->id())
                                        <button wire:click="delete({{ $user->id }})" wire:confirm="Anda yakin ingin menghapus pengguna ini?" class="p-2 rounded-full text-red-600 hover:bg-red-100" title="Hapus Data"><i class="fas fa-trash fa-sm"></i></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-16"><h3 class="text-lg font-semibold">Pengguna Tidak Ditemukan</h3></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>

    @if ($isModalOpen)
        <div x-data="{ show: @entangle('isModalOpen') }" x-show="show" x-transition.opacity.duration.300ms class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
            <div x-show="show" x-transition.scale.duration.300ms @click.away="show = false" class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
                <form wire:submit.prevent="store">
                    <div class="p-6 bg-indigo-600 text-white flex items-center justify-between"><h3 class="text-xl font-bold flex items-center"><i class="fas {{ $isEditMode ? 'fa-user-edit' : 'fa-user-plus' }} mr-3"></i><span>{{ $isEditMode ? 'Ubah Pengguna' : 'Tambah Pengguna Baru' }}</span></h3><button type="button" @click="show = false" class="text-indigo-200 hover:text-white text-3xl">&times;</button></div>
                    <div class="p-8 space-y-6">
                        <div><label for="name" class="text-xs font-semibold text-slate-500 uppercase">Nama Lengkap <span class="text-red-500">*</span></label><input wire:model="name" type="text" id="name" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500" required>@error('name')<span class="text-red-500 text-xs">{{$message}}</span>@enderror</div>
                        <div class="grid grid-cols-2 gap-6">
                            <div><label for="username" class="text-xs font-semibold text-slate-500 uppercase">Username <span class="text-red-500">*</span></label><input wire:model="username" type="text" id="username" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500" required>@error('username')<span class="text-red-500 text-xs">{{$message}}</span>@enderror</div>
                            <div><label for="role" class="text-xs font-semibold text-slate-500 uppercase">Peran (Role) <span class="text-red-500">*</span></label><select wire:model="role" id="role" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500" required><option value="">-- Pilih Peran --</option><option value="admin">Admin</option><option value="staff">Staff</option></select>@error('role')<span class="text-red-500 text-xs">{{$message}}</span>@enderror</div>
                        </div>
                        <hr/>
                        <div><p class="text-sm text-slate-500">{{ $isEditMode ? 'Kosongkan jika tidak ingin mengubah password.' : 'Password default untuk pengguna baru.' }}</p></div>
                        <div class="grid grid-cols-2 gap-6">
                             <div><label for="password" class="text-xs font-semibold text-slate-500 uppercase">Password @if(!$isEditMode)<span class="text-red-500">*</span>@endif</label><input wire:model="password" type="password" id="password" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500">@error('password')<span class="text-red-500 text-xs">{{$message}}</span>@enderror</div>
                             <div><label for="password_confirmation" class="text-xs font-semibold text-slate-500 uppercase">Konfirmasi Password @if(!$isEditMode)<span class="text-red-500">*</span>@endif</label><input wire:model="password_confirmation" type="password" id="password_confirmation" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500"></div>
                        </div>
                    </div>
                    <div class="p-6 bg-slate-50 rounded-b-xl flex justify-end space-x-3 border-t"><button type="button" @click="show = false" class="px-4 py-2.5 border rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">Batal</button><button type="submit" class="inline-flex items-center px-4 py-2.5 border text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700">Simpan</button></div>
                </form>
            </div>
        </div>
    @endif
</div>