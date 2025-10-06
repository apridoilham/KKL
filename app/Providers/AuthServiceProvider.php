<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Fungsi helper untuk membersihkan dan mengecek role
        $checkRole = fn($user, $roles) => in_array(strtolower(trim($user->role)), (array)$roles);

        // Hanya Admin yang bisa mengelola pengguna
        Gate::define('manage-users', fn(User $user) => $checkRole($user, 'admin'));

        // Hanya Admin yang bisa mengelola data master barang (tambah, ubah, hapus)
        Gate::define('manage-items', fn(User $user) => $checkRole($user, 'admin'));

        // Admin DAN Staff Pengiriman yang bisa mengelola transaksi manual
        Gate::define('manage-transactions', fn(User $user) => $checkRole($user, ['admin', 'pengiriman']));

        // Admin DAN Staff Produksi yang bisa mengelola produksi
        Gate::define('manage-production', fn(User $user) => $checkRole($user, ['admin', 'produksi']));

        // Semua role bisa melihat halaman-halaman dasar
        Gate::define('view-pages', fn(User $user) => $checkRole($user, ['admin', 'produksi', 'pengiriman']));
    }
}