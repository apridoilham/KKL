<header class="flex h-16 flex-shrink-0 items-center justify-between border-b border-slate-200 bg-white px-6">
    <div class="flex items-center">
        <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 focus:outline-none lg:hidden">
            <i class="fas fa-bars fa-lg"></i>
        </button>
    </div>

    <div class="flex items-center">
        <div x-data="{ dropdownOpen: false }" class="relative">
            <button @click="dropdownOpen = !dropdownOpen" class="flex items-center focus:outline-none">
                <div class="flex items-center">
                    <div class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-sm font-bold text-amber-600">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="hidden text-left md:block">
                        <div class="font-semibold text-slate-800">{{ auth()->user()->name }}</div>
                        <div class="text-xs capitalize text-slate-500">{{ auth()->user()->role }}</div>
                    </div>
                </div>
            </button>

            <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition class="absolute right-0 z-10 mt-2 w-48 rounded-lg bg-white py-2 shadow-xl ring-1 ring-slate-200" x-cloak>
                <a href="{{ route('profile') }}" class="flex w-full items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
                    <i class="fas fa-user-circle fa-sm fa-fw mr-2 text-slate-400"></i> Profil Saya
                </a>
                <a href="{{ route('logout') }}" class="flex w-full items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-slate-400"></i> Logout
                </a>
            </div>
        </div>
    </div>
</header>