<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function production_role_is_redirected_from_dashboard(): void
    {
        // Arrange: Buat user dengan role 'produksi'
        $user = User::factory()->create(['role' => 'produksi']);

        // Act & Assert: Coba akses dashboard ('/') dan pastikan dialihkan ke '/production'
        $this->actingAs($user)->get('/')->assertRedirect('/production');
    }

    #[Test]
    public function production_role_cannot_access_user_management_page(): void
    {
        // Arrange: Buat user dengan role 'produksi'
        $user = User::factory()->create(['role' => 'produksi']);

        // Act & Assert: Coba akses '/user' dan pastikan mendapat error 403 (Forbidden)
        $this->actingAs($user)->get('/user')->assertForbidden();
    }

    #[Test]
    public function pengiriman_role_is_redirected_from_dashboard(): void
    {
        // Arrange: Buat user dengan role 'pengiriman'
        $user = User::factory()->create(['role' => 'pengiriman']);

        // Act & Assert: Coba akses dashboard ('/') dan pastikan dialihkan ke '/transaction'
        $this->actingAs($user)->get('/')->assertRedirect('/transaction');
    }

    #[Test]
    public function pengiriman_role_cannot_access_production_page(): void
    {
        // Arrange: Buat user dengan role 'pengiriman'
        $user = User::factory()->create(['role' => 'pengiriman']);

        // Act & Assert: Coba akses '/production' dan pastikan mendapat error 403 (Forbidden)
        $this->actingAs($user)->get('/production')->assertForbidden();
    }

    #[Test]
    public function admin_role_can_access_all_pages(): void
    {
        // Arrange: Cari user admin yang dibuat oleh migrasi
        $admin = User::where('username', 'admin')->firstOrFail();

        // Act & Assert: Pastikan admin bisa mengakses semua halaman utama
        $this->actingAs($admin)->get('/')->assertOk();
        $this->actingAs($admin)->get('/item')->assertOk();
        $this->actingAs($admin)->get('/transaction')->assertOk();
        $this->actingAs($admin)->get('/production')->assertOk();
        $this->actingAs($admin)->get('/user')->assertOk();
        $this->actingAs($admin)->get('/report')->assertOk();
    }
}