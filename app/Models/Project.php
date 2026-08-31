<?php

namespace App\Models;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasCollaboration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use Auditable, HasCollaboration, HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'contact_id',
        'manager_id',
        'code',
        'name',
        'status',
        'priority',
        'is_template',
        'progress_override',
        'start_date',
        'due_date',
        'budget',
        'estimated_minutes',
        'description',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'priority' => ProjectPriority::class,
            'is_template' => 'boolean',
            'progress_override' => 'integer',
            'start_date' => 'date',
            'due_date' => 'date',
            'budget' => 'decimal:2',
            'estimated_minutes' => 'integer',
        ];
    }


    public function scopeOperational(Builder $query): Builder
    {
        return $query->where('is_template', false);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    public function timeEntries(): HasManyThrough
    {
        return $this->hasManyThrough(TimeEntry::class, Task::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function progressPercentage(): int
    {
        if ($this->progress_override !== null) {
            return max(0, min(100, (int) $this->progress_override));
        }

        if (isset($this->attributes['tasks_count'])) {
            $total = (int) $this->attributes['tasks_count'];
            $completed = (int) ($this->attributes['completed_tasks_count'] ?? 0);

            return $total > 0 ? (int) round(($completed / $total) * 100) : 0;
        }

        $tasks = $this->relationLoaded('tasks')
            ? $this->tasks
            : $this->tasks()->get(['id', 'status']);

        if ($tasks->isEmpty()) {
            return 0;
        }

        $completed = $tasks->filter(
            fn (Task $task) => $task->status === \App\Enums\TaskStatus::Completed
        )->count();

        return (int) round(($completed / $tasks->count()) * 100);
    }

    public function trackedMinutes(): int
    {
        if ($this->relationLoaded('timeEntries')) {
            return (int) $this->timeEntries->sum('minutes');
        }

        return (int) $this->timeEntries()->sum('minutes');
    }
}
