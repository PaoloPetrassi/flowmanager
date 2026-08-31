<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskRecurrence;
use App\Enums\TaskStatus;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasCollaboration;
use App\Models\Concerns\HasExtensibleData;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use Auditable, HasCollaboration, HasExtensibleData, HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'parent_id',
        'milestone_id',
        'assigned_to',
        'title',
        'status',
        'priority',
        'recurrence',
        'recurrence_interval',
        'recurrence_ends_at',
        'recurrence_source_id',
        'next_recurrence_id',
        'due_date',
        'estimated_minutes',
        'completed_at',
        'due_reminder_sent_at',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'recurrence' => TaskRecurrence::class,
            'recurrence_interval' => 'integer',
            'recurrence_ends_at' => 'date',
            'due_date' => 'date',
            'estimated_minutes' => 'integer',
            'completed_at' => 'datetime',
            'due_reminder_sent_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recurrenceSource(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'recurrence_source_id');
    }

    public function nextRecurrence(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'next_recurrence_id');
    }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'task_id',
            'depends_on_task_id'
        )->withTimestamps();
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'depends_on_task_id',
            'task_id'
        )->withTimestamps();
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function scopeOperational(Builder $query): Builder
    {
        return $query->whereHas('project', fn (Builder $projectQuery) => $projectQuery->where('is_template', false));
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            TaskStatus::Completed->value,
            TaskStatus::Cancelled->value,
        ]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->open()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today());
    }

    public function trackedMinutes(): int
    {
        if ($this->relationLoaded('timeEntries')) {
            return (int) $this->timeEntries->sum('minutes');
        }

        return (int) $this->timeEntries()->sum('minutes');
    }

    public function hasBlockingDependencies(): bool
    {
        return $this->dependencies()
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])
            ->exists();
    }
}
