<div 
    x-show="sidebarOpen"
    @click.away="if (window.innerWidth < 768) sidebarOpen = false"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed inset-y-0 left-0 w-64 bg-indigo-800 text-white flex-shrink-0 z-20 md:relative md:translate-x-0"
    x-cloak
>
    <div class="flex items-center justify-center h-16 bg-indigo-900 shadow-md">
        <a href="/" class="flex items-center space-x-2 text-white">
            <i class="fas fa-boxes text-2xl text-indigo-300"></i>
            <span class="text-xl font-bold tracking-wider">INVENTARIS</span>
        </a>
    </div>
    
    <nav class="mt-6 px-4">
        <a class="flex items-center px-4 py-2.5 mt-2 text-indigo-200 rounded-lg transition-colors duration-200 hover:bg-indigo-700 hover:text-white {{ $data['urlPath'] == 'home' ? 'bg-indigo-700 text-white' : '' }}" href="/">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span class="mx-4 font-medium">Dashboard</span>
        </a>
        
        <p class="px-4 mt-6 mb-2 text-xs font-semibold tracking-wider text-indigo-400 uppercase">Manajemen</p>

        <div x-data="{ open: {{ request()->is('item*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 mt-2 text-indigo-200 rounded-lg transition-colors duration-200 hover:bg-indigo-700 hover:text-white">
                <span class="flex items-center">
                    <i class="fas fa-fw fa-box"></i>
                    <span class="mx-4 font-medium">Barang</span>
                </span>
                <i class="fas text-xs" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i>
            </button>
            <div x-show="open" x-transition class="mt-2 space-y-2 pl-8">
                <a href="/item?type=barang_mentah" class="block py-2 text-indigo-300 hover:text-white {{ request()->get('type') == 'barang_mentah' ? 'font-bold text-white' : '' }}">
                    Barang Mentah
                </a>
                <a href="/item?type=barang_jadi" class="block py-2 text-indigo-300 hover:text-white {{ request()->get('type') == 'barang_jadi' ? 'font-bold text-white' : '' }}">
                    Barang Jadi
                </a>
            </div>
        </div>
        
        <a class="flex items-center px-4 py-2.5 mt-2 text-indigo-200 rounded-lg transition-colors duration-200 hover:bg-indigo-700 hover:text-white {{ $data['urlPath'] == 'transaction' ? 'bg-indigo-700 text-white' : '' }}" href="/transaction">
            <i class="fas fa-fw fa-exchange-alt"></i>
            <span class="mx-4 font-medium">Transaksi</span>
        </a>

        @can('manage-production')
        <a class="flex items-center px-4 py-2.5 mt-2 text-indigo-200 rounded-lg transition-colors duration-200 hover:bg-indigo-700 hover:text-white {{ $data['urlPath'] == 'production' ? 'bg-indigo-700 text-white' : '' }}" href="/production">
            <i class="fas fa-fw fa-industry"></i>
            <span class="mx-4 font-medium">Produksi</span>
        </a>
        @endcan
        
        @if(auth()->user()->role === 'admin')
        <a class="flex items-center px-4 py-2.5 mt-2 text-indigo-200 rounded-lg transition-colors duration-200 hover:bg-indigo-700 hover:text-white {{ $data['urlPath'] == 'user' ? 'bg-indigo-700 text-white' : '' }}" href="/user">
            <i class="fas fa-fw fa-users"></i>
            <span class="mx-4 font-medium">Pengguna</span>
        </a>
        @endif

        <p class="px-4 mt-6 mb-2 text-xs font-semibold tracking-wider text-indigo-400 uppercase">Laporan</p>

        <a class="flex items-center px-4 py-2.5 mt-2 text-indigo-200 rounded-lg transition-colors duration-200 hover:bg-indigo-700 hover:text-white {{ $data['urlPath'] == 'report' ? 'bg-indigo-700 text-white' : '' }}" href="/report">
            <i class="fas fa-fw fa-chart-line"></i>
            <span class="mx-4 font-medium">Laporan</span>
        </a>
    </nav>
</div>