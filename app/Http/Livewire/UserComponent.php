<?php

namespace App\Http\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class UserComponent extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind-custom';
    public array $data;
    public string $search = '';
    public int $perPage = 10;
    public ?int $userId = null;
    public string $name = '', $username = '', $role = '';
    public ?string $password = null, $password_confirmation = null;
    public ?string $security_question = null, $security_answer = null;
    public bool $isModalOpen = false;
    public bool $isEditMode = false;

    public string $filterRole = 'all';

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($this->userId)],
            'role' => 'required|in:admin,produksi,pengiriman',
            'security_question' => 'nullable|string|max:255',
        ];
        
        $rules['security_answer'] = $this->security_question ? 'required|string|max:255' : 'nullable|string|max:255';
        
        if (!$this->isEditMode) {
            $rules['password'] = 'required|string|min:6|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:6|confirmed';
        }
        return $rules;
    }

    public function mount(): void
    {
        Gate::authorize('manage-users');
        $this->data = ['title' => 'Manajemen Pengguna', 'urlPath' => 'user'];
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'filterRole'])) {
            $this->resetPage();
        }
    }

    public function resetInputFields(): void
    {
        $this->reset(['userId', 'name', 'username', 'role', 'password', 'password_confirmation', 'security_question', 'security_answer', 'isModalOpen', 'isEditMode']);
    }

    public function create(): void
    {
        Gate::authorize('manage-users');
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    public function edit(int $id): void
    {
        Gate::authorize('manage-users');
        $user = User::findOrFail($id);
        $this->userId = $id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->role = $user->role;
        $this->security_question = $user->security_question;
        $this->security_answer = null; 
        $this->isEditMode = true;
        $this->isModalOpen = true;
    }

    public function store(): void
    {
        Gate::authorize('manage-users');
        $this->validate();

        $userData = [
            'name' => $this->name,
            'username' => $this->username,
            'role' => $this->role,
        ];

        if (!empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        if (!empty($this->security_question) && !empty($this->security_answer)) {
            $userData['security_question'] = $this->security_question;
            $userData['security_answer'] = Hash::make($this->security_answer);
        } elseif ($this->isEditMode && empty($this->security_question)) {
            // Jika pertanyaan keamanan dikosongkan saat edit, hapus juga jawabannya
            $userData['security_question'] = null;
            $userData['security_answer'] = null;
        }

        User::updateOrCreate(['id' => $this->userId], $userData);

        $this->dispatch(
            'toast',
            status: 'success',
            message: $this->userId ? 'Data Pengguna berhasil diperbarui.' : 'Pengguna baru berhasil dibuat.'
        );
        $this->resetInputFields();
    }

    public function delete(int $id): void
    {
        Gate::authorize('manage-users');
        if ($id == auth()->id()) {
            $this->dispatch('toast', status: 'failed', message: 'Anda tidak dapat menghapus akun Anda sendiri.');
            return;
        }
        User::findOrFail($id)->delete();
        $this->dispatch('toast', status: 'success', message: 'Pengguna berhasil dihapus.');
    }

    public function render()
    {
        $usersQuery = User::query()
            ->where(fn($query) => $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('username', 'like', '%' . $this->search . '%'));

        $usersQuery->when($this->filterRole !== 'all', function ($query) {
            return $query->where('role', $this->filterRole);
        });

        $users = $usersQuery->latest()->paginate($this->perPage);

        return view('livewire.user', ['users' => $users])->layout('components.layouts.app', ['data' => $this->data]);
    }
}