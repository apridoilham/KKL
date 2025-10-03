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
        // Fungsi trim() akan menghapus spasi di awal/akhir
        // Fungsi strtolower() akan mengubah semua menjadi huruf kecil
        $checkAdmin = fn(User $user) => strtolower(trim($user->role)) === 'admin';

        Gate::define('is-admin', $checkAdmin);
        Gate::define('delete-item', $checkAdmin);
        Gate::define('delete-transaction', $checkAdmin);
        Gate::define('manage-users', $checkAdmin);
    }
}