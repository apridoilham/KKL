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
    public $filter = '';
    public $filterBy = '';
    public $dateFrom, $dateUntil, $monthFrom, $monthUntil;
    public $selectYear;
    
    public $reportData;
    public $noDataFound = false; // Properti baru untuk menangani state "data tidak ditemukan"

    public function mount()
    {
        $this->data = [
            'title' => 'Generate Reports',
            'urlPath' => 'report'
        ];
        $this->selectYear = Carbon::now()->year;
    }

    public function updatedFilter()
    {
        $this->reset(['filterBy', 'reportData', 'noDataFound']);
    }

    public function handleReset(){
        $this->reset(['filter', 'filterBy', 'reportData', 'noDataFound', 'dateFrom', 'dateUntil', 'monthFrom', 'monthUntil']);
        $this->selectYear = Carbon::now()->year;
    }

    public function generatePreview(){
        $this->reportData = null;
        $this->noDataFound = false;
        $query = null;
        $dataFound = null;

        if ($this->filterBy == 'date') {
            $this->validate(['dateFrom' => 'required|date', 'dateUntil' => 'required|date|after_or_equal:dateFrom']);
            if ($this->filter == 'item') {
                $query = Item::whereBetween('created_at', [$this->dateFrom, $this->dateUntil]);
            } else {
                $query = Transaction::with('item')->where('type', $this->filter)->whereBetween('created_at', [$this->dateFrom, $this->dateUntil]);
            }
        } elseif ($this->filterBy == 'month') {
            $this->validate(['monthFrom' => 'required|integer', 'monthUntil' => 'required|integer|gte:monthFrom', 'selectYear' => 'required|integer']);
            if ($this->filter == 'item') {
                $query = Item::whereYear('created_at', $this->selectYear)->whereMonth('created_at', '>=', $this->monthFrom)->whereMonth('created_at', '<=', $this->monthUntil);
            } else {
                $query = Transaction::with('item')->where('type', $this->filter)->whereYear('created_at', $this->selectYear)->whereMonth('created_at', '>=', $this->monthFrom)->whereMonth('created_at', '<=', $this->monthUntil);
            }
        } else { // year
            $this->validate(['selectYear' => 'required|integer']);
            if ($this->filter == 'item') {
                $query = Item::whereYear('created_at', $this->selectYear);
            } else {
                $query = Transaction::with('item')->where('type', $this->filter)->whereYear('created_at', $this->selectYear);
            }
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
            return; // Mencegah print jika tidak ada data
        }

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
    
        // Mengirim event ke frontend untuk membuka tab baru
        $this->dispatch('open-new-tab', url: route('print.report'));
    }

    public function render()
    {
        return view('livewire.report')->layout('components.layouts.app', ['data' => $this->data]);
    }
}