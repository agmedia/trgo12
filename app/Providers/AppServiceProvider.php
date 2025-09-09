<?php

namespace App\Providers;

use App\Models\Back\User\UserDetail;
use App\Policies\UserDetailPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    protected $policies = [
        UserDetail::class => UserDetailPolicy::class,
    ];

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
        //
        Gate::before(function ($user, $ability) {
            return $user->hasRole('master') ? true : null;
        });
    }
}
