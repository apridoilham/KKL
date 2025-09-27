<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ReportPrintComponent extends Component
{
    public $data;
    public $filter, $filterBy;
    public $dateFrom, $dateUntil, $monthFrom, $monthUntil;
    public $selectYear;
    public $no = 1;
    public $reportData;
    public $titleData;

    public function mount()
    {

        if(session('filterBy') == 'date'){
            if(session('dateFrom') == '' || session('dateUntil') == ''){
                session()->flash('dataSession', (object) [
                    'status' => 'failed',
                    'message' => 'Make sure to fill in all date inputs'
                ]);
            }else{
                if(session('filter') == 'item'){
                    $data = Item::whereBetween('created_at',[session('dateFrom'), session('dateUntil')])
                    ->orderBy('created_at', 'desc')
                    ->get();

                    !$data->isEmpty() ? $this->reportData = $data : session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'No data found in that date range'
                    ]);

                    

                    !$data->isEmpty() ? Log::info('Filter data:', $this->reportData->toArray()) : '';
                }else{
                    $transactions = Transaction::query()
                    ->with('item')
                    ->where('type', session('filter'))
                    ->whereBetween('created_at',[session('dateFrom'), session('dateUntil')])
                    ->orderBy('created_at', 'desc')->get();

                    !$transactions->isEmpty() ? $this->reportData = $transactions : session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'No data found in that date range'
                    ]);

                    !$transactions->isEmpty() ? Log::info('Filter data:', $this->reportData->toArray()) : '';

                }
                
            }
        }else if(session('filterBy') == 'month'){
            if(session('monthFrom') == '' || session('monthUntil') == '' || session('selectYear') == ''){
                session()->flash('dataSession', (object) [
                    'status' => 'failed',
                    'message' => 'Make sure to fill in all month and year inputs'
                ]);
            }else{
                if(session('filter') == 'item'){
                    $data = Item::whereYear('created_at', session('selectYear'))
                    ->whereMonth('created_at', '>=', session('monthFrom'))
                    ->whereMonth('created_at', '<=', session('monthUntil'))
                    ->orderBy('created_at', 'desc')
                    ->get();

                    !$data->isEmpty() ? $this->reportData = $data : session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'No data found in that date range'
                    ]);
                }else{
                    $transactions = Transaction::query()
                    ->with('item')
                    ->where('type', session('filter'))
                    ->whereYear('created_at', session('selectYear'))
                    ->whereMonth('created_at', '>=', session('monthFrom'))
                    ->whereMonth('created_at', '<=', session('monthUntil'))
                    ->orderBy('created_at', 'desc')->get();

                    !$transactions->isEmpty() ? $this->reportData = $transactions : session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'No data found in that date range'
                    ]);

                    
                }
            }
        }else{
            if(session('selectYear') == ''){
                session()->flash('dataSession', (object) [
                    'status' => 'failed',
                    'message' => 'Make sure to fill in year inputs'
                ]);
            }else{
                if(session('filter') == 'item'){
                    $data = Item::whereYear('created_at', session('selectYear'))
                    ->orderBy('created_at', 'desc')
                    ->get();

                    !$data->isEmpty() ? $this->reportData = $data : session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'No data found in that date range'
                    ]);
                }else{
                    $transactions = Transaction::query()
                    ->with('item')
                    ->where('type', session('filter'))
                    ->whereYear('created_at', session('selectYear'))
                    ->orderBy('created_at', 'desc')->get();

                    !$transactions->isEmpty() ? $this->reportData = $transactions : session()->flash('dataSession', (object) [
                        'status' => 'failed',
                        'message' => 'No data found in that date range'
                    ]);

                }
            }
        }

        $this->data = [
            'title' => 'Report Print Page',
            'urlPath' => 'report'
        ];
        if(session('filter')  == 'item'){
            $this->titleData = 'Item data report';
        }elseif(session('filter')  == 'in'){
            $this->titleData = 'Incoming item data report';
        }elseif(session('filter')  == 'out'){
            $this->titleData = 'Outgoing item data report';
        }elseif(session('filter')  == 'damaged'){
            $this->titleData = 'Damaged item data report';

        }

    }

    public function render()
    {
        return view('livewire.report-print')->layout('components.layouts.blank'); // Menggunakan layout koson;
    }
}
