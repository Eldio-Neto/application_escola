<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gates para autorização
        Gate::define('manage-courses', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('manage-payments', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('manage-enrollments', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('view-admin-dashboard', function ($user) {
            return $user->isAdmin();
        });
    }
}
