<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Observers\TaskWorkflowObserver;
use App\Observers\TicketWorkflowObserver;
use App\Observers\WorkAssignmentObserver;
use App\Policies\CompanyPolicy;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as IlluminateView;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Company::class, CompanyPolicy::class);

        Project::observe(WorkAssignmentObserver::class);
        Task::observe(WorkAssignmentObserver::class);
        Task::observe(TaskWorkflowObserver::class);
        Asset::observe(WorkAssignmentObserver::class);
        Ticket::observe(WorkAssignmentObserver::class);
        Ticket::observe(TicketWorkflowObserver::class);

        Paginator::useBootstrapFive();

        $slowQueryBudget = (int) config('flowmanager.diagnostics.slow_request_query_ms', 500);

        if (app()->environment('local') && ! app()->runningInConsole() && $slowQueryBudget > 0) {
            DB::whenQueryingForLongerThan(
                $slowQueryBudget,
                function (Connection $connection, QueryExecuted $event) use ($slowQueryBudget): void {
                    Log::warning('FlowManager request exceeded the local database query budget.', [
                        'connection' => $connection->getName(),
                        'budget_ms' => $slowQueryBudget,
                        'last_query_ms' => $event->time,
                        'sql' => $event->sql,
                    ]);
                },
            );
        }

        View::composer('layouts.app', function (IlluminateView $view) {
            static $hasNotificationsTable = null;

            $user = Auth::user();

            if (! $user) {
                return;
            }

            $user->loadMissing('roles.permissions');

            $headerNotifications = collect();
            $unreadNotificationCount = 0;
            $hasNotificationsTable ??= Schema::hasTable('notifications');

            if ($hasNotificationsTable) {
                $headerNotifications = $user->notifications()->latest()->limit(6)->get();
                $unreadNotificationCount = $user->unreadNotifications()->count();
            }

            $view->with([
                'sidebarWorkCounts' => [
                    'tasks' => $user->hasPermission('tasks.view')
                        ? Task::query()->operational()->open()->where('assigned_to', $user->id)->count()
                        : 0,
                    'tickets' => $user->hasPermission('tickets.view')
                        ? Ticket::query()->open()->where('assigned_to', $user->id)->count()
                        : 0,
                ],
                'headerNotifications' => $headerNotifications,
                'unreadNotificationCount' => $unreadNotificationCount,
            ]);
        });
    }
}
