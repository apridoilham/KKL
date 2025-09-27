<div class="container-fluid px-4 md:px-6 py-6">

    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Hello, {{ auth()->user()->name }}! 👋</h1>
            <p class="mt-1 text-slate-500">Welcome back, here is your inventory summary.</p>
        </div>
        <div class="flex space-x-2 mt-4 md:mt-0">
            <button wire:click="$set('isModalOpenData', true)" class="btn btn-outline-primary btn-sm shadow-sm flex items-center">
                <i class="fas fa-edit fa-sm mr-2"></i> Change Data
            </button>
            <button wire:click="$set('isModalOpen', true)" class="btn btn-outline-warning btn-sm shadow-sm flex items-center">
                <i class="fas fa-key fa-sm mr-2"></i> Change Password
            </button>
        </div>
    </div>

    @if (session()->has('dataSession'))
        <div class="alert alert-{{ session('dataSession')->status == 'success' ? 'success' : 'danger' }} alert-dismissible fade show mb-6" role="alert">
            {{ session('dataSession')->message }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-box fa-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500">Total Data Items</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $totalItems }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-arrow-down fa-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500">Total Items In</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $totalIn }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-arrow-up fa-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500">Total Items Out</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $totalOut }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500">Total Items Damaged</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $totalDamaged }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-users fa-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500">Total Users</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $totalUsers }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold text-slate-800 mb-4">Stock by Category</h3>
            <div class="h-64">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
        <div class="lg:col-span-3 bg-white p-6 rounded-xl shadow-lg">
            <h3 class="font-bold text-slate-800 mb-4">Transaction Trends</h3>
            <div class="h-64">
                 <canvas id="transactionTrendChart"></canvas>
            </div>
        </div>
    </div>
    
    @if($isModalOpenData)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="changeData">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Your Data</h5>
                        <button type="button" class="close" wire:click="$set('isModalOpenData', false)">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input wire:model="name" type="text" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Security Question</label>
                            <input wire:model="securityQuestion" type="text" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Security Answer (Required to save)</label>
                            <input wire:model="securityAnswer" type="text" class="form-control" placeholder="Enter your security answer to confirm" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isModalOpenData', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($isModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="changePassword">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Password</h5>
                        <button type="button" class="close" wire:click="$set('isModalOpen', false)">&times;</button>
                    </div>
                    <div class="modal-body">
                         <div class="form-group">
                            <label>Current Password</label>
                            <input wire:model="password" type="password" class="form-control" required>
                            @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input wire:model="newPassword" type="password" class="form-control" required>
                            @error('newPassword') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input wire:model="confPass" type="password" class="form-control" required>
                            @error('confPass') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save New Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('livewire:navigated', () => {
        let categoryChartInstance, transactionChartInstance;

        const renderCharts = () => {
            // Hancurkan instance chart yang ada jika sudah dibuat
            if (categoryChartInstance) categoryChartInstance.destroy();
            if (transactionChartInstance) transactionChartInstance.destroy();

            // Pie Chart for Categories
            const ctxPie = document.getElementById('categoryChart');
            if (ctxPie) {
                categoryChartInstance = new Chart(ctxPie, {
                    type: 'doughnut',
                    data: {
                        labels: @json($categories),
                        datasets: [{
                            data: @json($quantities),
                            backgroundColor: @json($pieChartColors),
                            borderWidth: 2,
                            borderColor: '#fff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 20,
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });
            }

            // Bar Chart for Transactions
            const ctxBar = document.getElementById('transactionTrendChart');
            if (ctxBar) {
                transactionChartInstance = new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: ['In', 'Out', 'Damaged'],
                        datasets: [{
                            label: 'Total Transactions',
                            data: @json([$stockTrend['in_quantity'], $stockTrend['out_quantity'], $stockTrend['damaged_quantity']]),
                            backgroundColor: @json($doughnutChartColors),
                            borderColor: @json($doughnutChartColors),
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0 // Tampilkan angka bulat di sumbu Y
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false // Sembunyikan legenda karena sudah jelas dari label
                            }
                        }
                    }
                });
            }
        };

        renderCharts(); // Panggil fungsi saat halaman pertama kali dimuat

        // Dengarkan event dari Livewire jika data chart diperbarui
        Livewire.on('chartDataUpdated', () => {
            renderCharts();
        });
    });
</script>
@endpush