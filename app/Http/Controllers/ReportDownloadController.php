<?php

namespace App\Http\Controllers;

use App\Exports\ItemsExport;
use App\Exports\TransactionsExport;
use App\Traits\BuildsReportQuery;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportDownloadController extends Controller
{
    use BuildsReportQuery;

    public function download(Request $request, string $type)
    {
        $validated = $request->validate([
            'filter' => 'required|in:item,in,out,damaged',
            'filterBy' => 'required|in:date,month,year',
            'itemType' => 'required|in:all,barang_mentah,barang_jadi',
            'dateFrom' => 'required_if:filterBy,date|nullable|date',
            'dateUntil' => 'required_if:filterBy,date|nullable|date|after_or_equal:dateFrom',
            'monthFrom' => 'required_if:filterBy,month|nullable|integer|min:1|max:12',
            'monthUntil' => 'required_if:filterBy,month|nullable|integer|min:1|max:12|gte:monthFrom',
            'selectYear' => 'required_if:filterBy,month,year|nullable|integer',
        ]);

        $filter = $validated['filter'];
        $filterBy = $validated['filterBy'];

        $query = $this->buildReportQuery($filter, $filterBy, $validated);
        $data = $query->get();

        $fileName = 'laporan-' . $filter . '-' . now()->format('Y-m-d') . '.' . $type;

        $exportClass = $filter === 'item' ? new ItemsExport($data) : new TransactionsExport($data);

        return match ($type) {
            'pdf' => Pdf::loadView('livewire.report-print-template', [
                'data' => $data,
                'view' => $filter === 'item' ? 'livewire.reports.item-table' : 'livewire.reports.transaction-table',
                'title' => 'Laporan ' . ucfirst($filter)
            ])->download($fileName),

            'xlsx' => Excel::download($exportClass, $fileName),

            'csv' => Excel::download($exportClass, $fileName, \Maatwebsite\Excel\Excel::CSV),
        };
    }
}