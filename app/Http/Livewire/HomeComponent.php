<?php

namespace App\Http\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class HomeComponent extends Component
{
    public array $data;
    public int $totalItems = 0, $totalIn = 0, $totalOut = 0, $totalDamaged = 0, $totalUsers = 0, $totalStock = 0;
    public array $categoryLabels = [], $categoryData = [];
    public array $trendLabels = ['Masuk', 'Keluar', 'Rusak'], $trendData = [];
    public array $topStockLabels = [], $topStockData = [];
    public array $chartPalette1 = ['#4A55A2', '#7895CB', '#A0BFE0', '#C5DFF8', '#F0F0F0'];
    public bool $isModalOpen = false, $isModalOpenData = false;
    public string $name = '', $username = '';
    public ?string $password = null, $newPassword = null, $confPass = null;
    public string $confirmationPassword = '';

    public string $filterType = 'all_time';
    public string $filterDate;
    public string $filterMonth;
    public string $filterYear;

    public function mount(): void
    {
        $this->data = ['title' => 'Dashboard', 'urlPath' => 'home'];
        $this->resetFilters(false);
        $this->loadDashboardData();
        $user = auth()->user();
        $this->name = $user->name;
        $this->username = $user->username;
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

    public function updatedFilterType(): void { $this->loadDashboardData(); }
    public function updatedFilterDate(): void { $this->loadDashboardData(); }
    public function updatedFilterMonth(): void { $this->loadDashboardData(); }
    public function updatedFilterYear(): void { $this->loadDashboardData(); }

    public function loadDashboardData(): void
    {
        $this->updateStatistics();
        $this->updateChartData();

        $this->dispatch('charts-updated',
            topStockLabels: $this->topStockLabels,
            topStockData: $this->topStockData,
            categoryLabels: $this->categoryLabels,
            categoryData: $this->categoryData,
            trendLabels: $this->trendLabels,
            trendData: $this->trendData,
            chartPalette1: $this->chartPalette1
        );
    }

    private function applyTimeFilter(Builder $query, string $dateColumn = 'created_at'): Builder
    {
        switch ($this->filterType) {
            case 'daily':
                return $query->whereDate($dateColumn, $this->filterDate);
            case 'monthly':
                $date = Carbon::parse($this->filterMonth);
                return $query->whereYear($dateColumn, $date->year)->whereMonth($dateColumn, $date->month);
            case 'yearly':
                return $query->whereYear($dateColumn, $this->filterYear);
            default:
                return $query;
        }
    }

    public function updateStatistics(): void
    {
        $cacheKey = 'dashboard-stats-' . $this->filterType . '-' . $this->filterDate . $this->filterMonth . $this->filterYear;
        $stats = Cache::remember($cacheKey, config('inventory.stats_cache_duration', 300), function () {
            // Mengambil total transaksi dalam satu query
            $transactionQuery = $this->applyTimeFilter(Transaction::query());
            $transactionTotals = $transactionQuery
                ->selectRaw("
                    SUM(CASE WHEN type IN ('pembelian_masuk', 'produksi_masuk') THEN quantity ELSE 0 END) as total_in,
                    SUM(CASE WHEN type IN ('pengiriman_keluar', 'produksi_keluar') THEN quantity ELSE 0 END) as total_out,
                    SUM(CASE WHEN type = 'rusak' THEN quantity ELSE 0 END) as total_damaged
                ")
                ->first();

            return [
                'total_items' => Item::count(),
                'total_users' => User::count(),
                'total_stock' => (int) Item::sum('quantity'),
                'total_in' => (int) $transactionTotals->total_in,
                'total_out' => (int) $transactionTotals->total_out,
                'total_damaged' => (int) $transactionTotals->total_damaged,
            ];
        });

        $this->totalItems = $stats['total_items'];
        $this->totalUsers = $stats['total_users'];
        $this->totalStock = $stats['total_stock'];
        $this->totalIn = $stats['total_in'];
        $this->totalOut = $stats['total_out'];
        $this->totalDamaged = $stats['total_damaged'];
    }

    public function updateChartData(): void
    {
        // Menggunakan applyTimeFilter yang sudah digabung
        $filteredQuery = $this->applyTimeFilter(
            Item::query()->join('transactions', 'items.id', '=', 'transactions.item_id'),
            'transactions.created_at'
        );

        $categoryInfo = (clone $filteredQuery)
            ->select('items.category', DB::raw('SUM(transactions.quantity) as total_quantity'))
            ->groupBy('items.category')
            ->get();

        $this->categoryLabels = $categoryInfo->pluck('category')->map(fn ($cat) => $cat ?? 'Tanpa Kategori')->toArray();
        $this->categoryData = $categoryInfo->pluck('total_quantity')->toArray();
        if (empty($this->categoryLabels)) {
            $this->categoryLabels = ['Belum Ada Data'];
            $this->categoryData = [0];
        }

        $topItems = (clone $filteredQuery)
            ->select('items.name', DB::raw('SUM(transactions.quantity) as total_activity'))
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('total_activity')
            ->take(5)
            ->get();

        $this->topStockLabels = $topItems->pluck('name')->toArray();
        $this->topStockData = $topItems->pluck('total_activity')->toArray();

        $this->trendData = [$this->totalIn, $this->totalOut, $this->totalDamaged];
        if (array_sum($this->trendData) === 0) {
            $this->trendData = [0, 0, 0];
        }
    }

    public function changePassword(): void
    {
        $this->validate([
            'password' => 'required',
            'newPassword' => 'required|min:6',
            'confPass' => 'required|same:newPassword',
        ]);
        $user = auth()->user();
        if (!Hash::check($this->password, $user->password)) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => 'Password saat ini salah.']);
            return;
        }
        $user->update(['password' => Hash::make($this->newPassword)]);
        $this->dispatch('toast', ['status' => 'success', 'message' => 'Password berhasil diperbarui.']);
        $this->reset('password', 'newPassword', 'confPass');
        $this->isModalOpen = false;
    }

    public function changeData(): void
    {
        $this->validate(['name' => 'required|string', 'confirmationPassword' => 'required|string']);
        $user = auth()->user();
        if (!Hash::check($this->confirmationPassword, $user->password)) {
            $this->addError('confirmationPassword', 'Password konfirmasi yang Anda masukkan salah.');
            return;
        }
        $user->update(['name' => $this->name]);
        $this->dispatch('toast', ['status' => 'success', 'message' => 'Nama Anda berhasil diperbarui.']);
        $this->isModalOpenData = false;
        $this->reset('confirmationPassword');
    }

    public function render()
    {
        return view('livewire.home')->layout('components.layouts.app', ['data' => $this->data]);
    }
}