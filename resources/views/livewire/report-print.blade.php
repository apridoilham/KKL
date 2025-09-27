@section('title', $data['title'] ?? 'Cetak Laporan')
<div class="row">
	@if (session()->has('dataSession'))
		@if (session('dataSession')->status == 'failed')
			<div class="row d-flex justify-content-center w-100 mt-4">
				<div class="col-4 text-center">
					<div class="alert alert-danger alert-dismissible fade show" role="alert">
						{{session('dataSession')->message}}
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<a class="btn btn-success" href='/report'> Back to Report Page</a>
				</div>
			</div>
		@endif
	@endif
	@if ($reportData && session('filter') == 'item')
		<div class="col-12 card p-4">
			<div class="card-header">
				<h4 class="text-center">{{$titleData}}</h4>
			</div>
			<div class="card-body bg-white">
				<div class="table-responsive">
					<table class="table table-bordered">
						<thead class="table-dark">
							<tr>
								<th scope="col">#</th>
								<th scope="col">Input At</th>
								<th scope="col">Code</th>
								<th scope="col">Category</th>
								<th scope="col">Name</th>
								<th scope="col">Quantity</th>
								<th scope="col">Status</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($reportData as $item)
								<tr>
									<th scope="row">{{$no++}}</th>
									<td>{{ $item->created_at }}</td>
									<td>{{ $item->code }}</td>
									<td>{{ $item->category }}</td>
									<td>{{ $item->name }}</td>
									<td>{{ $item->quantity }}</td>
									<td><span
											class="badge {{$item->status == 'available' ? 'badge-primary' : 'badge-danger'}}">{{ $item->status }}</span>
									</td>
								</tr>
							@endforeach
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
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
			<div class="card-footer text-right">
				<p>Date : <?= date("d-m-Y"); ?></p>
			</div>
		</div>
	@endif

	@if ($reportData && session('filter') != 'item')
		<div class="col-12 card p-4">
			<div class="card-header">
				<h4 class="text-center">{{$titleData }}</h4>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered">
						<thead class="table-dark">
							<tr>
								<th scope="col">#</th>
								<th scope="col">Input At</th>
								<th scope="col">Category</th>
								<th scope="col">Name</th>
								<th scope="col">Type</th>
								<th scope="col">Quantity</th>
								<th scope="col">Description</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($reportData as $item)
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
								</tr>
							@endforeach
						</tbody>
						<tfoot>
							<tr>
								<th scope="col">#</th>
								<th scope="col">Category</th>
								<th scope="col">Name</th>
								<th scope="col">Type</th>
								<th scope="col">Quantity</th>
								<th scope="col">Description</th>
								<th scope="col">Input At</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
			<div class="card-footer text-right">
				<p>Date : <?= date("d-m-Y"); ?></p>
			</div>
		</div>
	@endif

	@if (!session()->has('dataSession'))
		<script>
			window.onload = function () {
				setTimeout(() => {
					window.print();
				}, 1500);
			}
		</script>
	@endif

</div>