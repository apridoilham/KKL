<div 
    x-show="sidebarOpen"
    @click.away="if (window.innerWidth < 1024) sidebarOpen = false"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed inset-y-0 left-0 z-20 flex w-64 flex-shrink-0 flex-col border-r border-slate-200 bg-white lg:relative lg:translate-x-0"
    x-cloak
>
    <div class="flex h-16 flex-shrink-0 items-center justify-center border-b border-slate-200 px-4">
        <a href="/" class="flex items-center gap-x-3 text-slate-900">
            <svg class="h-7 w-auto text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
            </svg>
            <span class="text-lg font-bold tracking-widest">INVENTARIS</span>
        </a>
    </div>
    
    <nav class="flex-1 space-y-2 overflow-y-auto p-4">
        @can('view-dashboard')
        <a class="flex items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-colors {{ $data['urlPath'] == 'home' ? 'bg-amber-50 text-amber-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}" href="/">
            <i class="fas fa-fw fa-tachometer-alt w-5 text-center"></i>
            <span class="mx-4">Dashboard</span>
        </a>
        @endcan
        
        <p class="px-4 pt-4 pb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase">Manajemen</p>

        <div x-data="{ open: {{ request()->is('item*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between rounded-lg px-4 py-2.5 text-sm font-medium transition-colors text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                <span class="flex items-center">
                    <i class="fas fa-fw fa-box w-5 text-center"></i>
                    <span class="mx-4">Barang</span>
                </span>
                <i class="fas text-xs" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i>
            </button>
            <div x-show="open" x-transition class="mt-2 space-y-2 pl-6">
                <a href="/item?type=barang_mentah" class="block rounded-lg px-4 py-2.5 text-sm transition-colors {{ request()->get('type') == 'barang_mentah' ? 'bg-amber-50 text-amber-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    Bahan Mentah
                </a>
                <a href="/item?type=barang_jadi" class="block rounded-lg px-4 py-2.5 text-sm transition-colors {{ request()->get('type') == 'barang_jadi' ? 'bg-amber-50 text-amber-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    Barang Jadi
                </a>
            </div>
        </div>
        
        <a class="flex items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-colors {{ $data['urlPath'] == 'transaction' ? 'bg-amber-50 text-amber-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}" href="/transaction">
            <i class="fas fa-fw fa-exchange-alt w-5 text-center"></i>
            <span class="mx-4">Transaksi</span>
        </a>

        @can('manage-production')
        <a class="flex items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-colors {{ $data['urlPath'] == 'production' ? 'bg-amber-50 text-amber-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}" href="/production">
            <i class="fas fa-fw fa-industry w-5 text-center"></i>
            <span class="mx-4">Produksi</span>
        </a>
        @endcan
        
        @can('manage-users')
        <a class="flex items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-colors {{ $data['urlPath'] == 'user' ? 'bg-amber-50 text-amber-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}" href="/user">
            <i class="fas fa-fw fa-users w-5 text-center"></i>
            <span class="mx-4">Pengguna</span>
        </a>
        @endcan

        @can('view-reports')
            <p class="px-4 pt-4 pb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase">Laporan</p>
            <a class="flex items-center rounded-lg px-4 py-2.5 text-sm font-medium transition-colors {{ $data['urlPath'] == 'report' ? 'bg-amber-50 text-amber-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}" href="/report">
                <i class="fas fa-fw fa-chart-line w-5 text-center"></i>
                <span class="mx-4">Laporan</span>
            </a>
        @endcan
    </nav>
</div>