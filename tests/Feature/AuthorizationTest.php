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
        $user = User::factory()->create(['role' => 'produksi']);

        $this->actingAs($user)->get('/')->assertRedirect('/production');
    }

    #[Test]
    public function production_role_cannot_access_user_management_page(): void
    {
        $user = User::factory()->create(['role' => 'produksi']);

        $this->actingAs($user)->get('/user')->assertForbidden();
    }

    #[Test]
    public function pengiriman_role_is_redirected_from_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'pengiriman']);

        $this->actingAs($user)->get('/')->assertRedirect('/transaction');
    }

    #[Test]
    public function pengiriman_role_cannot_access_production_page(): void
    {
        $user = User::factory()->create(['role' => 'pengiriman']);

        $this->actingAs($user)->get('/production')->assertForbidden();
    }

    #[Test]
    public function admin_role_can_access_all_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/')->assertOk();
        $this->actingAs($admin)->get('/item')->assertOk();
        $this->actingAs($admin)->get('/transaction')->assertOk();
        $this->actingAs($admin)->get('/production')->assertOk();
        $this->actingAs($admin)->get('/user')->assertOk();
        $this->actingAs($admin)->get('/report')->assertOk();
    }
}