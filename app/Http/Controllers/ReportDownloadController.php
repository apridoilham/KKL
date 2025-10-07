<?php

namespace App\Http\Controllers;

use App\Exports\ItemsExport;
use App\Exports\TransactionsExport;
use App\Traits\BuildsReportQuery;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controller;

class ReportDownloadController extends Controller
{
    use BuildsReportQuery;

    public function download(Request $request, string $type)
    {
        $validated = $request->validate([
            'filter' => 'required|in:item,in,out,damaged,pembelian_masuk,produksi_masuk,produksi_keluar,pengiriman_keluar,rusak',
            'filterBy' => 'required|in:date,month,year',
            'dateFrom' => 'nullable|date',
            'dateUntil' => 'nullable|date',
            'monthFrom' => 'nullable|integer',
            'monthUntil' => 'nullable|integer',
            'selectYear' => 'nullable|integer',
        ]);

        $filter = $validated['filter'];
        $filterBy = $validated['filterBy'];

        $query = $this->buildReportQuery($filter, $filterBy, $validated);
        $data = $query->get();

        $fileName = 'laporan-' . $filter . '-' . now()->format('Y-m-d') . '.' . $type;

        $exportClass = $filter === 'item' ? new ItemsExport($data) : new TransactionsExport($data);

        switch ($type) {
            case 'pdf':
                $view = $filter === 'item' ? 'livewire.reports.item-table' : 'livewire.reports.transaction-table';
                $pdf = Pdf::loadView('livewire.report-print-template', ['data' => $data, 'view' => $view, 'title' => 'Laporan ' . ucfirst($filter)]);
                return $pdf->download($fileName);

            case 'xlsx':
                return Excel::download($exportClass, $fileName);

            case 'csv':
                return Excel::download($exportClass, $fileName, \Maatwebsite\Excel\Excel::CSV);
        }
    }
}