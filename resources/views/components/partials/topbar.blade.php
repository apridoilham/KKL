<header class="flex items-center justify-between px-6 py-3 bg-white border-b-2 border-slate-200">
    <div class="flex items-center">
        <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 focus:outline-none md:hidden">
            <i class="fas fa-bars fa-lg"></i>
        </button>
    </div>

    <div class="flex items-center">
        <div x-data="{ dropdownOpen: false }" class="relative">
            <button @click="dropdownOpen = !dropdownOpen" class="flex items-center focus:outline-none">
                <div class="flex items-center">
                    <div class="user-avatar h-10 w-10 text-sm mr-3">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="hidden md:block text-left">
                        <div class="font-semibold text-slate-800">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-slate-500 capitalize">{{ auth()->user()->role }}</div>
                    </div>
                </div>
            </button>

            <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-10" x-cloak>
                <a href="/logout" class="block px-4 py-2 text-sm text-slate-700 hover:bg-indigo-500 hover:text-white">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2"></i> Logout
                </a>
            </div>
        </div>
    </div>
</header>