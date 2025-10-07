<?php

namespace App\Http\Livewire;

use App\Traits\BuildsReportQuery;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ReportComponent extends Component
{
    use BuildsReportQuery;

    public array $data;
    public string $filter = '';
    public string $filterBy = '';
    public ?string $dateFrom = null, $dateUntil = null;
    public int $monthFrom, $monthUntil;
    public int $selectYear;
    public $reportData;
    public bool $noDataFound = false;

    public function mount(): void
    {
        Gate::authorize('view-reports');

        $this->data = ['title' => 'Buat Laporan', 'urlPath' => 'report'];
        $this->handleReset();
    }

    public function updatedFilter(): void
    {
        $this->reset(['filterBy', 'reportData', 'noDataFound']);
    }

    public function handleReset(): void
    {
        $this->reset(['filter', 'filterBy', 'reportData', 'noDataFound', 'dateFrom', 'dateUntil']);
        $this->selectYear = Carbon::now()->year;
        $this->monthFrom = 1;
        $this->monthUntil = 12;
    }

    public function generatePreview(): void
    {
        $this->reportData = null;
        $this->noDataFound = false;
        $rules = [];
        if ($this->filterBy == 'date') {
            $rules = ['dateFrom' => 'required|date', 'dateUntil' => 'required|date|after_or_equal:dateFrom'];
        } elseif ($this->filterBy == 'month') {
            $rules = ['monthFrom' => 'required|integer', 'monthUntil' => 'required|integer|gte:monthFrom', 'selectYear' => 'required|integer'];
        } elseif ($this->filterBy == 'year') {
            $rules = ['selectYear' => 'required|integer'];
        }
        $validated = $this->validate($rules);
        
        $dataFound = $this->buildReportQuery($this->filter, $this->filterBy, array_merge($validated, [
            'monthFrom' => $this->monthFrom, 'monthUntil' => $this->monthUntil, 'selectYear' => $this->selectYear
        ]))->get();

        if ($dataFound->isNotEmpty()) {
            $this->reportData = $dataFound;
        } else {
            $this->noDataFound = true;
        }
    }

    public function handlePrint(): void
    {
        if (!$this->reportData) return;
        $queryParams = ['filter' => $this->filter, 'filterBy' => $this->filterBy, 'dateFrom' => $this->dateFrom, 'dateUntil' => $this->dateUntil, 'monthFrom' => $this->monthFrom, 'monthUntil' => $this->monthUntil, 'selectYear' => $this->selectYear];
        $url = route('print.report', array_filter($queryParams));
        $this->dispatch('open-new-tab', url: $url);
    }

    public function render()
    {
        return view('livewire.report')->layout('components.layouts.app', ['data' => $this->data]);
    }
}