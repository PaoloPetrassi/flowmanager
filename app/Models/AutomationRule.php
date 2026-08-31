<?php

namespace App\Models;

use App\Enums\AutomationAction;
use App\Enums\AutomationTrigger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'trigger',
        'action',
        'conditions',
        'action_config',
        'is_active',
        'last_run_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'trigger' => AutomationTrigger::class,
            'action' => AutomationAction::class,
            'conditions' => 'array',
            'action_config' => 'array',
            'is_active' => 'boolean',
            'last_run_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AutomationRun::class);
    }
}
