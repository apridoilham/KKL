<div class="container mx-auto px-4 py-6 md:px-6">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900">Profil Saya</h1>
        <p class="mt-1 text-slate-500">Kelola informasi akun dan keamanan Anda.</p>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <div class="flex items-center space-x-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-200 text-2xl font-bold text-amber-600">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">{{ $name }}</h2>
                        <p class="text-sm text-slate-500 capitalize">{{ auth()->user()->role }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-8">
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <form wire:submit.prevent="changeData">
                    <div class="border-b border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-slate-800">Ubah Data Diri</h3>
                    </div>
                    <div class="space-y-6 p-6">
                        <div>
                            <label for="name" class="text-sm font-medium text-slate-700">Nama Lengkap</label>
                            <input wire:model="name" id="name" type="text" class="mt-1 block w-full rounded-lg border border-slate-300 py-2 px-3 focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                            @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="username" class="text-sm font-medium text-slate-700">Username</label>
                            <input wire:model="username" id="username" type="text" class="mt-1 block w-full rounded-lg border border-slate-300 py-2 px-3 focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                             @error('username') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <hr>
                        <div>
                            <label for="confirmation_password_data" class="text-sm font-medium text-slate-700">Konfirmasi dengan Password Anda</label>
                            <input wire:model="confirmation_password" id="confirmation_password_data" type="password" class="mt-1 block w-full rounded-lg border border-slate-300 py-2 px-3 focus:border-amber-500 focus:ring-1 focus:ring-amber-500" required>
                             @error('confirmation_password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="flex justify-end rounded-b-xl border-t border-slate-200 bg-slate-50 p-6">
                        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Simpan Perubahan Data</button>
                    </div>
                </form>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <form wire:submit.prevent="changePassword">
                    <div class="border-b border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-slate-800">Ubah Password</h3>
                    </div>
                    <div class="space-y-6 p-6">
                        <div>
                            <label for="current_password" class="text-sm font-medium text-slate-700">Password Saat Ini</label>
                            <input wire:model="current_password" id="current_password" type="password" class="mt-1 block w-full rounded-lg border border-slate-300 py-2 px-3 focus:border-amber-500 focus:ring-1 focus:ring-amber-500" required>
                            @error('current_password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="new_password" class="text-sm font-medium text-slate-700">Password Baru</label>
                            <input wire:model="new_password" id="new_password" type="password" class="mt-1 block w-full rounded-lg border border-slate-300 py-2 px-3 focus:border-amber-500 focus:ring-1 focus:ring-amber-500" required>
                             @error('new_password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="new_password_confirmation" class="text-sm font-medium text-slate-700">Konfirmasi Password Baru</label>
                            <input wire:model="new_password_confirmation" id="new_password_confirmation" type="password" class="mt-1 block w-full rounded-lg border border-slate-300 py-2 px-3 focus:border-amber-500 focus:ring-1 focus:ring-amber-500" required>
                        </div>
                    </div>
                    <div class="flex justify-end rounded-b-xl border-t border-slate-200 bg-slate-50 p-6">
                        <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-400">Simpan Password Baru</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>