<?php

namespace App\Models;

use App\Support\FlowResourceRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'auditable_label',
        'event',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjectUrl(): ?string
    {
        return FlowResourceRegistry::urlForAudit($this);
    }

    public function resourceLabel(): string
    {
        return FlowResourceRegistry::labelForClass($this->auditable_type);
    }

    public function fieldLabel(string $field): string
    {
        $validationKey = 'validation.attributes.'.$field;
        $translated = __($validationKey);

        if ($translated !== $validationKey) {
            return Str::ucfirst($translated);
        }

        return __(Str::headline($field));
    }
}
