<?php

namespace App\Http\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class HomeComponent extends Component
{
    public array $data;
    public string $filterType = 'all_time';
    public string $filterDate;
    public string $filterMonth;
    public string $filterYear;

    // Properti Statistik
    public int $totalItems = 0, $totalRawItems = 0, $totalFinishedItems = 0;
    public int $totalUsers = 0, $totalStock = 0, $totalRawStock = 0, $totalFinishedStock = 0;
    public int $totalIn = 0, $totalInRaw = 0, $totalInFinished = 0;
    public int $totalOut = 0, $totalOutUsed = 0, $totalOutShippedRaw = 0, $totalOutShippedFinished = 0;
    public int $totalDamaged = 0, $totalDamagedRaw = 0, $totalDamagedFinished = 0;

    public function mount(): void
    {
        $this->data = ['title' => 'Dashboard', 'urlPath' => 'home'];
        $this->resetFilters(false);
        $this->loadDashboardData();
    }

    public function resetFilters($loadData = true): void
    {
        $this->filterType = 'all_time';
        $this->filterDate = now()->format('Y-m-d');
        $this->filterMonth = now()->format('Y-m');
        $this->filterYear = now()->format('Y');
        if ($loadData) {
            $this->loadDashboardData();
        }
    }
    
    public function applyDashboardFilter($type, $date = null, $month = null, $year = null)
    {
        $this->filterType = $type;
        if ($date) $this->filterDate = $date;
        if ($month) $this->filterMonth = $month;
        if ($year) $this->filterYear = $year;

        $this->loadDashboardData();
    }

    public function loadDashboardData(): void
    {
        $this->updateStatistics();
    }

    private function applyTimeFilter(Builder $query): Builder
    {
        switch ($this->filterType) {
            case 'daily':
                return $query->whereDate('created_at', $this->filterDate);
            case 'monthly':
                try {
                    $date = Carbon::parse($this->filterMonth);
                    return $query->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month);
                } catch (\Exception $e) { return $query; }
            case 'yearly':
                return $query->whereYear('created_at', $this->filterYear);
            default:
                return $query;
        }
    }

    public function updateStatistics(): void
    {
        $baseTransactionQuery = $this->applyTimeFilter(Transaction::query());
        $itemCounts = Item::selectRaw("
                COUNT(*) as total_items,
                SUM(CASE WHEN item_type = 'barang_mentah' THEN 1 ELSE 0 END) as total_raw_items,
                SUM(CASE WHEN item_type = 'barang_jadi' THEN 1 ELSE 0 END) as total_finished_items,
                SUM(quantity) as total_stock,
                SUM(CASE WHEN item_type = 'barang_mentah' THEN quantity ELSE 0 END) as total_raw_stock,
                SUM(CASE WHEN item_type = 'barang_jadi' THEN quantity ELSE 0 END) as total_finished_stock
            ")->first();

        $this->totalUsers = User::count();
        $this->totalItems = (int) $itemCounts->total_items;
        $this->totalRawItems = (int) $itemCounts->total_raw_items;
        $this->totalFinishedItems = (int) $itemCounts->total_finished_items;
        $this->totalStock = (int) $itemCounts->total_stock;
        $this->totalRawStock = (int) $itemCounts->total_raw_stock;
        $this->totalFinishedStock = (int) $itemCounts->total_finished_stock;
        
        $this->totalIn = (int) (clone $baseTransactionQuery)->whereIn('type', ['masuk_mentah', 'masuk_jadi'])->sum('quantity');
        $this->totalInRaw = (int) (clone $baseTransactionQuery)->where('type', 'masuk_mentah')->sum('quantity');
        $this->totalInFinished = (int) (clone $baseTransactionQuery)->where('type', 'masuk_jadi')->sum('quantity');
        $this->totalOut = (int) (clone $baseTransactionQuery)->whereIn('type', ['keluar_dikirim', 'keluar_terpakai', 'keluar_mentah'])->sum('quantity');
        $this->totalOutUsed = (int) (clone $baseTransactionQuery)->where('type', 'keluar_terpakai')->sum('quantity');
        $this->totalOutShippedRaw = (int) (clone $baseTransactionQuery)->where('type', 'keluar_mentah')->sum('quantity');
        $this->totalOutShippedFinished = (int) (clone $baseTransactionQuery)->where('type', 'keluar_dikirim')->sum('quantity');
        $this->totalDamaged = (int) (clone $baseTransactionQuery)->where('type', 'rusak')->sum('quantity');
        $this->totalDamagedRaw = (int) (clone $baseTransactionQuery)->where('type', 'rusak')->whereHas('item', fn($q) => $q->where('item_type', 'barang_mentah'))->sum('quantity');
        $this->totalDamagedFinished = (int) (clone $baseTransactionQuery)->where('type', 'rusak')->whereHas('item', fn($q) => $q->where('item_type', 'barang_jadi'))->sum('quantity');
    }

    public function render()
    {
        return view('livewire.home')->layout('components.layouts.app', ['data' => $this->data]);
    }
}