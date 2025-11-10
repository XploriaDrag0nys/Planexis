<?php

namespace App\Providers;

use App\Models\Table;
use App\Models\TableRow;
use App\Models\User;
use App\Policies\TablePolicy;
use App\Policies\TableRowPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Table::class => TablePolicy::class,
        TableRow::class => TableRowPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::define('manageUsers', function (User $user) {
            return $user->isAdmin() || $user->isProjectManager();
        });
    }
}