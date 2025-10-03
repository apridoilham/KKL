<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class LoginComponent extends Component
{
    public $data, $checkData;
    public $username, $password, $name, $securityQuestion, $securityAnswer, $newPassword, $confPass;
    public $isModalOpen = false;
    public $isVerified, $isUserFound;

    public function mount()
    {
        $this->data = ['title' => 'Login Page', 'urlPath' => 'login'];
        $this->checkData = User::count();
    }

    public function submit()
    {
        if ($this->checkData > 0) {
            $this->loginProcess();
        } else {
            $this->saveData();
        }
    }

    private function saveData()
    {
        $this->validate([
            'username' => 'required|string|min:3|unique:users,username',
            'password' => 'required|string|min:6',
            'name' => 'required|string',
            'securityQuestion' => 'required|string',
            'securityAnswer' => 'required|string',
        ]);
        User::create([
            'username' => $this->username,
            'password' => Hash::make($this->password),
            'name' => $this->name,
            'security_question' => $this->securityQuestion,
            'security_answer' => Hash::make($this->securityAnswer),
            'role' => 'admin',
        ]);
        $this->checkData = User::count();
        $this->reset(['username', 'password', 'name', 'securityQuestion', 'securityAnswer']);
        session()->flash('dataSession', ['status' => 'success', 'message' => 'Akun Admin berhasil dibuat. Silakan login.']);
    }

    private function loginProcess()
    {
        $this->validate(['username' => 'required|string', 'password' => 'required|string']);
        $user = User::where('username', $this->username)->first();
        if (!$user || !Hash::check($this->password, $user->password)) {
            $this->addError('username', 'Username atau password salah.');
            return;
        }
        Auth::login($user);
        return redirect()->route('home');
    }

    public function verifyData()
    {
        $this->validate(['username' => 'required|string']);
        $user = User::where('username', $this->username)->first();
        if (!$user) {
            session()->flash('dataSession2', ['status' => 'failed', 'message' => 'Username tidak ditemukan.']);
            return;
        }
        $this->isUserFound = true;
        $this->securityQuestion = $user->security_question;
        if ($this->isUserFound && $this->securityAnswer) {
            if (Hash::check($this->securityAnswer, $user->security_answer)) {
                $this->isVerified = true;
            } else {
                session()->flash('dataSession2', ['status' => 'failed', 'message' => 'Jawaban keamanan salah.']);
            }
        }
    }

    public function changePassword()
    {
        $this->validate([
            'newPassword' => 'required|string|min:6',
            'confPass' => 'required|string|same:newPassword',
        ]);
        User::where('username', '=', $this->username)->update(['password' => Hash::make($this->newPassword)]);
        session()->flash('dataSession2', ['status' => 'success', 'message' => 'Password berhasil diubah.']);
        $this->reset(['newPassword', 'confPass', 'isVerified', 'isUserFound', 'username', 'securityQuestion', 'securityAnswer']);
        $this->isModalOpen = false;
    }

    public function render()
    {
        return view('livewire.login')->layout('components.layouts.blank');
    }
}