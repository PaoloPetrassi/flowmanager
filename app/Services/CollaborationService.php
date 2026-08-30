<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\FlowNotification;
use App\Support\FlowResourceRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CollaborationService
{
    public static function dataFor(Model $target): array
    {
        return [
            'comments' => $target->comments()
                ->with('user:id,name')
                ->latest()
                ->limit(20)
                ->get(),
            'attachments' => $target->attachments()
                ->with('user:id,name')
                ->latest()
                ->limit(20)
                ->get(),
            'auditLogs' => auth()->user()?->hasPermission('audit.view')
                ? $target->auditLogs()
                    ->with('user:id,name')
                    ->limit(12)
                    ->get()
                : collect(),
        ];
    }

    public static function notifyCommentAdded(
        Model $target,
        User $author
    ): void {
        $recipients = self::recipientsFor($target)
            ->reject(fn (User $user) => $user->is($author))
            ->unique('id');

        $route = FlowResourceRegistry::routeFor($target);

        if (! $route) {
            return;
        }

        foreach ($recipients as $recipient) {
            $recipient->notify(new FlowNotification(
                kind: 'comment',
                titleKey: 'New comment',
                messageKey: ':user commented on :item.',
                parameters: [
                    'user' => $author->name,
                    'item' => FlowResourceRegistry::labelForModel($target),
                ],
                routeName: $route,
                routeParameters: [$target->getKey()],
                icon: 'bi-chat-left-text',
            ));
        }
    }

    /**
     * @return Collection<int, User>
     */
    private static function recipientsFor(Model $target): Collection
    {
        $userIds = match (true) {
            $target instanceof Project => [$target->manager_id],
            $target instanceof Task => [
                $target->assigned_to,
                $target->project?->manager_id,
            ],
            $target instanceof Asset => [$target->assigned_to],
            $target instanceof Ticket => [$target->assigned_to],
            default => [$target->created_by],
        };

        $userIds = collect($userIds)
            ->filter()
            ->unique()
            ->values();

        return $userIds->isEmpty()
            ? collect()
            : User::query()
                ->whereIn('id', $userIds)
                ->get();
    }
}
