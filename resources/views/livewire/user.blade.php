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
                <form wire:submit.prevent="store" novalidate>
                    <div class="flex items-center justify-between border-b border-slate-200 p-6">
                        <h3 class="flex items-center text-xl font-bold text-slate-800"><i class="fas {{ $isEditMode ? 'fa-user-edit' : 'fa-user-plus' }} mr-3 text-slate-400"></i><span>{{ $isEditMode ? 'Ubah Pengguna' : 'Tambah Pengguna Baru' }}</span></h3>
                        <button type="button" @click="show = false" class="text-3xl text-slate-400 hover:text-slate-600">&times;</button>
                    </div>
                    <div class="space-y-6 p-8">
                        <div>
                            <label for="name" class="text-sm font-medium text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                            <div class="relative mt-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="fas fa-user text-slate-400"></i>
                                </div>
                                <input wire:model="name" id="name" type="text" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" placeholder="Masukkan nama lengkap..." required>
                            </div>
                            @error('name')<span class="text-xs text-red-500 mt-1">{{$message}}</span>@enderror
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="username" class="text-sm font-medium text-slate-700">Username <span class="text-red-500">*</span></label>
                                <div class="relative mt-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-at text-slate-400"></i>
                                    </div>
                                    <input wire:model="username" id="username" type="text" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" placeholder="Masukkan username..." required>
                                </div>
                                @error('username')<span class="text-xs text-red-500 mt-1">{{$message}}</span>@enderror
                            </div>
                            <div>
                                <label for="role" class="text-sm font-medium text-slate-700">Peran (Role) <span class="text-red-500">*</span></label>
                                <select wire:model="role" id="role" class="mt-1 block w-full appearance-none rounded-lg border border-slate-300 bg-white py-2.5 px-3 text-slate-800 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" required>
                                    <option value="">-- Pilih Peran --</option>
                                    <option value="admin">Admin</option>
                                    <option value="produksi">Staff Produksi</option>
                                    <option value="pengiriman">Staff Pengiriman</option>
                                </select>
                                @error('role')<span class="text-xs text-red-500 mt-1">{{$message}}</span>@enderror
                            </div>
                        </div>
                        <hr class="border-slate-200"/>
                        <p class="text-sm text-slate-500 -my-2">Pertanyaan keamanan untuk fitur Lupa Password (opsional).</p>
                        <div>
                            <label for="security_question" class="text-sm font-medium text-slate-700">Pertanyaan Keamanan</label>
                            <div class="relative mt-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="fas fa-question-circle text-slate-400"></i>
                                </div>
                                <input wire:model="security_question" type="text" id="security_question" placeholder="cth: Siapa nama hewan peliharaan Anda?" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            </div>
                            @error('security_question')<span class="text-xs text-red-500 mt-1">{{$message}}</span>@enderror
                        </div>
                        <div>
                            <label for="security_answer" class="text-sm font-medium text-slate-700">Jawaban Keamanan</label>
                            <div class="relative mt-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="fas fa-key text-slate-400"></i>
                                </div>
                                <input wire:model="security_answer" type="text" id="security_answer" placeholder="Jawaban (case-sensitive)" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            </div>
                            @error('security_answer')<span class="text-xs text-red-500 mt-1">{{$message}}</span>@enderror
                        </div>
                        <hr class="border-slate-200"/>
                        <p class="text-sm text-slate-500 -my-2">{{ $isEditMode ? 'Kosongkan jika tidak ingin mengubah password.' : 'Password untuk pengguna baru.' }}</p>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                             <div>
                                <label for="password" class="text-sm font-medium text-slate-700">Password @if(!$isEditMode)<span class="text-red-500">*</span>@endif</label>
                                <div class="relative mt-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-lock text-slate-400"></i>
                                    </div>
                                    <input wire:model="password" id="password" type="password" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                </div>
                                @error('password')<span class="text-xs text-red-500 mt-1">{{$message}}</span>@enderror
                            </div>
                             <div>
                                <label for="password_confirmation" class="text-sm font-medium text-slate-700">Konfirmasi Password @if(!$isEditMode)<span class="text-red-500">*</span>@endif</label>
                                <div class="relative mt-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-lock text-slate-400"></i>
                                    </div>
                                    <input wire:model="password_confirmation" id="password_confirmation" type="password" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                </div>
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