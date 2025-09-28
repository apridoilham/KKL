<div class="container-fluid px-4 md:px-6 py-6">

    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800">Halo, {{ auth()->user()->name }}! 👋</h1>
            <p class="mt-1 text-slate-600">Selamat datang kembali, berikut ringkasan inventaris Anda.</p>
        </div>
        <div class="flex items-center space-x-3">
            {{-- Tombol Ubah Data --}}
            <button wire:click="$set('isModalOpenData', true)" 
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                <i class="fas fa-edit fa-fw mr-2 text-slate-500"></i>
                Ubah Data
            </button>

            {{-- Tombol Ubah Password --}}
            <button wire:click="$set('isModalOpen', true)"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                <i class="fas fa-key fa-fw mr-2 text-slate-500"></i>
                Ubah Password
            </button>
        </div>
    </div>
    
    @if (session()->has('dataSession'))
        <div class="alert alert-{{ session('dataSession')['status'] == 'success' ? 'success' : 'danger' }} alert-dismissible fade show mb-6" role="alert">
            {{ session('dataSession')['message'] }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Jenis Barang</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalItems }}</p>
                </div>
                <div class="text-2xl text-teal-500"><i class="fas fa-boxes"></i></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Stok Tersedia</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalStock }}</p>
                </div>
                <div class="text-2xl text-indigo-500"><i class="fas fa-warehouse"></i></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Pengguna</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalUsers }}</p>
                </div>
                <div class="text-2xl text-sky-500"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Barang Masuk</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalIn }}</p>
                </div>
                <div class="text-2xl text-green-500"><i class="fas fa-arrow-down"></i></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Barang Keluar</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalOut }}</p>
                </div>
                <div class="text-2xl text-yellow-500"><i class="fas fa-arrow-up"></i></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Barang Rusak</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalDamaged }}</p>
                </div>
                <div class="text-2xl text-red-500"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-lg">
            <h3 class="font-bold text-slate-800 mb-4">5 Barang Stok Tertinggi</h3>
            <div class="h-80"><canvas id="topStockChart"></canvas></div>
        </div>
        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-lg">
                <h3 class="font-bold text-slate-800 mb-4">Stok per Kategori</h3>
                <div class="h-64"><canvas id="categoryChart"></canvas></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg">
                <h3 class="font-bold text-slate-800 mb-4">Tren Transaksi</h3>
                <div class="h-64"><canvas id="transactionTrendChart"></canvas></div>
            </div>
        </div>
    </div>

    @if($isModalOpenData)
    <div x-data="{ show: @entangle('isModalOpenData') }" x-show="show" x-transition.opacity.duration.300ms class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
        <div x-show="show" x-transition.scale.duration.300ms @click.away="show = false" class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
            <form wire:submit.prevent="changeData">
                <div class="p-6 bg-blue-600 text-white flex items-center justify-between">
                    <h3 class="text-xl font-bold flex items-center"><i class="fas fa-user-edit mr-3"></i><span>Ubah Data Diri</span></h3>
                    <button type="button" @click="show = false" class="text-blue-200 hover:text-white text-3xl">&times;</button>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 uppercase">Nama Lengkap</label>
                        <input wire:model="name" type="text" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-blue-500" required>
                        @error('name')<span class="text-red-500 text-xs">{{$message}}</span>@enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 uppercase">Username</label>
                        <input wire:model="username" type="text" class="mt-1 block w-full bg-slate-100 border-0 border-b-2 border-slate-200 p-2 focus:ring-0" required readonly>
                    </div>
                    <hr>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 uppercase">Password Anda <span class="text-red-500 normal-case">(Wajib untuk konfirmasi)</span></label>
                        <input wire:model="confirmationPassword" type="password" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-blue-500" required>
                        @error('confirmationPassword') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="p-6 bg-slate-50 rounded-b-xl flex justify-end space-x-3 border-t">
                    <button type="button" @click="show = false" class="px-4 py-2.5 border rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">Batal</button>
                    <button type="submit" class="inline-flex items-center px-4 py-2.5 border text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($isModalOpen)
    <div x-data="{ show: @entangle('isModalOpen') }" x-show="show" x-transition.opacity.duration.300ms class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
        <div x-show="show" x-transition.scale.duration.300ms @click.away="show = false" class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
            <form wire:submit.prevent="changePassword">
                <div class="p-6 bg-yellow-500 text-white flex items-center justify-between"><h3 class="text-xl font-bold flex items-center"><i class="fas fa-key mr-3"></i><span>Ubah Password</span></h3><button type="button" @click="show = false" class="text-yellow-100 hover:text-white text-3xl">&times;</button></div>
                <div class="p-8 space-y-6">
                    <div><label class="text-xs font-semibold text-slate-500 uppercase">Password Saat Ini</label><input wire:model="password" type="password" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-yellow-500" required>@error('password') <span class="text-red-500 text-xs">{{$message}}</span> @enderror</div>
                    <div><label class="text-xs font-semibold text-slate-500 uppercase">Password Baru</label><input wire:model="newPassword" type="password" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-yellow-500" required>@error('newPassword') <span class="text-red-500 text-xs">{{$message}}</span> @enderror</div>
                    <div><label class="text-xs font-semibold text-slate-500 uppercase">Konfirmasi Password Baru</label><input wire:model="confPass" type="password" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-yellow-500" required>@error('confPass') <span class="text-red-500 text-xs">{{$message}}</span> @enderror</div>
                </div>
                <div class="p-6 bg-slate-50 rounded-b-xl flex justify-end space-x-3 border-t"><button type="button" @click="show = false" class="px-4 py-2.5 border rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">Batal</button><button type="submit" class="inline-flex items-center px-4 py-2.5 border text-sm font-medium rounded-lg text-white bg-yellow-500 hover:bg-yellow-600">Simpan Password Baru</button></div>
            </form>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('livewire:navigated', () => {
    Chart.defaults.color = '#64748b';
    Chart.defaults.borderColor = 'rgba(0, 0, 0, 0.05)';
    let charts = {};
    const destroyChart = (chartId) => { if (charts[chartId]) charts[chartId].destroy(); };
    const renderCharts = () => {
        destroyChart('categoryChart');
        destroyChart('transactionTrendChart');
        destroyChart('topStockChart');
        const ctxCategory = document.getElementById('categoryChart');
        if (ctxCategory) {
            charts['categoryChart'] = new Chart(ctxCategory, {
                type: 'polarArea',
                data: { labels: @json($categoryLabels), datasets: [{ data: @json($categoryData), backgroundColor: @json($chartPalette1) }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }
        const ctxTransaction = document.getElementById('transactionTrendChart');
        if (ctxTransaction) {
            charts['transactionTrendChart'] = new Chart(ctxTransaction, {
                type: 'doughnut',
                data: { labels: @json($trendLabels), datasets: [{ data: @json($trendData), backgroundColor: ['#28a745', '#ffc107', '#dc3545'], borderWidth: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
            });
        }
        const ctxTopStock = document.getElementById('topStockChart');
        if (ctxTopStock) {
            charts['topStockChart'] = new Chart(ctxTopStock, {
                type: 'bar',
                data: { labels: @json($topStockLabels), datasets: [{ label: 'Stok', data: @json($topStockData), backgroundColor: @json($chartPalette1), borderRadius: 4 }] },
                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }, plugins: { legend: { display: false } } }
            });
        }
    };
    renderCharts();
});
</script>
@endpush