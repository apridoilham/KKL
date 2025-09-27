<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{$data['title']}}</h1>
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
                            <th scope="col">Code</th>
                            <th scope="col">Category</th>
                            <th scope="col">Name</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Status</th>
                            <th scope="col">Input At</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items->items() as $item)
                            <tr>
                                <th scope="row">{{$no++}}</th>
                                <td>{{ $item->code }}</td>
                                <td>{{ $item->category }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td><span
                                        class="badge {{$item->status == 'available' ? 'badge-primary' : 'badge-danger'}}">{{ $item->status }}</span>
                                </td>
                                <td>{{ $item->created_at }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" title="Edit Data"
                                        wire:click='edit({{$item->id}})'>
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    {{-- Tombol Hapus hanya tampil untuk Admin --}}
                                    @if(auth()->user()->role == 'admin')
                                        <button type="button" class="btn btn-sm btn-danger" title="Delete Data"
                                            wire:click='delete({{$item->id}})'
                                            wire:confirm="Are you sure you want to delete this post?">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        @if ($items->count() == 0)
                            <tr>
                                <td colspan="8" class="text-center"> Tidak ada data</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Code</th>
                            <th scope="col">Category</th>
                            <th scope="col">Name</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Status</th>
                            <th scope="col">Input At</th>
                            <th scope="col">Action</th>
                        </tr>
                    </tfoot>
                </table>

                {{ $items->links() }}
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
                                <label>Code Item</label>
                                <input type="text" class="form-control" wire:model="code"
                                    placeholder="Exp : BRG0001" title="Input Code Item">
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" class="form-control" wire:model="category"
                                    placeholder="Exp : Elektornik" title="Input Category">
                            </div>
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" class="form-control" wire:model="name"
                                    placeholder="Exp : Laptop etc" title="Input Name" required>
                                @error('name')
                                    <span class="text-danger mt-4">{{ $message }}</span>
                                @enderror
                            </div>
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