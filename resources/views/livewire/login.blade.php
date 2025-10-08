<div>
    @section('title', $data['title'] ?? 'Login')
    <div class="antialiased font-sans">
        <div class="min-h-screen flex items-center justify-center bg-slate-100 p-6">
            
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-2xl md:p-12">
                
                <div class="mb-12 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 border-2 border-amber-200">
                        <svg class="h-8 w-8 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                        </svg>
                    </div>
                    <h1 class="mt-6 text-3xl font-bold tracking-tighter text-slate-900">
                        {{ $checkData > 0 ? 'Selamat Datang' : 'Administrator Setup' }}
                    </h1>
                    <p class="mt-2 text-sm text-slate-500">
                        Masuk untuk mengakses panel kontrol inventaris.
                    </p>
                </div>

                <form class="space-y-7" wire:submit.prevent='submit'>
                    <div>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <i class="fas fa-user fa-fw"></i>
                            </div>
                            <input wire:model.blur='username' id="username" type="text" class="w-full rounded-lg border border-slate-300 bg-slate-50 py-3 pl-12 pr-4 text-slate-900 placeholder-slate-400 transition-colors focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500" placeholder="Username" required>
                        </div>
                        @error('username') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    
                    <div x-data="{ show: false }">
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <i class="fas fa-lock fa-fw"></i>
                            </div>
                            <input wire:model.blur='password' id="password" :type="show ? 'text' : 'password'" class="w-full rounded-lg border border-slate-300 bg-slate-50 py-3 pl-12 pr-10 text-slate-900 placeholder-slate-400 transition-colors focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500" placeholder="Password" required>
                            <div class="absolute inset-y-0 right-0 flex items-center">
                                <button type="button" @click="show = !show" class="p-3 text-slate-400 hover:text-slate-600">
                                    <i class="fas" :class="{ 'fa-eye-slash': show, 'fa-eye': !show }"></i>
                                </button>
                            </div>
                        </div>
                        @error('password') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    @if ($checkData > 0)
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center">
                                <input wire:model="remember" id="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                <label for="remember" class="ml-2 block text-slate-700">Ingat saya</label>
                            </div>
                            <a href="#" wire:click.prevent="$set('isModalOpen', true)" class="font-medium text-amber-600 hover:text-amber-500">Lupa password?</a>
                        </div>
                    @endif
                    
                    @if (session()->has('dataSession'))
                        <div class="text-center text-sm {{ session('dataSession')['status'] == 'success' ? 'text-green-600' : 'text-red-600' }}">
                            {{ session('dataSession')['message'] }}
                        </div>
                    @endif

                    <div class="pt-4">
                        <button type="submit" wire:loading.attr="disabled" class="flex w-full items-center justify-center rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold uppercase tracking-widest text-white shadow-lg transition-transform hover:bg-slate-800 hover:scale-[1.02] active:scale-100 disabled:bg-slate-400">
                            <span wire:loading.remove wire:target="submit">{{ $checkData > 0 ? 'Login' : 'Buat Akun' }}</span>
                            <span wire:loading wire:target="submit">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        @if ($isModalOpen)
            <div x-data="{ show: @entangle('isModalOpen') }" x-show="show" x-transition.opacity.duration-300ms class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm" x-cloak>
                <div x-show="show" x-transition.scale.duration-300ms @click.away="show = false" class="w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-2xl border">
                    <div class="flex items-center justify-between border-b p-6">
                        <h3 class="flex items-center text-xl font-bold text-slate-800"><i class="fas fa-question-circle mr-3 text-amber-500"></i><span>Lupa Password</span></h3>
                        <button type="button" @click="show = false" class="text-3xl text-slate-400 hover:text-slate-600">&times;</button>
                    </div>
                    
                    @if (!$isVerified)
                        <form wire:submit.prevent="verifyData" novalidate>
                            <div class="space-y-6 p-8">
                                @if (session()->has('dataSession2'))
                                    <div class="rounded-lg p-4 text-sm {{ session('dataSession2')['status'] == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ session('dataSession2')['message'] }}
                                    </div>
                                @endif
                                <div>
                                    <label for="modal_username" class="text-sm font-medium text-slate-700">Username Anda</label>
                                    <div class="relative mt-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <i class="fas fa-user text-slate-400"></i>
                                        </div>
                                        <input wire:model.lazy="username" id="modal_username" type="text" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" placeholder="Masukkan username Anda..." required>
                                    </div>
                                    @error('username') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                @if ($isUserFound)
                                    <div>
                                        <label for="modal_security_answer" class="text-sm font-medium text-slate-700">Pertanyaan: {{ $securityQuestion }}</label>
                                        <div class="relative mt-1">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <i class="fas fa-key text-slate-400"></i>
                                            </div>
                                            <input wire:model="securityAnswer" id="modal_security_answer" type="text" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" placeholder="Masukkan jawaban Anda..." required>
                                        </div>
                                        @error('securityAnswer') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                            <div class="flex justify-end space-x-3 border-t bg-slate-50 p-6">
                                <button type="button" @click="show = false" class="rounded-lg border bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100">Batal</button>
                                <button type="submit" class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                                    <span wire:loading.remove wire:target="verifyData">Verifikasi</span>
                                    <span wire:loading wire:target="verifyData">Memverifikasi...</span>
                                </button>
                            </div>
                        </form>
                    @else
                        <form wire:submit.prevent="changePassword" novalidate>
                            <div class="space-y-6 p-8">
                                <p class="text-sm text-slate-600">Verifikasi berhasil. Silakan masukkan password baru Anda.</p>
                                <div>
                                    <label for="newPassword" class="text-sm font-medium text-slate-700">Password Baru</label>
                                    <div class="relative mt-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <i class="fas fa-lock text-slate-400"></i>
                                        </div>
                                        <input wire:model="newPassword" id="newPassword" type="password" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" required>
                                    </div>
                                    @error('newPassword') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="confPass" class="text-sm font-medium text-slate-700">Konfirmasi Password Baru</label>
                                    <div class="relative mt-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <i class="fas fa-lock text-slate-400"></i>
                                        </div>
                                        <input wire:model="confPass" id="confPass" type="password" class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" required>
                                    </div>
                                    @error('confPass') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="flex justify-end space-x-3 border-t bg-slate-50 p-6">
                                <button type="button" @click="show = false" class="rounded-lg border bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100">Batal</button>
                                <button type="submit" class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                                    Ubah Password
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>