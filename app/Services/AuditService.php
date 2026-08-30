<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Support\FlowResourceRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    private const IGNORED_FIELDS = [
        'created_at',
        'updated_at',
        'deleted_at',
        'password',
        'remember_token',
    ];

    public static function recordCreated(Model $model): void
    {
        self::record(
            $model,
            'created',
            [],
            self::sanitize($model->getAttributes())
        );
    }

    public static function recordUpdated(Model $model): void
    {
        $changes = self::sanitize($model->getChanges());

        if ($changes === []) {
            return;
        }

        $oldValues = [];

        foreach (array_keys($changes) as $field) {
            $oldValues[$field] = $model->getRawOriginal($field);
        }

        self::record(
            $model,
            'updated',
            $oldValues,
            $changes
        );
    }

    public static function record(
        Model $model,
        string $event,
        array $oldValues = [],
        array $newValues = []
    ): void {
        if (! Auth::check()) {
            return;
        }

        $request = app()->bound('request')
            ? request()
            : null;

        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'auditable_label' => FlowResourceRegistry::labelForModel($model),
            'event' => $event,
            'old_values' => $oldValues === [] ? null : $oldValues,
            'new_values' => $newValues === [] ? null : $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request
                ? mb_substr((string) $request->userAgent(), 0, 500)
                : null,
        ]);
    }

    private static function sanitize(array $values): array
    {
        return Arr::except(
            $values,
            self::IGNORED_FIELDS
        );
    }
}
