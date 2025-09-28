@section('title', $data['title'] ?? 'Login')
<div class="bg-slate-100 flex items-center justify-center min-h-screen font-sans">

    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-xl shadow-2xl">
        
        <div class="flex flex-col items-center space-y-2">
            <svg class="w-16 h-16 text-indigo-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M22 8.5h-3.5v-3a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5v3H1a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h21a1 1 0 0 0 1-1v-10a1 1 0 0 0-1-1Zm-16.5-2h10v2h-10Zm15.5 12H2v-8h20Z"/>
                <path d="M10.5 14.5h-4a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5Zm-3.5-1h3v-1h-3Z"/>
            </svg>
            <h1 class="text-3xl font-bold text-slate-800 text-center">
                {{ $checkData > 0 ? 'Selamat Datang!' : 'Buat Akun Admin' }}
            </h1>
            <p class="text-sm text-slate-500">Sistem Manajemen Inventaris</p>
        </div>

        <form class="space-y-6" wire:submit.prevent='submit'>
            <div>
                <label for="username" class="text-sm font-medium text-slate-700">Username</label>
                {{-- PERBAIKAN: Input field diubah menjadi kotak --}}
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-slate-400"></i>
                    </div>
                    <input wire:model='username' id="username" type="text" class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg bg-slate-50 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                @error('username') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label for="password" class="text-sm font-medium text-slate-700">Password</label>
                {{-- PERBAIKAN: Input field diubah menjadi kotak --}}
                <div class="relative mt-1">
                     <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-slate-400"></i>
                    </div>
                    <input wire:model='password' id="password" type="password" class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg bg-slate-50 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            @if ($checkData == 0)
                {{-- Fields untuk user pertama kali --}}
            @endif
            
            @if (session()->has('dataSession'))<div class="text-center text-sm {{ session('dataSession')['status'] == 'success' ? 'text-green-600' : 'text-red-600' }}">{{ session('dataSession')['message'] }}</div>@endif

            <div>
                <button type="submit" wire:loading.attr="disabled" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:bg-indigo-400">
                    <span wire:loading.remove wire:target="submit">{{ $checkData > 0 ? 'Login' : 'Buat & Login' }}</span>
                    <span wire:loading wire:target="submit">Memproses...</span>
                </button>
            </div>
        </form>

        <div class="text-center">
            <a href="#" wire:click.prevent="$set('isModalOpen', true)" class="text-sm text-indigo-600 hover:text-indigo-500 hover:underline">
                Lupa Password?
            </a>
        </div>
    </div>

    @if ($isModalOpen)
        {{-- Kode Modal tidak berubah signifikan, namun input di dalamnya juga akan mengikuti gaya baru jika diperlukan --}}
        <div x-data="{ show: @entangle('isModalOpen') }" x-show="show" x-transition.opacity.duration.300ms class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
            <div x-show="show" x-transition.scale.duration.300ms @click.away="show = false" class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
                {{-- Isi Modal Lupa Password --}}
            </div>
        </div>
    @endif
</div>