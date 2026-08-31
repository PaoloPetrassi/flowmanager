<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRun extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'automation_rule_id',
        'status',
        'subject_type',
        'subject_id',
        'message',
        'context',
        'ran_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'ran_at' => 'datetime',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'automation_rule_id');
    }
}
