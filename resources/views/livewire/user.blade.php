<div class="container mx-auto px-4 py-6 md:px-6">

    <div class="mb-8 flex flex-col items-start justify-between gap-y-4 md:flex-row md:items-center">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">Manajemen Pengguna</h1>
            <p class="mt-1 text-slate-500">Tambah, ubah, atau hapus data pengguna sistem.</p>
        </div>
        <div class="mt-4 md:mt-0">
             <button wire:click="create" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800">
                <i class="fas fa-plus mr-2"></i>
                Tambah Pengguna
            </button>
        </div>
    </div>
    
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="text-xs font-semibold text-slate-500">Filter Berdasarkan Peran</label>
                <select wire:model.live="filterRole" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    <option value="all">Semua Peran</option>
                    <option value="admin">Admin</option>
                    <option value="produksi">Staff Produksi</option>
                    <option value="pengiriman">Staff Pengiriman</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Pencarian Nama / Username</label>
                <input wire:model.live.debounce.300ms="search" type="text" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white py-2 px-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" placeholder="Cari pengguna...">
            </div>
        </div>
    </div>
    
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-medium">Nama</th>
                        <th scope="col" class="px-6 py-4 font-medium">Username</th>
                        <th scope="col" class="px-6 py-4 font-medium">Peran (Role)</th>
                        <th scope="col" class="px-6 py-4 font-medium">Tanggal Bergabung</th>
                        <th scope="col" class="px-6 py-4 text-center font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $user->name }}</td>
                            <td class="px-6 py-4">{{ $user->username }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $roleClass = match(strtolower($user->role)) {
                                        'admin' => 'bg-amber-100 text-amber-800',
                                        'produksi' => 'bg-sky-100 text-sky-800',
                                        'pengiriman' => 'bg-cyan-100 text-cyan-800',
                                        default => 'bg-slate-100 text-slate-800',
                                    };
                                @endphp
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $roleClass }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button wire:click="edit({{ $user->id }})" class="p-2 rounded-full text-slate-400 hover:text-amber-500" title="Ubah Data"><i class="fas fa-pen fa-sm"></i></button>
                                    @if ($user->id != auth()->id())
                                        <button wire:click="delete({{ $user->id }})" wire:confirm="Anda yakin ingin menghapus pengguna ini?" class="p-2 rounded-full text-slate-400 hover:text-red-500" title="Hapus Data"><i class="fas fa-trash fa-sm"></i></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m-7.5-2.962a3.75 3.75 0 1 0-5.216 5.216 3.75 3.75 0 0 0 5.216-5.216zM12 10.5a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0z" />
                                </svg>
                                <h3 class="mt-2 text-lg font-semibold text-slate-800">Pengguna Tidak Ditemukan</h3>
                                <p class="mt-1 text-sm text-slate-500">Tidak ada data yang cocok dengan filter atau pencarian Anda.</p>
                             </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $users->links('vendor.livewire.tailwind-custom') }}</div>

    @if ($isModalOpen)
        <div x-data="{ show: @entangle('isModalOpen') }" x-show="show" x-transition.opacity.duration-300ms class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm" x-cloak>
            <div x-show="show" x-transition.scale.duration-300ms @click.away="show = false" class="w-full max-w-lg overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl">
                <form wire:submit.prevent="store">
                    <div class="flex items-center justify-between border-b border-slate-200 p-6">
                        <h3 class="flex items-center text-xl font-bold text-slate-800"><i class="fas {{ $isEditMode ? 'fa-user-edit' : 'fa-user-plus' }} mr-3 text-slate-400"></i><span>{{ $isEditMode ? 'Ubah Pengguna' : 'Tambah Pengguna Baru' }}</span></h3>
                        <button type="button" @click="show = false" class="text-3xl text-slate-400 hover:text-slate-600">&times;</button>
                    </div>
                    <div class="space-y-6 p-8">
                        <div>
                            <label for="name" class="text-xs font-semibold uppercase text-slate-500">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text" id="name" class="mt-1 block w-full border-0 border-b-2 border-slate-200 bg-transparent p-0 pb-2 text-slate-800 focus:border-amber-500 focus:ring-0" required>
                            @error('name')<span class="text-red-500 text-xs">{{$message}}</span>@enderror
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="username" class="text-xs font-semibold uppercase text-slate-500">Username <span class="text-red-500">*</span></label>
                                <input wire:model="username" type="text" id="username" class="mt-1 block w-full border-0 border-b-2 border-slate-200 bg-transparent p-0 pb-2 text-slate-800 focus:border-amber-500 focus:ring-0" required>
                                @error('username')<span class="text-red-500 text-xs">{{$message}}</span>@enderror
                            </div>
                            <div class="relative">
                                <label for="role" class="absolute -top-2 left-0 text-xs font-semibold uppercase text-slate-500">Peran (Role) <span class="text-red-500">*</span></label>
                                <select wire:model="role" id="role" class="mt-1 block w-full appearance-none border-0 border-b-2 border-slate-200 bg-transparent px-0 pt-4 pb-2 text-slate-800 focus:border-amber-500 focus:ring-0" style="background-image: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 20 20%27%3e%3cpath stroke=%27%236b7280%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%271.5%27 d=%27m6 8 4 4 4-4%27/%3e%3c/svg%3e'); background-position: right 0 center; background-repeat: no-repeat; background-size: 1.5em 1.5em;" required>
                                    <option value="">-- Pilih Peran --</option>
                                    <option value="admin">Admin</option>
                                    <option value="produksi">Staff Produksi</option>
                                    <option value="pengiriman">Staff Pengiriman</option>
                                </select>
                                @error('role')<span class="text-red-500 text-xs">{{$message}}</span>@enderror
                            </div>
                        </div>
                        <hr class="border-slate-200"/>
                        <p class="text-sm text-slate-500 -mb-2">{{ $isEditMode ? 'Kosongkan jika tidak ingin mengubah password.' : 'Password untuk pengguna baru.' }}</p>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                             <div>
                                <label for="password" class="text-xs font-semibold uppercase text-slate-500">Password @if(!$isEditMode)<span class="text-red-500">*</span>@endif</label>
                                <input wire:model="password" type="password" id="password" class="mt-1 block w-full border-0 border-b-2 border-slate-200 bg-transparent p-0 pb-2 text-slate-800 focus:border-amber-500 focus:ring-0">
                                @error('password')<span class="text-red-500 text-xs">{{$message}}</span>@enderror
                            </div>
                             <div>
                                <label for="password_confirmation" class="text-xs font-semibold uppercase text-slate-500">Konfirmasi Password @if(!$isEditMode)<span class="text-red-500">*</span>@endif</label>
                                <input wire:model="password_confirmation" type="password" id="password_confirmation" class="mt-1 block w-full border-0 border-b-2 border-slate-200 bg-transparent p-0 pb-2 text-slate-800 focus:border-amber-500 focus:ring-0">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 rounded-b-xl border-t border-slate-200 bg-slate-50 p-6">
                        <button type="button" @click="show = false" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>