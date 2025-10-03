<?php

namespace App\Http\Controllers;

use App\Exports\ItemsExport;
use App\Exports\TransactionsExport;
use App\Traits\BuildsReportQuery;
use Illuminate\Http\Request;
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
            'dateFrom' => 'nullable|date',
            'dateUntil' => 'nullable|date',
            'monthFrom' => 'nullable|integer',
            'monthUntil' => 'nullable|integer',
            'selectYear' => 'nullable|integer',
        ]);

        $filter = $validated['filter'];
        $filterBy = $validated['filterBy'];

        $query = $this->buildReportQuery($filter, $filterBy, $validated);
        $data = $query->get(); // Fetch data menjadi collection

        $fileName = 'laporan-' . $filter . '-' . now()->format('Y-m-d') . '.' . $type;

        switch ($type) {
            case 'pdf':
                $view = $filter === 'item' ? 'livewire.reports.item-table' : 'livewire.reports.transaction-table';
                $pdf = Pdf::loadView('livewire.report-print-template', ['data' => $data, 'view' => $view, 'title' => 'Laporan ' . ucfirst($filter)]);
                return $pdf->download($fileName);

            case 'xlsx':
                // Kirim collection data ke export class
                $export = $filter === 'item' ? new ItemsExport($data) : new TransactionsExport($data);
                return Excel::download($export, $fileName);
                
            case 'csv':
                 // Kirim collection data ke export class
                $export = $filter === 'item' ? new ItemsExport($data) : new TransactionsExport($data);
                return Excel::download($export, $fileName, \Maatwebsite\Excel\Excel::CSV);
        }

        return redirect()->back();
    }
}