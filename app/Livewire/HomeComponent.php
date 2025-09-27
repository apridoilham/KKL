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

    public $totalItems, $totalIn, $totalOut, $totalDamaged;
    public $categories = [];
    public $quantities = [];
    public $stockTrend = [];

    public $isModalOpen = false;
    public $isModalOpenData = false;
    
    public $checkData, $isVerified;
    public $username, $password, $newPassword, $confPass, $name, $securityQuestion, $securityAnswer;


    /* 
        mount akan diload ketika pertama kali dijalankan sebelum view ditampilkan
    */
    public function mount()
    {
        $this->data = [
            'title' => 'Home Page',
            'urlPath' => 'home'
        ];
        // Hitung total items
        $this->totalItems = Item::count();

        // Hitung total transaksi berdasarkan tipe
        $this->totalIn = Transaction::where('type', 'in')->sum('quantity');
        $this->totalOut = Transaction::where('type', 'out')->sum('quantity');
        $this->totalDamaged = Transaction::where('type', 'damaged')->sum('quantity');

        $this->updateChartData();
    
    }


    public function checkUser(){
        $this->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cek apakah user ada
        $user = User::where('username', $this->username)->first();

        if(!$user)
        return session()->flash('dataSession', (object) [
            'status' => 'failed',
            'message' => 'Incorrect username or password'
        ]);
        
        if (Hash::check($this->password, $user->password)) {
            $this->isVerified = true;
            $this->name = $user->name;
            $this->securityQuestion = $user->security_question;
        } else {
            return session()->flash('dataSession', (object) [
                'status' => 'failed',
                'message' => 'Incorrect username or password'
            ]);
        }
    }


    

    public function updateChartData(){
        // Ambil data stok per kategori
        $data = Item::select('category', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('category')
            ->get();

        //pluck itu untuk mendapatkan data sesuai yang kita nginkan, dan menjadikan array baru
        // Simpan data kategori dan jumlah stok ke variabel
        $this->categories = $data->pluck('category'); // Ambil nama kategori
        $this->quantities = $data->pluck('total_quantity'); // Ambil jumlah stok


        // Ambil data stok berdasarkan waktu dengan grouping berdasarkan bulan/tanggal
        $data2 = [
            'in_quantity' => Transaction::where('type', 'in')->sum('quantity'),
            'out_quantity' => Transaction::where('type', 'out')->sum('quantity'),
            'damaged_quantity' => Transaction::where('type', 'damaged')->sum('quantity'),
        ];
        
        $this->stockTrend = (object) $data2;
    }

    

    public function changePassword(){
        $this->validate([
            'newPassword' => 'required|string',
            'confPass' => 'required|string',
        ]);

        if($this->newPassword !== $this->confPass)
        return session()->flash('dataSession', (object) [
            'status' => 'failed',
            'message' => 'Confirmation password is not same '
        ]);

        User::where('username','=', $this->username)
            ->update([
                'password' => bcrypt($this->newPassword)
            ]);

        session()->flash('dataSession', (object) [
            'status' => 'success',
            'message' => 'Password changed successfully'
        ]);

        $this->newPassword = '';
        $this->confPass = '';
    }
    

    public function changeData(){
        $this->validate([
            'name' => 'required|string',
            'securityQuestion' => 'required|string',
            'securityAnswer' => 'required|string',
        ]);

        User::where('username','=', $this->username)
            ->update([
                'name' => $this->name,
                'security_question' => $this->securityQuestion,
                'security_answer' => bcrypt($this->securityAnswer),
            ]);

        session()->flash('dataSession', (object) [
            'status' => 'success',
            'message' => 'Data changed successfully'
        ]);

        $this->name = '';
        $this->securityQuestion = '';
        $this->securityAnswer = '';
    }
    public function render()
    {
        return view('livewire.home')
            ->layout('components.layouts.app',['data' => $this->data]);
    }
}