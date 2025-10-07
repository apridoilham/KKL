<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    ];

    public function boot(): void
    {
        $checkRole = fn($user, $roles) => in_array(strtolower(trim($user->role)), (array)$roles);

        Gate::define('manage-users', fn(User $user) => $checkRole($user, 'admin'));

        Gate::define('manage-items', fn(User $user) => $checkRole($user, 'admin'));

        Gate::define('manage-transactions', fn(User $user) => $checkRole($user, ['admin', 'pengiriman']));
        
        Gate::define('edit-transactions', fn(User $user) => $checkRole($user, 'admin'));

        Gate::define('manage-production', fn(User $user) => $checkRole($user, ['admin', 'produksi']));

        Gate::define('view-pages', fn(User $user) => $checkRole($user, ['admin', 'produksi', 'pengiriman']));

        Gate::define('view-reports', fn(User $user) => $checkRole($user, 'admin'));
    }
}