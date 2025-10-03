<div class="container-fluid px-4 md:px-6 py-6">

    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800">{{ $data['title'] }}</h1>
            <p class="mt-1 text-slate-600">Filter dan buat laporan berdasarkan data inventaris Anda.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg">
        <div class="p-8">
            <h3 class="text-xl font-bold text-slate-800 mb-6 border-b border-slate-200 pb-4">Opsi Laporan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700">1. Pilih Tipe Data</label>
                    <select wire:model.live="filter" class="mt-1 block w-full pl-3 pr-8 py-2.5 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Pilih Data --</option>
                        <option value="item">Semua Barang</option>
                        <option value="in">Barang Masuk</option>
                        <option value="out">Barang Keluar</option>
                        <option value="damaged">Barang Rusak</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">2. Pilih Periode</label>
                    <select wire:model.live="filterBy" class="mt-1 block w-full pl-3 pr-8 py-2.5 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500" @if(!$filter) disabled @endif>
                        <option value="">-- Pilih Periode --</option>
                        <option value="date">Per Tanggal</option>
                        <option value="month">Per Bulan</option>
                        <option value="year">Per Tahun</option>
                    </select>
                </div>
                <div class="space-y-4">
                    @if ($filterBy == 'date')
                        <div>
                            <label class="block text-sm font-medium text-slate-700">3. Rentang Tanggal</label>
                            <div class="flex items-center space-x-2 mt-1">
                                <input wire:model="dateFrom" type="date" class="block w-full border-slate-300 rounded-lg">
                                <span>s/d</span>
                                <input wire:model="dateUntil" type="date" class="block w-full border-slate-300 rounded-lg">
                            </div>
                            @error('dateFrom') <span class="text-red-500 text-xs">{{$message}}</span> @enderror
                            @error('dateUntil') <span class="text-red-500 text-xs">{{$message}}</span> @enderror
                        </div>
                    @elseif ($filterBy == 'month')
                        <div>
                            <label class="block text-sm font-medium text-slate-700">3. Rentang Bulan & Tahun</label>
                            <div class="flex items-center space-x-2 mt-1">
                                <select wire:model="monthFrom" class="block w-full border-slate-300 rounded-lg">
                                    @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option> @endfor
                                </select>
                                <span>s/d</span>
                                <select wire:model="monthUntil" class="block w-full border-slate-300 rounded-lg">
                                    @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option> @endfor
                                </select>
                                <input wire:model="selectYear" type="number" class="block w-full border-slate-300 rounded-lg" placeholder="Tahun">
                            </div>
                            @error('monthFrom') <span class="text-red-500 text-xs">{{$message}}</span> @enderror
                        </div>
                    @elseif ($filterBy == 'year')
                         <div>
                            <label class="block text-sm font-medium text-slate-700">3. Pilih Tahun</label>
                            <input wire:model="selectYear" type="number" class="mt-1 block w-full border-slate-300 rounded-lg" placeholder="cth: {{ date('Y') }}">
                            @error('selectYear') <span class="text-red-500 text-xs">{{$message}}</span> @enderror
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-slate-400">3. Detail</label>
                             <div class="mt-1 p-4 border-2 border-dashed border-slate-200 rounded-lg text-center text-slate-400">
                                <p>Pilih periode untuk melanjutkan</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="p-6 bg-slate-50 flex justify-end items-center space-x-3 border-t border-slate-200">
            <button wire:click="handleReset" class="px-4 py-2.5 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">Reset</button>
            <button wire:click="generatePreview" class="px-4 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">Buat Pratinjau</button>
            
            <button wire:click="handlePrint" @if(!$reportData) disabled @endif class="inline-flex items-center px-4 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 disabled:bg-teal-300 disabled:cursor-not-allowed">
                <i class="fas fa-print mr-2"></i> Cetak
            </button>
            
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @if(!$reportData) disabled @endif class="inline-flex items-center px-4 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-300 disabled:cursor-not-allowed">
                    <i class="fas fa-download mr-2"></i> Download <i class="fas fa-chevron-down ml-2 text-xs"></i>
                </button>
                
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-xl py-2 z-10" x-cloak>
                    @php
                        // Menyiapkan query parameters untuk link download agar filter tetap terbawa
                        $queryParams = array_filter([
                            'filter' => $filter, 'filterBy' => $filterBy, 'dateFrom' => $dateFrom, 'dateUntil' => $dateUntil,
                            'monthFrom' => $monthFrom, 'monthUntil' => $monthUntil, 'selectYear' => $selectYear,
                        ]);
                    @endphp
                    <a href="{{ route('report.download', array_merge(['type' => 'pdf'], $queryParams)) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-indigo-500 hover:text-white">
                        <i class="fas fa-file-pdf fa-sm fa-fw mr-2"></i> PDF
                    </a>
                    <a href="{{ route('report.download', array_merge(['type' => 'xlsx'], $queryParams)) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-indigo-500 hover:text-white">
                        <i class="fas fa-file-excel fa-sm fa-fw mr-2"></i> Excel (XLSX)
                    </a>
                    <a href="{{ route('report.download', array_merge(['type' => 'csv'], $queryParams)) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-indigo-500 hover:text-white">
                        <i class="fas fa-file-csv fa-sm fa-fw mr-2"></i> CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    @if ($reportData)
    <div class="mt-8 bg-white rounded-xl shadow-lg">
        <div class="p-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-800">Pratinjau Laporan</h3>
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
    <div class="mt-8 text-center py-16 px-4 bg-white rounded-xl shadow-lg">
        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
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