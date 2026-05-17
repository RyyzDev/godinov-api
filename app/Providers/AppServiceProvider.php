<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Mendefinisikan Gate 'is-admin'
        Gate::define('is-admin', function (User $user) {
            return $user->role === 'admin';
        });

        // Mendefinisikan Gate 'is-pm'
        Gate::define('is-pm', function (User $user) {
            return $user->role === 'pm' || $user->role === 'admin';
        });

        // Mendefinisikan Gate 'is-cfo'
        Gate::define('is-cfo', function (User $user) {
            return $user->role === 'cfo' || $user->role === 'admin';
        });

        // Mendefinisikan Gate 'is-super-sales' - dapat manage sales users
        Gate::define('is-super-sales', function (User $user) {
            return $user->role === 'super_sales' || $user->role === 'admin';
        });

        // Mendefinisikan Gate 'is-sales'
        Gate::define('is-sales', function (User $user) {
            return $user->role === 'sales' || $user->role === 'super_sales' || $user->role === 'admin';
        });
    }
}