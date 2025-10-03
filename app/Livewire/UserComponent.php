<?php

namespace App\Livewire; // <-- PERUBAHAN UTAMA DI SINI

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserComponent extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind-custom';

    // Properti untuk data halaman
    public array $data;

    // Properti fungsionalitas tabel
    public string $search = '';
    public int $perPage = 10;
    
    // Properti form binding
    public ?int $userId = null;
    public string $name = '', $username = '', $role = '';
    public ?string $password = null, $password_confirmation = null;

    // Properti state management
    public bool $isModalOpen = false;
    public bool $isEditMode = false;

    /**
     * Dijalankan saat komponen pertama kali dimuat.
     */
    public function mount(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized Action');
        }

        $this->data = [
            'title' => 'Manajemen Pengguna',
            'urlPath' => 'user'
        ];
    }

    /**
     * Membersihkan semua field input dan state modal.
     */
    public function resetInputFields(): void
    {
        $this->reset(['userId', 'name', 'username', 'role', 'password', 'password_confirmation', 'isModalOpen', 'isEditMode']);
    }

    /**
     * Menyiapkan modal untuk membuat pengguna baru.
     */
    public function create(): void
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    /**
     * Mengisi form dengan data pengguna yang akan diedit.
     */
    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->userId = $id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->role = $user->role;
        
        $this->isEditMode = true;
        $this->isModalOpen = true;
    }

    /**
     * Menyimpan data pengguna baru atau memperbarui yang sudah ada.
     */
    public function store(StoreUserRequest $request): void
    {
        $validatedData = $request->validated();

        $userData = [
            'name' => $validatedData['name'],
            'username' => $validatedData['username'],
            'role' => $validatedData['role'],
        ];

        if (!empty($validatedData['password'])) {
            $userData['password'] = Hash::make($validatedData['password']);
        }

        User::updateOrCreate(['id' => $this->userId], $userData);

        $this->dispatch('toast', [
            'status' => 'success',
            'message' => $this->userId ? 'Data Pengguna berhasil diperbarui.' : 'Pengguna baru berhasil dibuat.'
        ]);

        $this->resetInputFields();
    }

    /**
     * Menghapus pengguna.
     */
    public function delete(int $id): void
    {
        if ($id == auth()->id()) {
            $this->dispatch('toast', ['status' => 'failed', 'message' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
            return;
        }

        User::findOrFail($id)->delete();
        $this->dispatch('toast', ['status' => 'success', 'message' => 'Pengguna berhasil dihapus.']);
    }

    /**
     * Merender view komponen.
     */
    public function render()
    {
        $users = User::query()
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.user', [
            'users' => $users
        ])->layout('components.layouts.app', ['data' => $this->data]);
    }
}