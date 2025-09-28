<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class HomeComponent extends Component
{
    public $data;

    // Data Statistik Utama
    public $totalItems, $totalIn, $totalOut, $totalDamaged, $totalUsers, $totalStock;

    // Data Grafik
    public $categoryLabels = [], $categoryData = [];
    public $trendLabels = ['Masuk', 'Keluar', 'Rusak'], $trendData = [];
    public $topStockLabels = [], $topStockData = [];
    public $chartPalette1 = ['#4A55A2', '#7895CB', '#A0BFE0', '#C5DFF8', '#F0F0F0'];
    
    // Properti untuk Modal
    public $isModalOpen = false;
    public $isModalOpenData = false;
    
    // Properti untuk Form
    public $name, $username;
    public $password, $newPassword, $confPass;
    public $confirmationPassword = '';

    public function mount()
    {
        $this->data = [
            'title' => 'Dashboard',
            'urlPath' => 'home'
        ];

        $this->updateStatistics();
        $this->updateChartData();

        $user = auth()->user();
        $this->name = $user->name;
        $this->username = $user->username;
    }

    public function updateStatistics()
    {
        $this->totalItems = Item::count();
        $this->totalUsers = User::count();
        $this->totalStock = Item::sum('quantity');
        $this->totalIn = Transaction::where('type', 'in')->sum('quantity');
        $this->totalOut = Transaction::where('type', 'out')->sum('quantity');
        $this->totalDamaged = Transaction::where('type', 'damaged')->sum('quantity');
    }

    public function updateChartData()
    {
        $categoryInfo = Item::select('category', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('category')
            ->get();
        $this->categoryLabels = $categoryInfo->pluck('category')->map(fn($cat) => $cat ?? 'Tanpa Kategori')->toArray();
        $this->categoryData = $categoryInfo->pluck('total_quantity')->toArray();
        if (empty($this->categoryLabels)) {
            $this->categoryLabels = ['Belum Ada Data'];
            $this->categoryData = [0];
        }

        $this->trendData = [$this->totalIn, $this->totalOut, $this->totalDamaged];
        if (array_sum($this->trendData) == 0) {
            $this->trendData = [0, 0, 0];
        }
        
        $topItems = Item::orderBy('quantity', 'desc')->take(5)->get();
        $this->topStockLabels = $topItems->pluck('name')->toArray();
        $this->topStockData = $topItems->pluck('quantity')->toArray();
    }
    
    public function changePassword()
    {
        $this->validate([
            'password' => 'required',
            'newPassword' => 'required|min:6',
            'confPass' => 'required|same:newPassword',
        ]);
        
        $user = auth()->user();

        if (!Hash::check($this->password, $user->password)) {
            session()->flash('dataSession', ['status' => 'failed', 'message' => 'Password saat ini salah.']);
            return;
        }

        $user->update(['password' => Hash::make($this->newPassword)]);

        session()->flash('dataSession', ['status' => 'success', 'message' => 'Password berhasil diperbarui.']);
        
        $this->reset(['password', 'newPassword', 'confPass']);
        $this->isModalOpen = false;
    }
    
    public function changeData()
    {
        $this->validate([
            'name' => 'required|string',
            'confirmationPassword' => 'required|string',
        ]);
        
        $user = auth()->user();
        
        if (!Hash::check($this->confirmationPassword, $user->password)) {
            session()->flash('dataSession', ['status' => 'failed', 'message' => 'Password konfirmasi yang Anda masukkan salah.']);
            $this->addError('confirmationPassword', 'Password salah.');
            return;
        }

        $user->update(['name' => $this->name]);

        session()->flash('dataSession', ['status' => 'success', 'message' => 'Nama Anda berhasil diperbarui.']);
        
        $this->isModalOpenData = false;
        $this->reset('confirmationPassword');
    }

    public function render()
    {
        return view('livewire.home')
            ->layout('components.layouts.app',['data' => $this->data]);
    }
}