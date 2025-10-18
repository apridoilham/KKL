<?php

namespace App\Http\Controllers;

use App\Exports\ItemsExport;
use App\Exports\TransactionsExport;
use App\Http\Requests\ReportRequest; // <- Ganti use Request
use App\Traits\BuildsReportQuery;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportDownloadController extends Controller
{
    use BuildsReportQuery;

    // <- Ganti tipe parameter $request
    public function download(ReportRequest $request, string $type)
    {
        // <- Hapus blok validasi manual, gunakan validated() dari Form Request
        $validated = $request->validated();

        $filter = $validated['filter'];
        $filterBy = $validated['filterBy'];

        // <- Gunakan $validated untuk parameter query
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