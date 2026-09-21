<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Role;

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
        Gate::define('admin-access', function ($user) {
            return $user->roles()->where('name', 'Admin Aide')->exists();
        });

        Gate::define('supply-access', function ($user) {
            return $user->roles()->where('name', 'Supply Office')->exists();
        });

        Gate::define('inspector-access', function ($user) {
            return $user->roles()->where('name', 'Inspector')->exists();
        });
    }
}
