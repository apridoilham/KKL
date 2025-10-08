<?php

namespace Tests\Feature;

use App\Http\Livewire\ProfileComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileComponentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_update_their_own_profile_data(): void
    {
        // Arrange: Buat user biasa
        $user = User::factory()->create(['password' => Hash::make('password-lama')]);

        // Act: Login sebagai user tersebut dan coba ubah data profil
        Livewire::actingAs($user)
            ->test(ProfileComponent::class)
            ->set('name', 'Nama Baru')
            ->set('username', 'usernamebaru')
            ->set('confirmation_password', 'password-lama') // Konfirmasi dengan password yang benar
            ->call('changeData');

        // Assert: Pastikan data di database sudah berubah
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'username' => 'usernamebaru',
        ]);
    }

    #[Test]
    public function user_can_change_their_own_password(): void
    {
        // Arrange: Buat user biasa
        $user = User::factory()->create(['password' => Hash::make('password-lama')]);

        // Act: Login sebagai user tersebut dan coba ubah password
        Livewire::actingAs($user)
            ->test(ProfileComponent::class)
            ->set('current_password', 'password-lama')
            ->set('new_password', 'password-baru-123')
            ->set('new_password_confirmation', 'password-baru-123')
            ->call('changePassword');

        // Assert: Pastikan password di database sudah berubah
        // Kita cek dengan mengambil data user terbaru dan membandingkan hash password-nya
        $this->assertTrue(Hash::check('password-baru-123', $user->fresh()->password));
    }

    #[Test]
    public function changing_data_fails_with_wrong_confirmation_password(): void
    {
        // Arrange: Buat user biasa
        $user = User::factory()->create([
            'name' => 'Nama Lama',
            'password' => Hash::make('password-benar'),
        ]);

        // Act & Assert: Coba ubah data dengan password konfirmasi yang salah
        Livewire::actingAs($user)
            ->test(ProfileComponent::class)
            ->set('name', 'Nama Gagal Diubah')
            ->set('confirmation_password', 'password-salah') // Password konfirmasi salah
            ->call('changeData')
            ->assertHasErrors('confirmation_password');

        // Assert: Pastikan nama tidak berubah di database
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nama Lama']);
    }
}