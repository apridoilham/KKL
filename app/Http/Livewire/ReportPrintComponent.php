<?php

namespace App\Http\Livewire;

use App\Traits\BuildsReportQuery;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class ReportPrintComponent extends Component
{
    use BuildsReportQuery;

    public $reportData;
    public string $titleData = 'Laporan Inventaris';
    public array $data;
    public string $filter, $filterBy;
    public array $params;

    public function mount(): void
    {
        $this->data = ['title' => 'Cetak Laporan', 'urlPath' => 'report'];
        $requestData = request()->query();
        $validator = Validator::make($requestData, [
            'filter' => 'required|in:item,in,out,damaged',
            'filterBy' => 'required|in:date,month,year',
            'itemType' => 'required|in:all,barang_mentah,barang_jadi',
            'dateFrom' => 'required_if:filterBy,date|date',
            'dateUntil' => 'required_if:filterBy,date|date',
            'monthFrom' => 'required_if:filterBy,month|integer',
            'monthUntil' => 'required_if:filterBy,month|integer',
            'selectYear' => 'required_if:filterBy,month,year|integer',
        ]);

        if ($validator->fails()) {
            $this->reportData = null;
            session()->flash('dataSession', ['status' => 'failed', 'message' => 'Parameter laporan tidak valid.']);
            return;
        }

        $validated = $validator->validated();
        $this->filter = $validated['filter'];
        $this->filterBy = $validated['filterBy'];
        $this->params = $validated;
        $this->reportData = $this->buildReportQuery($this->filter, $this->filterBy, $this->params)->get();

        if ($this->reportData->isEmpty()) {
            session()->flash('dataSession', ['status' => 'failed', 'message' => 'Tidak ada data yang ditemukan untuk dicetak.']);
        }

        switch ($this->filter) {
            case 'item': $this->titleData = 'Laporan Data Barang'; break;
            case 'in': $this->titleData = 'Laporan Barang Masuk'; break;
            case 'out': $this->titleData = 'Laporan Barang Keluar'; break;
            case 'damaged': $this->titleData = 'Laporan Barang Rusak'; break;
        }
    }

    public function render()
    {
        return view('livewire.report-print')->layout('components.layouts.blank', ['data' => $this->data]);
    }
}