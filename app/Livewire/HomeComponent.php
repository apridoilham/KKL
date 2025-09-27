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

    public $totalItems, $totalIn, $totalOut, $totalDamaged, $totalUsers;
    public $categories = [];
    public $quantities = [];
    public $stockTrend = [];
    
    // Palet warna baru untuk grafik
    public $pieChartColors = ['#4A55A2', '#7895CB', '#A0BFE0', '#C5DFF8'];
    public $doughnutChartColors = ['#28a745', '#ffc107', '#dc3545'];

    public $isModalOpen = false;
    public $isModalOpenData = false;
    
    public $checkData, $isVerified;
    public $username, $password, $newPassword, $confPass, $name, $securityQuestion, $securityAnswer;

    public function mount()
    {
        $this->data = [
            'title' => 'Dashboard',
            'urlPath' => 'home'
        ];
        // Hitung total items
        $this->totalItems = Item::count();
        $this->totalUsers = User::count();

        // Hitung total transaksi berdasarkan tipe
        $this->totalIn = Transaction::where('type', 'in')->sum('quantity');
        $this->totalOut = Transaction::where('type', 'out')->sum('quantity');
        $this->totalDamaged = Transaction::where('type', 'damaged')->sum('quantity');

        $this->updateChartData();
    }

    public function updateChartData(){
        $data = Item::select('category', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('category')
            ->get();

        $this->categories = $data->pluck('category')->map(fn($cat) => $cat ?? 'Uncategorized');
        $this->quantities = $data->pluck('total_quantity');
        
        if($this->categories->isEmpty()){
            $this->categories = ['No Data'];
            $this->quantities = [0];
        }

        $this->stockTrend = [
            'in_quantity' => $this->totalIn,
            'out_quantity' => $this->totalOut,
            'damaged_quantity' => $this->totalDamaged,
        ];
    }
    
    public function checkUser(){
        $this->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $this->username)->first();

        if(!$user || !Hash::check($this->password, $user->password)) {
             return session()->flash('dataSession', (object) [
                'status' => 'failed',
                'message' => 'Incorrect username or password'
            ]);
        }
        
        $this->isVerified = true;
        $this->name = $user->name;
        $this->securityQuestion = $user->security_question;
    }

    public function changePassword(){
        $this->validate([
            'newPassword' => 'required|min:6',
            'confPass' => 'required|same:newPassword',
        ]);

        User::where('username','=', $this->username)
            ->update(['password' => bcrypt($this->newPassword)]);

        session()->flash('dataSession', (object) [
            'status' => 'success',
            'message' => 'Password changed successfully'
        ]);
        
        $this->reset(['newPassword', 'confPass', 'password', 'isVerified', 'username']);
        $this->isModalOpen = false;
    }
    
    public function changeData(){
        $this->validate([
            'name' => 'required|string',
            'securityQuestion' => 'required|string',
            'securityAnswer' => 'required|string',
        ]);

        User::where('username','=', auth()->user()->username)
            ->update([
                'name' => $this->name,
                'security_question' => $this->securityQuestion,
                'security_answer' => bcrypt($this->securityAnswer),
            ]);

        session()->flash('dataSession', (object) [
            'status' => 'success',
            'message' => 'Data changed successfully'
        ]);

        $this->reset(['name', 'securityQuestion', 'securityAnswer']);
        $this->isModalOpenData = false;
        // Refresh component data
        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.home')
            ->layout('components.layouts.app',['data' => $this->data]);
    }
}