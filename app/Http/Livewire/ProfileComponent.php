<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ProfileComponent extends Component
{
    public array $data;
    public string $name = '', $username = '';

    // Properti untuk ubah password
    public ?string $current_password = null;
    public ?string $new_password = null;
    public ?string $new_password_confirmation = null;

    // Properti untuk ubah data
    public string $confirmation_password = '';

    // Properti untuk ubah pertanyaan keamanan
    public ?string $security_question = null;
    public ?string $security_answer = null;
    public string $security_confirmation_password = '';


    public function mount(): void
    {
        $this->data = ['title' => 'Edit Profil', 'urlPath' => 'profile'];
        $user = auth()->user();
        $this->name = $user->name;
        $this->username = $user->username;
        $this->security_question = $user->security_question;
    }

    public function changePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Password saat ini yang Anda masukkan salah.');
            return;
        }

        $user->update([
            'password' => Hash::make($this->new_password)
        ]);

        $this->dispatch('toast', status: 'success', message: 'Password berhasil diperbarui.');
        $this->reset('current_password', 'new_password', 'new_password_confirmation');
    }

    public function changeData(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . auth()->id(),
            'confirmation_password' => 'required|string',
        ]);

        $user = auth()->user();

        if (!Hash::check($this->confirmation_password, $user->password)) {
            $this->addError('confirmation_password', 'Password konfirmasi yang Anda masukkan salah.');
            return;
        }

        $user->update([
            'name' => $this->name,
            'username' => $this->username,
        ]);

        $this->dispatch('toast', status: 'success', message: 'Data diri berhasil diperbarui.');
        $this->reset('confirmation_password');
    }

    public function changeSecurity(): void
    {
        $this->validate([
            'security_question' => 'required|string|max:255',
            'security_answer' => 'required|string|max:255',
            'security_confirmation_password' => 'required|string',
        ]);

        $user = auth()->user();

        if (!Hash::check($this->security_confirmation_password, $user->password)) {
            $this->addError('security_confirmation_password', 'Password konfirmasi yang Anda masukkan salah.');
            return;
        }

        $user->update([
            'security_question' => $this->security_question,
            'security_answer' => Hash::make($this->security_answer),
        ]);

        $this->dispatch('toast', status: 'success', message: 'Pertanyaan keamanan berhasil diperbarui.');
        $this->reset('security_answer', 'security_confirmation_password');
    }


    public function render()
    {
        return view('livewire.profile')->layout('components.layouts.app', ['data' => $this->data]);
    }
}