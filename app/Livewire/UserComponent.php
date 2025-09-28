<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserComponent extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind-custom';

    public $data;
    public $search = '';
    public $perPage = 10;
    
    public $userId, $name, $username, $role, $password, $password_confirmation;

    public $isModalOpen = false;
    public $isEditMode = false;

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $this->userId,
            'role' => 'required|in:admin,staff',
        ];

        if (!$this->isEditMode) {
            $rules['password'] = 'required|string|min:6|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:6|confirmed';
        }

        return $rules;
    }

    public function mount()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized Action');
        }

        $this->data = [
            'title' => 'Manajemen Pengguna',
            'urlPath' => 'user'
        ];
    }

    public function resetInputFields()
    {
        $this->reset(['userId', 'name', 'username', 'role', 'password', 'password_confirmation', 'isModalOpen', 'isEditMode']);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->role = $user->role;
        
        $this->isEditMode = true;
        $this->isModalOpen = true;
    }

    public function store()
    {
        $this->validate();

        $userData = [
            'name' => $this->name,
            'username' => $this->username,
            'role' => $this->role,
        ];

        if (!empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        User::updateOrCreate(['id' => $this->userId], $userData);

        session()->flash('dataSession', [
            'status' => 'success',
            'message' => $this->userId ? 'Data Pengguna berhasil diperbarui.' : 'Pengguna baru berhasil dibuat.'
        ]);

        $this->resetInputFields();
    }

    public function delete($id)
    {
        if ($id == auth()->id()) {
            session()->flash('dataSession', ['status' => 'failed', 'message' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
            return;
        }

        User::findOrFail($id)->delete();
        session()->flash('dataSession', ['status' => 'success', 'message' => 'Pengguna berhasil dihapus.']);
    }

    public function render()
    {
        $users = User::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('username', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.user', [
            'users' => $users
        ])->layout('components.layouts.app', ['data' => $this->data]);
    }
}