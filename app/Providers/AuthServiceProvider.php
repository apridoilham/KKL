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
        // Mendefinisikan Gate untuk admin
        // Gate ini akan bernilai true jika pengguna yang login memiliki role 'admin'
        Gate::define('is-admin', function (User $user) {
            return $user->role === 'admin';
        });

        // Gate spesifik untuk aksi-aksi yang hanya boleh dilakukan admin
        Gate::define('delete-item', fn(User $user) => $user->role === 'admin');
        Gate::define('delete-transaction', fn(User $user) => $user->role === 'admin');
        Gate::define('manage-users', fn(User $user) => $user->role === 'admin');
    }
}