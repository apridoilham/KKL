<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Component;

class ReportComponent extends Component
{
    public $data;
    public $filter = '';
    public $filterBy = '';
    public $dateFrom, $dateUntil, $monthFrom, $monthUntil;
    public $selectYear;
    
    public $reportData;
    public $noDataFound = false;

    public function mount()
    {
        $this->data = [
            'title' => 'Buat Laporan',
            'urlPath' => 'report'
        ];
        $this->selectYear = Carbon::now()->year;
        $this->monthFrom = 1;
        $this->monthUntil = 12;
    }

    public function updatedFilter()
    {
        $this->reset(['filterBy', 'reportData', 'noDataFound']);
    }

    public function handleReset(){
        $this->reset(['filter', 'filterBy', 'reportData', 'noDataFound', 'dateFrom', 'dateUntil', 'monthFrom', 'monthUntil']);
        $this->selectYear = Carbon::now()->year;
        $this->monthFrom = 1;
        $this->monthUntil = 12;
    }

    public function generatePreview(){
        $this->reportData = null;
        $this->noDataFound = false;
        $query = null;
        
        $rules = [];
        if ($this->filterBy == 'date') {
            $rules = ['dateFrom' => 'required|date', 'dateUntil' => 'required|date|after_or_equal:dateFrom'];
        } elseif ($this->filterBy == 'month') {
            $rules = ['monthFrom' => 'required|integer', 'monthUntil' => 'required|integer|gte:monthFrom', 'selectYear' => 'required|integer'];
        } elseif ($this->filterBy == 'year') {
            $rules = ['selectYear' => 'required|integer'];
        }
        $this->validate($rules);
        
        if ($this->filter == 'item') {
            $query = Item::query();
        } else {
            $query = Transaction::with('item')->where('type', $this->filter);
        }

        if ($this->filterBy == 'date') {
            $query->whereBetween('created_at', [$this->dateFrom, $this->dateUntil]);
        } elseif ($this->filterBy == 'month') {
            $query->whereYear('created_at', $this->selectYear)->whereMonth('created_at', '>=', $this->monthFrom)->whereMonth('created_at', '<=', $this->monthUntil);
        } elseif ($this->filterBy == 'year') {
            $query->whereYear('created_at', $this->selectYear);
        }

        $dataFound = $query->orderBy('created_at', 'desc')->get();

        if ($dataFound->isNotEmpty()) {
            $this->reportData = $dataFound;
        } else {
            $this->noDataFound = true;
        }
    }

    public function handlePrint(){
        if (!$this->reportData) {
            return;
        }

        session([
            'filter' => $this->filter,
            'filterBy' => $this->filterBy,
            'dateFrom' => $this->dateFrom,
            'dateUntil' => $this->dateUntil,
            'monthFrom' => $this->monthFrom,
            'monthUntil' => $this->monthUntil,
            'selectYear' => $this->selectYear,
        ]);
    
        $this->dispatch('open-new-tab', url: route('print.report'));
    }

    public function render()
    {
        return view('livewire.report')
            ->layout('components.layouts.app', ['data' => $this->data]);
    }
}