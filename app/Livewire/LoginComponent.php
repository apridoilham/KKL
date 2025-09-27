<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class LoginComponent extends Component
{
    
    public $data, $checkData;
    public $username, $password,$name, $securityQuestion, $securityAnswer, $newPassword, $confPass;

    public $isModalOpen = false;
    public $isVerified, $isUserFound;
    

    public function mount(){
        
        $this->data = [
            'title' => 'Login Page',
            'urlPath' => 'login'
        ];

        // Cek dulu apakah ada data di tabel users ketika web diload pertama kali
        $this->checkData = User::count();
    }

    // Menggabungkan logika login dan simpan data pertama kali
    public function submit(){
        if($this->checkData > 0){
            $this->loginProcess();
        } else {
            $this->saveData();
        }
    }

    private function saveData(){

        $this->validate([
            'username' => 'required|string|min:3',
            'password' => 'required|string|min:6',
            'name' => 'required|string',
            'securityQuestion' => 'required|string',
            'securityAnswer' => 'required|string',
        ]);

        if($this->checkData != 0){
            session()->flash('dataSession', (object) [
                'status' => 'failed',
                'message' => 'The data is already there. Ensure login with correct username and password'
            ]);
        }else{
            User::create([
                'username' => $this->username,
                'password' => bcrypt($this->password),
                'name' => $this->name,
                'security_question' => $this->securityQuestion,
                'security_answer' => bcrypt($this->securityAnswer),
                'role' => 'admin', // User pertama otomatis menjadi admin
            ]);

            $this->checkData = User::count();
            $this->reset(['username', 'password', 'name', 'securityQuestion', 'securityAnswer']);

            session()->flash('dataSession', (object) [
                'status' => 'success',
                'message' => 'Data is saved successfully. Login to continue'
            ]);
        }
    }

    private function loginProcess(){

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
        
        Auth::login($user); 
        return redirect()->route('home');
    }

    public function verifyData(){
        $this->validate([
            'username' => 'required|string',
        ]);

        $user = User::where('username', $this->username)->first();

        if(!$user)
        return session()->flash('dataSession2', (object) [
            'status' => 'failed',
            'message' => 'Incorrect username or password'
        ]);
        $this->isUserFound = true;
        $this->securityQuestion = $user->security_question;

        if($this->isUserFound && $this->securityAnswer){
            if (Hash::check($this->securityAnswer, $user->security_answer)) {
                $this->isVerified = true;
            } else {
                return session()->flash('dataSession2', (object) [
                    'status' => 'failed',
                    'message' => 'Failed. Security answer is wrong'
                ]);
            }
        }
    }

    public function changePassword(){
        $this->validate([
            'newPassword' => 'required|string',
            'confPass' => 'required|string|same:newPassword',
        ]);

        if($this->newPassword !== $this->confPass)
        return session()->flash('dataSession2', (object) [
            'status' => 'failed',
            'message' => 'Confirmation password is not same'
        ]);

        User::where('username','=', $this->username)
            ->update([
                'password' => bcrypt($this->newPassword)
            ]);

        session()->flash('dataSession2', (object) [
            'status' => 'success',
            'message' => 'Password changed successfully'
        ]);

        $this->reset(['newPassword', 'confPass', 'isVerified', 'isUserFound', 'username', 'securityQuestion', 'securityAnswer']);
    }

    public function render()
    {
        return view('livewire.login')->layout('components.layouts.blank');
    }
}