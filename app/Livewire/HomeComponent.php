<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
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
    public bool $isModalOpen = false;
    public bool $isModalOpenData = false;
    public string $name = '', $username = '';
    public ?string $password = null, $newPassword = null, $confPass = null;
    public string $confirmationPassword = '';

    public function mount(): void
    {
        $this->data = [
            'title' => 'Dashboard',
            'urlPath' => 'home'
        ];

        $this->loadInitialData();

        $user = auth()->user();
        $this->name = $user->name;
        $this->username = $user->username;
    }

    public function loadInitialData(): void
    {
        $this->updateStatistics();
        $this->updateChartData();
    }

    public function updateStatistics(): void
    {
        $duration = config('inventory.stats_cache_duration', 300);

        $this->totalItems = Cache::remember('stats:total_items', $duration, fn() => Item::count());
        $this->totalUsers = Cache::remember('stats:total_users', $duration, fn() => User::count());
        $this->totalStock = (int) Cache::remember('stats:total_stock', $duration, fn() => Item::sum('quantity'));
        $this->totalIn = (int) Cache::remember('stats:total_in', $duration, fn() => Transaction::where('type', 'in')->sum('quantity'));
        $this->totalOut = (int) Cache::remember('stats:total_out', $duration, fn() => Transaction::where('type', 'out')->sum('quantity'));
        // PERBAIKAN: Mengganti titik (.) dengan panah (->)
        $this->totalDamaged = (int) Cache::remember('stats:total_damaged', $duration, fn() => Transaction::where('type', 'damaged')->sum('quantity'));
    }

    public function updateChartData(): void
    {
        $categoryInfo = Item::select('category', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('category')->get();
        // PERBAIKAN: Mengganti titik (.) dengan panah (->)
        $this->categoryLabels = $categoryInfo->pluck('category')->map(fn ($cat) => $cat ?? 'Tanpa Kategori')->toArray();
        $this->categoryData = $categoryInfo->pluck('total_quantity')->toArray();
        if (empty($this->categoryLabels)) {
            $this->categoryLabels = ['Belum Ada Data'];
            $this->categoryData = [0];
        }
        $this->trendData = [$this->totalIn, $this->totalOut, $this->totalDamaged];
        if (array_sum($this->trendData) === 0) {
            $this->trendData = [0, 0, 0];
        }
        $topItems = Item::orderByDesc('quantity')->take(5)->get();
        $this->topStockLabels = $topItems->pluck('name')->toArray();
        $this->topStockData = $topItems->pluck('quantity')->toArray();
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