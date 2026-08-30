<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\FlowResourceRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()->hasPermission('audit.view'),
            403
        );

        $search = trim((string) $request->query('search'));
        $event = (string) $request->query('event');
        $userId = $request->integer('user_id') ?: null;
        $resource = (string) $request->query('resource');
        $dateFrom = (string) $request->query('date_from');
        $dateTo = (string) $request->query('date_to');

        $resourceClasses = FlowResourceRegistry::auditResources();

        $logs = AuditLog::query()
            ->with('user:id,name,email')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('auditable_label', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when(
                in_array($event, $this->events(), true),
                fn (Builder $query) => $query->where('event', $event)
            )
            ->when(
                $userId,
                fn (Builder $query) => $query->where('user_id', $userId)
            )
            ->when(
                isset($resourceClasses[$resource]),
                fn (Builder $query) => $query->where(
                    'auditable_type',
                    $resourceClasses[$resource]
                )
            )
            ->when(
                $dateFrom !== '',
                fn (Builder $query) => $query->whereDate('created_at', '>=', $dateFrom)
            )
            ->when(
                $dateTo !== '',
                fn (Builder $query) => $query->whereDate('created_at', '<=', $dateTo)
            )
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('activity.index', [
            'logs' => $logs,
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'events' => $this->events(),
            'resources' => array_keys($resourceClasses),
            'filters' => [
                'search' => $search,
                'event' => $event,
                'user_id' => $userId,
                'resource' => $resource,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    private function events(): array
    {
        return [
            'created',
            'updated',
            'deleted',
            'restored',
            'commented',
            'comment_deleted',
            'attachment_added',
            'attachment_deleted',
            'permanently_deleted',
            'permissions_updated',
            'roles_updated',
            'password_changed',
        ];
    }
}
