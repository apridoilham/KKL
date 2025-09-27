<!-- Begin Page Content -->
<div class="container-fluid">

  <!-- Page Heading -->
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

  <div class="row">
    <div class="col-sm-12 col-lg-6">
      <!-- Collapsable Card Example -->
      <div class="card shadow mb-4">
        <!-- Card Header - Accordion -->
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">Form Report Data</h6>
        </div>
        <div class="card-body">
          <div class="form-group row align-items-center">
            <label class="col-sm-3 col-form-label ">What data is needed?</label>
            <div class="col-sm-9">
              <select wire:model.live='filter' type="text" class="form-control" {{$filterBy ? 'disabled' : ''}}>
                <option value="">--Select list data--</option>
                <option value="item">Items</option>
                <option value="in">Items In</option>
                <option value="out">Items Out</option>
                <option value="damaged">Items Damaged</option>
              </select>
            </div>
          </div>

          {{-- Jika filter sudah dipilih, ini akan muncul --}}
          @if ($filter)
            <div class="form-group row align-items-center">
              <label class="col-sm-3 col-form-label ">Select the time period</label>
              <div class="col-sm-9">
                <select wire:model.live='filterBy' type="text" class="form-control" {{$filterBy ? 'disabled' : ''}}>
                <option value="">--Select period by--</option>
                <option value="date">Date</option>
                <option value="month">Month</option>
                <option value="year">Year</option>
                </select>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Jika filter dan filterBy sudah dipilih, ini akan muncul sesuai filterBy yang dipilih --}}
    @if ($filterBy && $filter)
    <div class="col-sm-12 col-lg-6">
      <!-- Collapsable Card Example -->
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary"> Filter By</h6>
        </div>
        <div class="card-body">

          {{-- Jika filterBy yang dipilih itu date, maka ini yang akan muncul --}}
          @if ($filterBy == 'date')
            <div class="row">
              <div class="col-sm-12 col-lg-6">
                <div class="form-group">
                  <label>From date</label>
                  <input wire:model='dateFrom' type="date" class="form-control">
                </div>
              </div>
              <div class="col-sm-12 col-lg-6">
                <div class="form-group">
                  <label>Until date</label>
                  <input wire:model='dateUntil' type="date" class="form-control">
                </div>
              </div>
            </div>
          @endif

          {{-- Jika filterBy yang dipilih itu month, maka ini yang akan muncul --}}
          @if ($filterBy == 'month')
            <div class="row">
              <div class="col-sm-12 col-lg-6">
                <div class="form-group">
                  <label>From the month</label>
                  <select wire:model='monthFrom' type="text" class="form-control">
                    <option value="">--Select Month--</option>
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">Sepetember</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-lg-6">
                <div class="form-group">
                  <label>Until the month</label>
                  <select wire:model='monthUntil' type="text" class="form-control">
                    <option value="">--Select Month--</option>
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">Sepetember</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                  </select>
                </div>
              </div>
            </div>
          @endif

          {{-- Jika filterBy yang dipilih itu month atau year, maka ini yang akan muncul --}}
          @if ($filterBy == 'month' || $filterBy == 'year')
            <div class="row">
              <div class="col-sm-12">
              <div class="form-group row align-items-center">
                <label class="col-sm-12 col-lg-3">Input year</label>
                <div class="col-sm-12 col-lg-9">
                  <input wire:model='selectYear' type="number" class="form-control" placeholder="input year">
                </div>
              </div>
              </div>
            </div>
          @endif

        </div>
        <div class="card-footer text-center text-lg-right">
        {{--
        - wire:click='handleReset' : jika diklik maka akan mengeksekusi method handleReset di komponen
        ReportComponent.
        - Begitu juga wire:click='handleCheck' dan wire:click='handlePrint'
        --}}
        <button wire:click='handleReset' type="button" class="btn btn-outline-danger"><i
          class="fas fa-window-restore"></i> Reset</button>
        <button wire:click='handleCheck' type="button" class="btn btn-outline-primary"><i class="fas fa-check"></i>
          Check</button>
        <button wire:click='handlePrint' type="button" class="btn btn-primary btn-success"><i
          class="fa fa-print"></i> Print</button>
        </div>
      </div>
    </div>
  @endif

    {{-- Table akan muncul jika data ada dan filternya item --}}
    @if ($reportData && $filter == 'item')
    <div class="col-12">
      <div class="card-body bg-white">
        <div class="table-responsive">
          <table class="table table-striped">
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
    </div>
  @endif

    {{-- Table akan muncul jika data ada dan filternya selain item yaitu in, out atau damaged --}}
    @if ($reportData && $filter != 'item')
    <div class="col-12">
      <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped">
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
    </div>
  @endif
  </div>
</div>