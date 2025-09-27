<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{$data['title']}}</h1>
        <div class="mt-4 mt-lg-0">
            <button type="button" wire:click="$set('isModalOpenData', true)" class="btn btn-success btn-sm"
                title="Change Password">Change
                Data</button>
            <button type="button" wire:click="$set('isModalOpen', true)" class="btn btn-primary btn-sm"
                title="Change Password">Change
                password</button>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Total Items -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Data Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{$totalItems}}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items In-->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Items In</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{$totalIn}}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Out-->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Items Out</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{$totalOut}}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Demaged -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Items Damaged</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{$totalDamaged}}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Stock / Categories</h6>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Trend Stock Items</h6>
                </div>
                <div class="card-body">
                    <canvas id="transactionTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    @if ($isModalOpen)
        {{-- Modal --}}
        <div class="modal fade show d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Password</h5>
                        <button type="button" class="close" wire:click="$set('isModalOpen', false)">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            @if(!$isVerified)
                                <div class="form-group">
                                    <input wire:model='username' type="text" class="form-control form-control-user"
                                        placeholder="Username" required>
                                    @error('username')
                                        <span class="text-danger mt-4">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <input wire:model='password' type="password" class="form-control form-control-user"
                                        placeholder="Password" required>
                                    @error('username')
                                        <span class="text-danger mt-4">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif
                            @if ($isVerified)
                                <div class="form-group">
                                    <input wire:model='newPassword' type="password" class="form-control form-control-user"
                                        placeholder="New Password">
                                    @error('newPassword')
                                        <span class="text-danger mt-4">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <input wire:model='confPass' type="password" class="form-control form-control-user"
                                        placeholder="Confirm Password">
                                    @error('confPass')
                                        <span class="text-danger mt-4">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif

                            @if (session()->has('dataSession'))
                                @if (session('dataSession')->status == 'success')
                                    <div class="alert alert-info alert-dismissible fade show my-3 small" role="alert">
                                        {{session('dataSession')->message}}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                                @if (session('dataSession')->status == 'failed')
                                    <div class="alert alert-danger alert-dismissible fade show my-3 small" role="alert">
                                        {{session('dataSession')->message}}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                            @endif
                        </form>
                    </div>
                    <div class="modal-footer">
                        @if(!$isVerified)
                            <button type="button" class="btn btn-secondary" wire:click='checkUser'>Check</button>
                        @endif
                        @if ($isVerified)
                            <button type="button" class="btn btn-primary" wire:click='changePassword'>Save</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($isModalOpenData)
        {{-- Modal --}}
        <div class="modal fade show d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Data</h5>
                        <button type="button" class="close" wire:click="$set('isModalOpenData', false)">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            @if(!$isVerified)
                                <div class="form-group">
                                
                                    <input wire:model='username' type="text" class="form-control form-control-user"
                                        placeholder="Username" required>
                                    @error('username')
                                        <span class="text-danger mt-4">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <input wire:model='password' type="password" class="form-control form-control-user"
                                        placeholder="Password" required>
                                    @error('username')
                                        <span class="text-danger mt-4">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif
                            @if ($isVerified)
                                <div class="form-group">
                                    <label >Name</label>
                                    <input wire:model='name' type="text" class="form-control form-control-user"
                                        placeholder="Input name">
                                    @error('name')
                                        <span class="text-danger mt-4">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Security Question</label>
                                    <input wire:model='securityQuestion' type="text" class="form-control form-control-user"
                                        placeholder="Input security question">
                                    @error('securityQuestion')
                                        <span class="text-danger mt-4">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Security Answer</label>
                                    <input wire:model='securityAnswer' type="text" class="form-control form-control-user"
                                        placeholder="Input security answer">
                                    @error('securityAnswer')
                                        <span class="text-danger mt-4">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="small" for="customCheck"><span class="text-danger">*Note : </span>The data will change when it is saved. And make sure the data is not empty</label>
                                </div>
                            @endif

                            @if (session()->has('dataSession'))
                                @if (session('dataSession')->status == 'success')
                                    <div class="alert alert-info alert-dismissible fade show my-3 small" role="alert">
                                        {{session('dataSession')->message}}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                                @if (session('dataSession')->status == 'failed')
                                    <div class="alert alert-danger alert-dismissible fade show my-3 small" role="alert">
                                        {{session('dataSession')->message}}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                            @endif
                        </form>
                    </div>
                    <div class="modal-footer">
                        @if(!$isVerified)
                            <button type="button" class="btn btn-secondary" wire:click='checkUser'>Check</button>
                        @endif
                        @if ($isVerified)
                            <button type="button" class="btn btn-primary" wire:click='changeData'>Save</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Page level plugins -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <script>
        const ctx = document.getElementById('categoryChart').getContext('2d');

        const categoryChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: @json($categories), // Megambil Data kategori dari Livewire
                datasets: [{
                    label: 'Jumlah Stok',
                    data: @json($quantities), // Data jumlah stok dari Livewire
                    backgroundColor: ['#4CAF50', '#FF5722', '#FFC107'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Chart.js Pie Chart'
                    }
                }
            },
        });

        const ctx2 = document.getElementById('transactionTrendChart').getContext('2d');

        const transactionTrendChart = new Chart(ctx2, {
            type: 'doughnut', // Pie chart untuk jenis transaksi
            data: {
                labels: ['In', 'Out', 'Damaged'], // Jenis transaksi
                datasets: [{
                    data: @json([$stockTrend->in_quantity, $stockTrend->out_quantity, $stockTrend->damaged_quantity]),
                    backgroundColor: ['#4CAF50', '#FF5722', '#FFC107']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Chart.js Doughnut Chart'
                    }
                }
            },
        });

    </script>

</div>
<!-- /.container-fluid -->