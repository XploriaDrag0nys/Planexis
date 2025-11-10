<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\Models\Table;
use App\Models\TableRow;
use App\Policies\TablePolicy;
use App\Policies\TableRowPolicy;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Les mappings Model → Policy
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Table::class => TablePolicy::class,
        TableRow::class => TableRowPolicy::class,
        User::class => \App\Policies\UserPolicy::class,
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
        $this->registerPolicies();
        Schema::defaultStringLength(191);
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip());
        });
        View::composer('users.index', function ($view) {
            $view->with('forced', Setting::get('2fa_forced', '0') === '1');
        });
    }
}
