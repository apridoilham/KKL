<?php

namespace Tests\Feature;

use App\Http\Livewire\UserComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserComponentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('username', 'admin')->firstOrFail();
    }

    #[Test]
    public function admin_can_create_a_new_user(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserComponent::class)
            ->call('create')
            ->set('name', 'User Test Baru')
            ->set('username', 'usertest')
            ->set('role', 'produksi')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('store');

        $this->assertDatabaseHas('users', [
            'username' => 'usertest',
            'role' => 'produksi',
        ]);
    }

    #[Test]
    public function admin_can_update_a_user(): void
    {
        $userToUpdate = User::factory()->create(['name' => 'Nama Lama', 'role' => 'produksi']);

        Livewire::actingAs($this->admin)
            ->test(UserComponent::class)
            ->call('edit', $userToUpdate->id)
            ->set('name', 'Nama Baru Diedit')
            ->set('role', 'pengiriman')
            ->call('store');

        $this->assertDatabaseHas('users', [
            'id' => $userToUpdate->id,
            'name' => 'Nama Baru Diedit',
            'role' => 'pengiriman',
        ]);
    }

    #[Test]
    public function username_must_be_unique(): void
    {
        User::factory()->create(['username' => 'sudahada']);

        Livewire::actingAs($this->admin)
            ->test(UserComponent::class)
            ->call('create')
            ->set('name', 'User Lain')
            ->set('username', 'sudahada')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('store')
            ->assertHasErrors(['username' => 'unique']);
    }

    #[Test]
    public function admin_can_delete_a_user(): void
    {
        $userToDelete = User::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(UserComponent::class)
            ->call('delete', $userToDelete->id);

        $this->assertDatabaseMissing('users', [
            'id' => $userToDelete->id,
        ]);
    }
}