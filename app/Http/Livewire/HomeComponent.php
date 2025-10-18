<?php

namespace App\Http\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class HomeComponent extends Component
{
    public array $data;
    public string $filterType = 'all_time';
    public string $filterDate;
    public string $filterMonth;
    public string $filterYear;

    public int $totalItems = 0, $totalRawItems = 0, $totalFinishedItems = 0;
    public int $totalUsers = 0, $totalAdmin = 0, $totalProduksi = 0, $totalPengiriman = 0;
    public int $totalStock = 0, $totalRawStock = 0, $totalFinishedStock = 0;
    public int $totalIn = 0, $totalInRaw = 0, $totalInFinished = 0;
    public int $totalOut = 0, $totalOutUsed = 0, $totalOutShippedRaw = 0, $totalOutShippedFinished = 0;
    public int $totalDamaged = 0, $totalDamagedRaw = 0, $totalDamagedFinished = 0;

    public function mount(): void
    {
        $this->data = ['title' => 'Dashboard', 'urlPath' => 'home'];
        $this->resetFilters(false);
        $this->updateStatistics();
    }

    public function resetFilters($loadData = true): void
    {
        $this->filterType = 'all_time';
        $this->filterDate = now()->format('Y-m-d');
        $this->filterMonth = now()->format('Y-m');
        $this->filterYear = now()->format('Y');
        if ($loadData) {
            $this->updateStatistics();
        }
    }

    public function applyDashboardFilter($type, $date = null, $month = null, $year = null)
    {
        $this->filterType = $type;
        if ($date) $this->filterDate = $date;
        if ($month) $this->filterMonth = $month;
        if ($year) $this->filterYear = $year;

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
                } catch (\Exception $e) {
                    return $query;
                }
            case 'yearly':
                return $query->whereYear('created_at', $this->filterYear);
            default:
                return $query;
        }
    }

    public function updateStatistics(): void
    {
        $filterKey = $this->filterType . '-' . $this->filterDate . '-' . $this->filterMonth . '-' . $this->filterYear;
        $cacheDuration = config('inventory.stats_cache_duration', 300);

        $stats = Cache::remember('dashboard-stats-' . $filterKey, $cacheDuration, function () {
            $itemCounts = Item::selectRaw("
                COUNT(*) as total_items,
                SUM(CASE WHEN item_type = 'barang_mentah' THEN 1 ELSE 0 END) as total_raw_items,
                SUM(CASE WHEN item_type = 'barang_jadi' THEN 1 ELSE 0 END) as total_finished_items,
                SUM(quantity) as total_stock,
                SUM(CASE WHEN item_type = 'barang_mentah' THEN quantity ELSE 0 END) as total_raw_stock,
                SUM(CASE WHEN item_type = 'barang_jadi' THEN quantity ELSE 0 END) as total_finished_stock
            ")->first();

            // ---- Perubahan Query Raw Transaksi ----
            $transactionCounts = $this->applyTimeFilter(Transaction::query())
                ->selectRaw("
                    SUM(CASE WHEN type IN ('masuk_mentah', 'produksi_jadi') THEN quantity ELSE 0 END) as total_in,
                    SUM(CASE WHEN type = 'masuk_mentah' THEN quantity ELSE 0 END) as total_in_raw,
                    SUM(CASE WHEN type = 'produksi_jadi' THEN quantity ELSE 0 END) as total_in_finished,
                    SUM(CASE WHEN type IN ('keluar_dikirim', 'produksi_terpakai', 'keluar_mentah') THEN quantity ELSE 0 END) as total_out,
                    SUM(CASE WHEN type = 'produksi_terpakai' THEN quantity ELSE 0 END) as total_out_used,
                    SUM(CASE WHEN type = 'keluar_mentah' THEN quantity ELSE 0 END) as total_out_shipped_raw,
                    SUM(CASE WHEN type = 'keluar_dikirim' THEN quantity ELSE 0 END) as total_out_shipped_finished,
                    SUM(CASE WHEN type IN ('rusak_mentah', 'rusak_jadi') THEN quantity ELSE 0 END) as total_damaged,
                    SUM(CASE WHEN type = 'rusak_mentah' THEN quantity ELSE 0 END) as total_damaged_raw,
                    SUM(CASE WHEN type = 'rusak_jadi' THEN quantity ELSE 0 END) as total_damaged_finished
                ")->first();
            // ------------------------------------

            $userCountsByRole = User::query()
                ->select('role', DB::raw('count(*) as total'))
                ->groupBy('role')
                ->pluck('total', 'role');

            return [
                'totalUsers' => $userCountsByRole->sum(),
                'totalAdmin' => (int) ($userCountsByRole['admin'] ?? 0),
                'totalProduksi' => (int) ($userCountsByRole['produksi'] ?? 0),
                'totalPengiriman' => (int) ($userCountsByRole['pengiriman'] ?? 0),
                'totalItems' => (int) ($itemCounts->total_items ?? 0), // Tambah null safety
                'totalRawItems' => (int) ($itemCounts->total_raw_items ?? 0),
                'totalFinishedItems' => (int) ($itemCounts->total_finished_items ?? 0),
                'totalStock' => (int) ($itemCounts->total_stock ?? 0),
                'totalRawStock' => (int) ($itemCounts->total_raw_stock ?? 0),
                'totalFinishedStock' => (int) ($itemCounts->total_finished_stock ?? 0),
                'totalIn' => (int) ($transactionCounts->total_in ?? 0), // Tambah null safety
                'totalInRaw' => (int) ($transactionCounts->total_in_raw ?? 0),
                'totalInFinished' => (int) ($transactionCounts->total_in_finished ?? 0),
                'totalOut' => (int) ($transactionCounts->total_out ?? 0),
                'totalOutUsed' => (int) ($transactionCounts->total_out_used ?? 0),
                'totalOutShippedRaw' => (int) ($transactionCounts->total_out_shipped_raw ?? 0),
                'totalOutShippedFinished' => (int) ($transactionCounts->total_out_shipped_finished ?? 0),
                'totalDamaged' => (int) ($transactionCounts->total_damaged ?? 0),
                'totalDamagedRaw' => (int) ($transactionCounts->total_damaged_raw ?? 0),
                'totalDamagedFinished' => (int) ($transactionCounts->total_damaged_finished ?? 0),
            ];
        });

        foreach ($stats as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function render()
    {
        return view('livewire.home')->layout('components.layouts.app', ['data' => $this->data]);
    }
}