<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use Livewire\Component;

class ReportPrintComponent extends Component
{
    public $reportData;
    public $titleData;
    public $data;

    public function mount()
    {
        $this->data = [
            'title' => 'Cetak Laporan',
            'urlPath' => 'report'
        ];

        $filter = session('filter');
        $filterBy = session('filterBy');

        $query = null;

        if ($filter == 'item') {
            $query = Item::query();
        } else {
            $query = Transaction::with('item')->where('type', $filter);
        }

        if ($filterBy == 'date') {
            $query->whereBetween('created_at', [session('dateFrom'), session('dateUntil')]);
        } elseif ($filterBy == 'month') {
            $query->whereYear('created_at', session('selectYear'))
                ->whereMonth('created_at', '>=', session('monthFrom'))
                ->whereMonth('created_at', '<=', session('monthUntil'));
        } elseif ($filterBy == 'year') {
            $query->whereYear('created_at', session('selectYear'));
        }

        $this->reportData = $query->orderBy('created_at', 'desc')->get();

        if ($this->reportData->isEmpty()) {
            session()->flash('dataSession', ['status' => 'failed', 'message' => 'Tidak ada data yang ditemukan untuk dicetak.']);
        }
        
        switch ($filter) {
            case 'item': $this->titleData = 'Laporan Data Barang'; break;
            case 'in': $this->titleData = 'Laporan Data Barang Masuk'; break;
            case 'out': $this->titleData = 'Laporan Data Barang Keluar'; break;
            case 'damaged': $this->titleData = 'Laporan Data Barang Rusak'; break;
            default: $this->titleData = 'Laporan Inventaris'; break;
        }
    }

    public function render()
    {
        return view('livewire.report-print')
            ->layout('components.layouts.blank', ['data' => $this->data]);
    }
}