<div class="container-fluid">
    <div class="mb-4 row">
        <div class="col-sm-12 col-lg-6">
            <h1 class="h3 mb-0 text-gray-800">{{$data['title']}}</h1>
        </div>
        <div class="col-sm-12 col-lg-6 mt-4 mt-lg-0">
            <div class="text-lg-right">
                <div class="d-flex align-items-center">
                    <div class="d-block w-75 mr-2">Filter Type By </div>
                    <select type="text" class="form-control" wire:model.live="filterType">
                        <option value="all">All data</option>
                        <option value="in">In</option>
                        <option value="out">Out</option>
                        <option value="damaged">Damaged</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    @if (session()->has('dataSession'))
        @if (session('dataSession')->status == 'success')
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{session('dataSession')->message}}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (session('dataSession')->status == 'failed')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{session('dataSession')->message}}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-sm-flex align-items-center justify-content-between">
            <button wire:click='create' type="button" class="btn btn-success btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-plus"></i>
                </span>
                <span class="text">Add Item</span>
            </button>
            <div>
                <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                            aria-label="Search" aria-describedby="basic-addon2" wire:model.live="search">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Input At</th>
                            <th scope="col">Category</th>
                            <th scope="col">Name</th>
                            <th scope="col">Type</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Description</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions->items() as $item)
                            <tr>
                                <th scope="row">{{$no++}}</th>
                                <td>{{ $item->created_at }}</td>
                                <td>{{ $item->item->category }}</td>
                                <td>{{ $item->item->name }}</td>
                                <td><span
                                        class="badge {{ ($item->type == 'in') ? 'badge-primary' : (($item->type == 'out') ? 'badge-warning' : 'badge-danger')}}">{{ $item->type }}</span>
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->description }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" title="Edit Data"
                                        wire:click='edit({{$item->id}})'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" title="Delete Data"
                                        wire:click='delete({{$item->id}})'
                                        wire:confirm="Are you sure you want to delete this post?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        @if ($transactions->count() == 0)
                            <tr>
                                <td colspan="8" class="text-center"> Tidak ada data</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Input At</th>
                            <th scope="col">Category</th>
                            <th scope="col">Name</th>
                            <th scope="col">Type</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Description</th>
                            <th scope="col">Action</th>
                        </tr>
                    </tfoot>
                </table>
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

    @if ($isModalOpen)
        {{-- Modal --}}
        <div class="modal fade show d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $id != '' ? 'Edit Data' : 'Add Data'}}</h5>
                        <button type="button" class="close" wire:click="$set('isModalOpen', false)">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label><span class="text-danger">*</span> Select Item</label>
                                <select type="text" class="form-control" wire:model.live="itemId" required
                                    {{$this->id != '' ? 'disabled' : ''}}>
                                    <option value="">--Select Status--</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->category }} - {{ $item->name }} ({{ $item->quantity }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('itemId') <span class="text-danger mt-4">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label><span class="text-danger">*</span> Type</label>
                                <select type="text" class="form-control" wire:model="type" required
                                    {{$this->id != '' ? 'disabled' : ''}}>
                                    <option value="">--Select Type--</option>
                                    <option value="in">In</option>
                                    <option value="out">Out</option>
                                    <option value="damaged">Damaged</option>
                                </select>
                                @error('type') <span class="text-danger mt-4">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label><span class="text-danger">*</span> Quantity</label>
                                <input type="number" class="form-control" wire:model="quantity" placeholder="0"
                                    title="Input Quantity" required>
                                @error('quantity') <span class="text-danger mt-4">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" rows="3" wire:model="description"
                                    placeholder="Input description (optional)" title="Input Description"></textarea>
                            </div>
                            <div class="small text-right text-danger">Make sure to input the data correctly. After 10
                                minutes, data cannot be edited and deleted</div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="$set('isModalOpen', false)">Close</button>
                        <button type="button" class="btn btn-primary" wire:click='store'>Save</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>