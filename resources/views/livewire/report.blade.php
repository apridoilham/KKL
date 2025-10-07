<div class="container mx-auto px-4 py-6 md:px-6">

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900">Buat Laporan</h1>
        <p class="mt-1 text-slate-500">Filter dan buat laporan berdasarkan data inventaris Anda.</p>
    </div>

    <div x-data="{ filterType: @entangle('filter'), reportIsAvailable: @entangle('hasReportData') }" class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800">Opsi Laporan</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="filter" class="text-sm font-medium text-slate-700">1. Tipe Data</label>
                    <select 
                        wire:model.live="filter" 
                        id="filter" 
                        class="mt-1 block w-full appearance-none rounded-lg border bg-white py-2.5 px-4 text-slate-800 outline-none focus:ring-1 transition-colors"
                        :class="{
                            'border-green-300 text-green-900 focus:border-green-500 focus:ring-green-500': filterType === 'in',
                            'border-yellow-300 text-yellow-900 focus:border-yellow-500 focus:ring-yellow-500': filterType === 'out',
                            'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500': filterType === 'damaged',
                            'border-slate-300 text-slate-800 focus:border-amber-500 focus:ring-amber-500': !['in', 'out', 'damaged'].includes(filterType)
                        }"
                        style="background-image: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 20 20%27%3e%3cpath stroke=%27%236b7280%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%271.5%27 d=%27m6 8 4 4 4-4%27/%3e%3c/svg%3e'); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em;"
                    >
                        <option value="">-- Pilih Data --</option>
                        <option value="item">Semua Barang</option>
                        <option value="in">Barang Masuk (Semua)</option>
                        <option value="out">Barang Keluar (Semua)</option>
                        <option value="damaged">Barang Rusak</option>
                    </select>
                </div>
                <div>
                    <label for="itemType" class="text-sm font-medium text-slate-700">2. Tipe Barang</label>
                    <select wire:model.live="itemType" id="itemType" class="mt-1 block w-full appearance-none rounded-lg border border-slate-300 bg-white py-2.5 px-4 text-slate-800 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 disabled:bg-slate-50" @if(!$filter) disabled @endif style="background-image: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 20 20%27%3e%3cpath stroke=%27%236b7280%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%271.5%27 d=%27m6 8 4 4 4-4%27/%3e%3c/svg%3e'); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em;">
                        <option value="all">Semua Barang</option>
                        <option value="barang_mentah">Barang Mentah</option>
                        <option value="barang_jadi">Barang Jadi</option>
                    </select>
                </div>
                <div>
                    <label for="filterBy" class="text-sm font-medium text-slate-700">3. Periode</label>
                    <select wire:model.live="filterBy" id="filterBy" class="mt-1 block w-full appearance-none rounded-lg border border-slate-300 bg-white py-2.5 px-4 text-slate-800 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 disabled:bg-slate-50" @if(!$filter) disabled @endif style="background-image: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 20 20%27%3e%3cpath stroke=%27%236b7280%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%271.5%27 d=%27m6 8 4 4 4-4%27/%3e%3c/svg%3e'); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em;">
                        <option value="">-- Pilih Periode --</option>
                        <option value="date">Per Tanggal</option>
                        <option value="month">Per Bulan</option>
                        <option value="year">Per Tahun</option>
                    </select>
                </div>
                <div class="space-y-4">
                    @if ($filterBy == 'date')
                        <div>
                            <label class="block text-sm font-medium text-slate-700">4. Rentang Tanggal</label>
                            <div class="mt-1 flex items-center space-x-2">
                                <input wire:model="dateFrom" type="date" class="block w-full rounded-lg border border-slate-300 py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                <span class="text-slate-500">s/d</span>
                                <input wire:model="dateUntil" type="date" class="block w-full rounded-lg border border-slate-300 py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            </div>
                            @error('dateFrom') <span class="text-red-500 text-xs">{{$message}}</span> @enderror
                            @error('dateUntil') <span class="text-red-500 text-xs">{{$message}}</span> @enderror
                        </div>
                    @elseif ($filterBy == 'month')
                        <div>
                            <label class="block text-sm font-medium text-slate-700">4. Rentang Bulan & Tahun</label>
                            <div class="mt-1 flex items-center space-x-2">
                                <select wire:model="monthFrom" class="block w-full rounded-lg border border-slate-300 py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                    @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option> @endfor
                                </select>
                                <select wire:model="monthUntil" class="block w-full rounded-lg border border-slate-300 py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                    @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option> @endfor
                                </select>
                                <input wire:model="selectYear" type="number" class="block w-full rounded-lg border border-slate-300 py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" placeholder="Tahun">
                            </div>
                            @error('monthFrom') <span class="text-red-500 text-xs">{{$message}}</span> @enderror
                        </div>
                    @elseif ($filterBy == 'year')
                         <div>
                            <label class="block text-sm font-medium text-slate-700">4. Pilih Tahun</label>
                            <input wire:model="selectYear" type="number" class="mt-1 block w-full rounded-lg border border-slate-300 py-2 px-3 text-slate-700 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" placeholder="cth: {{ date('Y') }}">
                            @error('selectYear') <span class="text-red-500 text-xs">{{$message}}</span> @enderror
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-slate-400">4. Detail</label>
                             <div class="mt-1 flex h-[42px] items-center justify-center rounded-lg border-2 border-dashed border-slate-200 text-center text-sm text-slate-400">
                                <p>Pilih periode untuk melanjutkan</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div 
            x-data="{
                checkAndFire(action, checkReportData = false) {
                    if (@this.get('filter') === '' || @this.get('filterBy') === '') {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { status: 'failed', message: 'Harap pilih Tipe Data dan Periode Laporan terlebih dahulu.' } }));
                        return;
                    }
                    if (checkReportData && !@this.get('hasReportData')) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { status: 'failed', message: 'Buat Pratinjau terlebih dahulu sebelum mencetak atau download.' } }));
                        return;
                    }
                    @this.call(action);
                }
            }"
            class="flex items-center justify-end space-x-3 rounded-b-xl border-t border-slate-200 bg-slate-50 p-6"
        >
            <button @click="checkAndFire('handleReset')" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100">Reset</button>
            <button @click="checkAndFire('generatePreview')" wire:loading.attr="disabled" wire:target="generatePreview" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Buat Pratinjau</button>
            <button @click="checkAndFire('handlePrint', true)" wire:loading.attr="disabled" :disabled="!reportIsAvailable" class="inline-flex items-center rounded-lg border border-transparent bg-cyan-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-cyan-700 disabled:bg-cyan-300 disabled:cursor-not-allowed">
                <i class="fas fa-print mr-2"></i> Cetak
            </button>
            <div x-data="{ open: false }" class="relative">
                <button @click="if (reportIsAvailable) { open = !open }" :disabled="!reportIsAvailable" class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-400 disabled:bg-amber-300 disabled:cursor-not-allowed">
                    <i class="fas fa-download mr-2"></i> Download <i class="fas fa-chevron-down ml-2 text-xs"></i>
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 z-10 mt-2 w-40 rounded-lg bg-white py-2 shadow-xl ring-1 ring-slate-200" x-cloak>
                    @php
                        $queryParams = array_filter([
                            'filter' => $filter, 'filterBy' => $filterBy, 'itemType' => $itemType,
                            'dateFrom' => $dateFrom, 'dateUntil' => $dateUntil,
                            'monthFrom' => $monthFrom, 'monthUntil' => $monthUntil, 'selectYear' => $selectYear,
                        ]);
                    @endphp
                    <a href="{{ route('report.download', array_merge(['type' => 'pdf'], $queryParams)) }}" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100">
                        <i class="fas fa-file-pdf fa-sm fa-fw mr-2 text-red-500"></i> PDF
                    </a>
                    <a href="{{ route('report.download', array_merge(['type' => 'xlsx'], $queryParams)) }}" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100">
                        <i class="fas fa-file-excel fa-sm fa-fw mr-2 text-green-500"></i> Excel (XLSX)
                    </a>
                    <a href="{{ route('report.download', array_merge(['type' => 'csv'], $queryParams)) }}" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100">
                        <i class="fas fa-file-csv fa-sm fa-fw mr-2 text-sky-500"></i> CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    @if ($reportData)
    <div class="mt-8 rounded-xl border border-slate-200 bg-white">
        <div class="p-6 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-800">Pratinjau Laporan</h3>
        </div>
        <div class="p-6">
            @if($filter == 'item')
                @include('livewire.reports.item-table', ['data' => $reportData])
            @else
                @include('livewire.reports.transaction-table', ['data' => $reportData])
            @endif
        </div>
    </div>
    @endif

    @if ($noDataFound)
    <div class="mt-8 rounded-xl border-2 border-dashed border-slate-200 bg-white py-16 px-4 text-center">
        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
        <h3 class="mt-2 text-lg font-semibold text-slate-800">Data Tidak Ditemukan</h3>
        <p class="mt-1 text-sm text-slate-500">Tidak ada catatan yang cocok dengan kriteria filter Anda.</p>
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        @this.on('open-new-tab', ({ url }) => { window.open(url, '_blank'); });
    });
</script>
@endpush