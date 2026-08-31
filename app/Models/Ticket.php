<?php

namespace App\Models;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasCollaboration;
use App\Models\Concerns\HasExtensibleData;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use Auditable, HasCollaboration, HasExtensibleData, HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'contact_id',
        'assigned_to',
        'reference',
        'subject',
        'category',
        'status',
        'priority',
        'sla_due_at',
        'sla_breached_at',
        'sla_reminder_sent_at',
        'first_response_at',
        'description',
        'resolution',
        'resolved_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'category' => TicketCategory::class,
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'sla_due_at' => 'datetime',
            'sla_breached_at' => 'datetime',
            'sla_reminder_sent_at' => 'datetime',
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            TicketStatus::Resolved->value,
            TicketStatus::Closed->value,
        ]);
    }

    public function isSlaBreached(): bool
    {
        return $this->sla_breached_at !== null
            || ($this->sla_due_at !== null && $this->sla_due_at->isPast() && $this->resolved_at === null);
    }
}
