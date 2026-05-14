<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Club;
use App\Models\Event;
use App\Models\Task;
use App\Policies\ClubPolicy;
use App\Policies\EventPolicy;
use App\Policies\TaskPolicy;

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
        public function boot(): void{
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if ($user->is_admin) {
                return true;
            }
        });

        \Illuminate\Support\Facades\Gate::define('is-leader', function ($user) {
            return \App\Models\Club::where('leader_id', $user->id)->exists();
        });
    }
}