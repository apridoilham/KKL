<div class="container mx-auto px-4 py-6 md:px-6">

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900">Halo, {{ auth()->user()->name }}! 👋</h1>
        <p class="mt-1 text-slate-500">Selamat datang kembali, berikut ringkasan inventaris Anda.</p>
    </div>

    <div 
        x-data="{ 
            open: false, 
            activeFilter: '{{ $filterType }}',
            dateValue: '{{ $filterDate }}',
            monthValue: '{{ \Carbon\Carbon::parse($filterMonth)->format('Y-m') }}',
            yearValue: '{{ $filterYear }}',
            selectedMonth: '{{ \Carbon\Carbon::parse($filterMonth)->format('m') }}',
            selectedYearForMonth: '{{ \Carbon\Carbon::parse($filterMonth)->format('Y') }}',
        }" 
        x-init="$watch('selectedMonth', () => { monthValue = selectedYearForMonth + '-' + selectedMonth }); $watch('selectedYearForMonth', () => { monthValue = selectedYearForMonth + '-' + selectedMonth })"
        class="mb-6"
    >
        <div class="relative">
            <button @click="open = !open" type="button" class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white p-4 text-left shadow-sm sm:w-auto">
                <div class="flex items-center gap-x-3">
                    <i class="fas fa-calendar-alt text-slate-400"></i>
                    <div>
                        <p class="text-xs text-slate-500">Filter Waktu</p>
                        <p class="font-semibold text-slate-800">
                            @if($filterType == 'daily')
                                {{ \Carbon\Carbon::parse($filterDate)->format('d F Y') }}
                            @elseif($filterType == 'monthly')
                                {{ \Carbon\Carbon::parse($filterMonth)->format('F Y') }}
                            @elseif($filterType == 'yearly')
                                {{ $filterYear }}
                            @else
                                Semua Waktu
                            @endif
                        </p>
                    </div>
                </div>
                <i class="fas fa-chevron-down text-slate-400 transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>

            <div 
                x-show="open" 
                @click.away="open = false" 
                x-transition
                class="absolute top-full z-10 mt-2 w-full max-w-sm rounded-xl border border-slate-200 bg-white p-4 shadow-lg"
                style="display: none;"
            >
                <div class="flex items-center space-x-1 rounded-lg bg-slate-100 p-1 mb-4">
                    <button @click="activeFilter = 'all_time'; $wire.resetFilters()" type="button" class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors" :class="{ 'bg-white text-slate-800 shadow-sm': activeFilter === 'all_time', 'text-slate-500 hover:text-slate-700': activeFilter !== 'all_time' }">Semua Waktu</button>
                    <button @click="activeFilter = 'daily'" type="button" class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors" :class="{ 'bg-white text-slate-800 shadow-sm': activeFilter === 'daily', 'text-slate-500 hover:text-slate-700': activeFilter !== 'daily' }">Harian</button>
                    <button @click="activeFilter = 'monthly'" type="button" class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors" :class="{ 'bg-white text-slate-800 shadow-sm': activeFilter === 'monthly', 'text-slate-500 hover:text-slate-700': activeFilter !== 'monthly' }">Bulanan</button>
                    <button @click="activeFilter = 'yearly'" type="button" class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors" :class="{ 'bg-white text-slate-800 shadow-sm': activeFilter === 'yearly', 'text-slate-500 hover:text-slate-700': activeFilter !== 'yearly' }">Tahunan</button>
                </div>

                <div class="mb-4">
                    <div x-show="activeFilter === 'daily'"><input x-model="dateValue" type="date" class="w-full rounded-lg border-slate-300 py-2 px-3 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"></div>
                    <div x-show="activeFilter === 'monthly'" class="flex items-center gap-x-2">
                        <select x-model="selectedMonth" class="w-full rounded-lg border-slate-300 py-2 px-3 focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                            @endfor
                        </select>
                        <select x-model="selectedYearForMonth" class="w-full rounded-lg border-slate-300 py-2 px-3 focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                            @php $currentYear = date('Y'); @endphp
                            @for ($year = $currentYear - 5; $year <= $currentYear + 1; $year++)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div x-show="activeFilter === 'yearly'"><input x-model="yearValue" type="number" class="w-full rounded-lg border-slate-300 py-2 px-3 focus:border-amber-500 focus:ring-1 focus:ring-amber-500" placeholder="Tahun..."></div>
                </div>

                <button 
                    @click="if (activeFilter !== 'all_time') { $wire.applyDashboardFilter(activeFilter, dateValue, monthValue, yearValue) }; open = false"
                    type="button"
                    class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800"
                    x-show="activeFilter !== 'all_time'"
                >
                    Terapkan
                </button>
            </div>
        </div>
    </div>
    
    <div class="relative">
        <div wire:loading.flex wire:target="loadDashboardData, resetFilters, applyDashboardFilter" class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-white/80 backdrop-blur-sm">
            <i class="fas fa-spinner fa-spin text-amber-500 text-3xl"></i>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            
            <div class="group h-[116px]" style="perspective: 1000px">
                <div x-data="{ flipped: false }" @click="flipped = !flipped" class="relative h-full w-full cursor-pointer transition-transform duration-500" style="transform-style: preserve-3d;" :class="{ '[transform:rotateY(180deg)]': flipped }">
                    <div class="absolute h-full w-full rounded-2xl border border-slate-200 bg-white p-6" style="backface-visibility: hidden;">
                        <p class="text-sm font-medium text-slate-500">Total Jenis Barang</p><p class="mt-1 text-3xl font-bold text-slate-900">{{ $totalItems }}</p>
                        <div class="absolute bottom-4 right-4 text-slate-300 transition-colors group-hover:text-slate-500"><i class="fas fa-sync-alt"></i></div>
                    </div>
                    <div class="absolute h-full w-full rounded-2xl border border-slate-200 bg-white p-6" style="backface-visibility: hidden; transform: rotateY(180deg);">
                        <div class="flex h-full items-center justify-around text-center"><div class="h-full w-px bg-slate-200 absolute left-1/2 top-0 -ml-px"></div><div><p class="text-xs font-medium text-slate-500">Bahan Mentah</p><p class="mt-1 text-3xl font-bold text-slate-900">{{ $totalRawItems }}</p></div><div><p class="text-xs font-medium text-slate-500">Barang Jadi</p><p class="mt-1 text-3xl font-bold text-slate-900">{{ $totalFinishedItems }}</p></div></div>
                        <div class="absolute bottom-4 right-4 text-slate-300 transition-colors group-hover:text-slate-500"><i class="fas fa-sync-alt"></i></div>
                    </div>
                </div>
            </div>
            <div class="group h-[116px]" style="perspective: 1000px">
                <div x-data="{ flipped: false }" @click="flipped = !flipped" class="relative h-full w-full cursor-pointer transition-transform duration-500" style="transform-style: preserve-3d;" :class="{ '[transform:rotateY(180deg)]': flipped }">
                    <div class="absolute h-full w-full rounded-2xl border border-slate-200 bg-white p-6" style="backface-visibility: hidden;">
                        <p class="text-sm font-medium text-slate-500">Total Stok Tersedia</p><p class="mt-1 text-3xl font-bold text-slate-900">{{ $totalStock }}</p>
                        <div class="absolute bottom-4 right-4 text-slate-300 transition-colors group-hover:text-slate-500"><i class="fas fa-sync-alt"></i></div>
                    </div>
                    <div class="absolute h-full w-full rounded-2xl border border-slate-200 bg-white p-6" style="backface-visibility: hidden; transform: rotateY(180deg);">
                        <div class="flex h-full items-center justify-around text-center"><div class="h-full w-px bg-slate-200 absolute left-1/2 top-0 -ml-px"></div><div><p class="text-xs font-medium text-slate-500">Bahan Mentah</p><p class="mt-1 text-3xl font-bold text-slate-900">{{ $totalRawStock }}</p></div><div><p class="text-xs font-medium text-slate-500">Barang Jadi</p><p class="mt-1 text-3xl font-bold text-slate-900">{{ $totalFinishedStock }}</p></div></div>
                        <div class="absolute bottom-4 right-4 text-slate-300 transition-colors group-hover:text-slate-500"><i class="fas fa-sync-alt"></i></div>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <p class="text-sm font-medium text-slate-500">Total Pengguna</p><p class="mt-1 text-3xl font-bold text-slate-900">{{ $totalUsers }}</p>
            </div>
            <div class="group h-[116px]" style="perspective: 1000px">
                <div x-data="{ flipped: false }" @click="flipped = !flipped" class="relative h-full w-full cursor-pointer transition-transform duration-500" style="transform-style: preserve-3d;" :class="{ '[transform:rotateY(180deg)]': flipped }">
                    <div class="absolute h-full w-full rounded-2xl border bg-green-50 border-green-200 p-6" style="backface-visibility: hidden;">
                        <p class="text-sm font-medium text-green-600">Total Barang Masuk</p><p class="mt-1 text-3xl font-bold text-green-800">{{ $totalIn }}</p>
                        <div class="absolute bottom-4 right-4 text-green-300 transition-colors group-hover:text-green-500"><i class="fas fa-sync-alt"></i></div>
                    </div>
                    <div class="absolute h-full w-full rounded-2xl border bg-green-50 border-green-200 p-6" style="backface-visibility: hidden; transform: rotateY(180deg);">
                        <div class="flex h-full items-center justify-around text-center"><div class="h-full w-px bg-green-200 absolute left-1/2 top-0 -ml-px"></div><div><p class="text-xs font-medium text-green-600">Bahan Mentah</p><p class="mt-1 text-3xl font-bold text-green-800">{{ $totalInRaw }}</p></div><div><p class="text-xs font-medium text-green-600">Barang Jadi</p><p class="mt-1 text-3xl font-bold text-green-800">{{ $totalInFinished }}</p></div></div>
                        <div class="absolute bottom-4 right-4 text-green-300 transition-colors group-hover:text-green-500"><i class="fas fa-sync-alt"></i></div>
                    </div>
                </div>
            </div>
            <div class="group h-[116px]" style="perspective: 1000px">
                <div x-data="{ flipped: false }" @click="flipped = !flipped" class="relative h-full w-full cursor-pointer transition-transform duration-500" style="transform-style: preserve-3d;" :class="{ '[transform:rotateY(180deg)]': flipped }">
                    <div class="absolute h-full w-full rounded-2xl border bg-yellow-50 border-yellow-200 p-6" style="backface-visibility: hidden;">
                        <p class="text-sm font-medium text-yellow-600">Total Barang Keluar</p><p class="mt-1 text-3xl font-bold text-yellow-800">{{ $totalOut }}</p>
                        <div class="absolute bottom-4 right-4 text-yellow-300 transition-colors group-hover:text-yellow-500"><i class="fas fa-sync-alt"></i></div>
                    </div>
                    <div class="absolute h-full w-full rounded-2xl border bg-yellow-50 border-yellow-200 p-6" style="backface-visibility: hidden; transform: rotateY(180deg);">
                        <div class="flex h-full items-center justify-around text-center">
                            <div><p class="text-xs font-medium text-yellow-600">Bahan Terpakai</p><p class="mt-1 text-2xl font-bold text-yellow-800">{{ $totalOutUsed }}</p></div><div class="h-full w-px bg-yellow-200"></div><div><p class="text-xs font-medium text-yellow-600">Kirim Bahan Mentah</p><p class="mt-1 text-2xl font-bold text-yellow-800">{{ $totalOutShippedRaw }}</p></div><div class="h-full w-px bg-yellow-200"></div><div><p class="text-xs font-medium text-yellow-600">Kirim Barang Jadi</p><p class="mt-1 text-2xl font-bold text-yellow-800">{{ $totalOutShippedFinished }}</p></div>
                        </div>
                        <div class="absolute bottom-4 right-4 text-yellow-300 transition-colors group-hover:text-yellow-500"><i class="fas fa-sync-alt"></i></div>
                    </div>
                </div>
            </div>
            <div class="group h-[116px]" style="perspective: 1000px">
                <div x-data="{ flipped: false }" @click="flipped = !flipped" class="relative h-full w-full cursor-pointer transition-transform duration-500" style="transform-style: preserve-3d;" :class="{ '[transform:rotateY(180deg)]': flipped }">
                    <div class="absolute h-full w-full rounded-2xl border bg-red-50 border-red-200 p-6" style="backface-visibility: hidden;">
                        <p class="text-sm font-medium text-red-600">Total Barang Rusak</p><p class="mt-1 text-3xl font-bold text-red-800">{{ $totalDamaged }}</p>
                        <div class="absolute bottom-4 right-4 text-red-300 transition-colors group-hover:text-red-500"><i class="fas fa-sync-alt"></i></div>
                    </div>
                    <div class="absolute h-full w-full rounded-2xl border bg-red-50 border-red-200 p-6" style="backface-visibility: hidden; transform: rotateY(180deg);">
                        <div class="flex h-full items-center justify-around text-center"><div class="h-full w-px bg-red-200 absolute left-1/2 top-0 -ml-px"></div><div><p class="text-xs font-medium text-red-600">Bahan Mentah</p><p class="mt-1 text-3xl font-bold text-red-800">{{ $totalDamagedRaw }}</p></div><div><p class="text-xs font-medium text-red-600">Barang Jadi</p><p class="mt-1 text-3xl font-bold text-red-800">{{ $totalDamagedFinished }}</p></div></div>
                        <div class="absolute bottom-4 right-4 text-red-300 transition-colors group-hover:text-red-500"><i class="fas fa-sync-alt"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>