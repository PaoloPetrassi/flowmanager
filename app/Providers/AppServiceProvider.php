<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Task;
use App\Models\Ticket;
use App\Policies\CompanyPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as IlluminateView;

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
        Gate::policy(
            Company::class,
            CompanyPolicy::class
        );

        Paginator::useBootstrapFive();

        View::composer('layouts.app', function (IlluminateView $view) {
            $user = Auth::user();

            if (! $user) {
                return;
            }

            $user->loadMissing('roles.permissions');

            $view->with('sidebarWorkCounts', [
                'tasks' => $user->hasPermission('tasks.view')
                    ? Task::query()
                        ->open()
                        ->where('assigned_to', $user->id)
                        ->count()
                    : 0,
                'tickets' => $user->hasPermission('tickets.view')
                    ? Ticket::query()
                        ->open()
                        ->where('assigned_to', $user->id)
                        ->count()
                    : 0,
            ]);
        });
    }
}
