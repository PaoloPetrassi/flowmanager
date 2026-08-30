<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model): void {
            AuditService::recordCreated($model);
        });

        static::updated(function ($model): void {
            AuditService::recordUpdated($model);
        });

        static::deleted(function ($model): void {
            $event = method_exists($model, 'isForceDeleting')
                && $model->isForceDeleting()
                    ? 'permanently_deleted'
                    : 'deleted';

            AuditService::record($model, $event);
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restored(function ($model): void {
                AuditService::record($model, 'restored');
            });
        }
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(
            AuditLog::class,
            'auditable'
        )->latest('created_at');
    }
}
