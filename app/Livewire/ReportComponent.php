<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;

class ReportComponent extends Component
{
    public $data;
    public $filter, $filterBy;
    public $dateFrom, $dateUntil, $monthFrom, $monthUntil;
    public $selectYear;
    public $no = 1;
    public $reportData;

    public function mount()
    {
        $this->data = [
            'title' => 'Report Page',
            'urlPath' => 'report'
        ];

        $this->selectYear = Carbon::now()->year;
    }

    /* 
        Ini akan mereset semua data dan tampilan ke semula. Karena semua tampilan report di handle di property $filter, $filterBy dan $reportData
    */

    public function handleReset(){
        $this->filter = '';
        $this->filterBy = '';
        $this->reportData= '';
    }

    /*
        - handleCheck() digunakan untuk menampilkan data terlebih dahulu sebelum di cetak
        - data yang bisa dicetak ada 4 yaitu data items, items in, items out dan items damaged
        - filterBynya ada 3 yaitu by date, mount, dan year
    */

    public function handleCheck(){

        if($this->filterBy == 'date'){
            /* 
                Jika by date, maka akan menggunakan parameter tanggal dari (dateFrom) dan sampai tanggal (dateUntil)
            */
            if($this->dateFrom == '' || $this->dateUntil == ''){
                session()->flash('dataSession', (object) [
                    'status' => 'failed',
                    'message' => 'Make sure to fill in all date inputs'
                ]);
            }else{
                if($this->filter == 'item'){

                    $data = Item::whereBetween('created_at',[$this->dateFrom, $this->dateUntil])
                    ->orderBy('created_at', 'desc')
                    ->get();

                    !$data->isEmpty() ? $this->reportData = $data : session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'No data found in that date range'
                    ]);
          

                }else{

                    $transactions = Transaction::query()
                    ->with('item')
                    ->where('type', $this->filter)
                    ->whereBetween('created_at',[$this->dateFrom, $this->dateUntil])
                    ->orderBy('created_at', 'desc')->get();

                    !$transactions->isEmpty() ? $this->reportData = $transactions : session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'No data found in that date range'
                    ]);

                }
                
            }
        }else if($this->filterBy == 'month'){
             /* 
                Jika by month, maka akan menggunakan parameter bulan dari (monthFrom), sampai bulan (monthUntil) dan tahun (year)
            */

            if($this->monthFrom == '' || $this->monthUntil == '' || $this->selectYear == ''){

                session()->flash('dataSession', (object) [
                    'status' => 'failed',
                    'message' => 'Make sure to fill in all month and year inputs'
                ]);

            }else{

                if($this->filter == 'item'){

                    $data = Item::whereYear('created_at', $this->selectYear)
                    ->whereMonth('created_at', '>=', $this->monthFrom)
                    ->whereMonth('created_at', '<=', $this->monthUntil)
                    ->orderBy('created_at', 'desc')
                    ->get();

                    !$data->isEmpty() ? $this->reportData = $data : session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'No data found in that date range'
                    ]);

                }else{

                    $transactions = Transaction::query()
                    ->with('item')
                    ->where('type', $this->filter)
                    ->whereYear('created_at', $this->selectYear)
                    ->whereMonth('created_at', '>=', $this->monthFrom)
                    ->whereMonth('created_at', '<=', $this->monthUntil)
                    ->orderBy('created_at', 'desc')->get();

                    !$transactions->isEmpty() ? $this->reportData = $transactions : session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'No data found in that date range'
                    ]);

            
                }
            }
        }else{
            /* 
                Jika selain itu maka akan menggunakan parameter tahun (year)
            */

            if($this->selectYear == ''){
                session()->flash('dataSession', (object) [
                    'status' => 'failed',
                    'message' => 'Make sure to fill in year inputs'
                ]);
            }else{

                if($this->filter == 'item'){

                    $data = Item::whereYear('created_at', $this->selectYear)
                    ->orderBy('created_at', 'desc')
                    ->get();

                    !$data->isEmpty() ? $this->reportData = $data : session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'No data found in that date range'
                    ]);

                }else{

                    $transactions = Transaction::query()
                    ->with('item')
                    ->where('type', $this->filter)
                    ->whereYear('created_at', $this->selectYear)
                    ->orderBy('created_at', 'desc')->get();

                    !$transactions->isEmpty() ? $this->reportData = $transactions : session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'No data found in that date range'
                    ]);

                }
            }
        }
    }

    /* 
        - handlePrint() method untuk mencetak data
        - data $dataFilter akan disimpan di session yang mana bisa digunakan di halaman lainnya
        - Ketika method dieksekusi maka akan redirect ke halaman print.report yaitu ke komponen ReportPrintComponent
    */
    public function handlePrint(){

        $dataFilter = [
            'filter' => $this->filter,
            'filterBy' => $this->filterBy,
            'dateFrom' => $this->dateFrom,
            'dateUntil' => $this->dateUntil,
            'monthFrom' => $this->monthFrom,
            'monthUntil' => $this->monthUntil,
            'selectYear' => $this->selectYear,
        ];
        session($dataFilter);
    
        // Redirect ke halaman cetak
        return Redirect::route('print.report')->with(['_blank' => true]);
    }

    public function render()
    {
        return view('livewire.report')
            ->layout('components.layouts.app',['data' => $this->data]);
    }
}
