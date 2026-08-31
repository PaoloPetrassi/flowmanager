<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskRecurrence;
use App\Enums\TaskStatus;
use App\Models\Milestone;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Task::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title')),
            'project_id' => $this->filled('project_id') ? $this->input('project_id') : null,
            'parent_id' => $this->filled('parent_id') ? $this->input('parent_id') : null,
            'milestone_id' => $this->filled('milestone_id') ? $this->input('milestone_id') : null,
            'assigned_to' => $this->filled('assigned_to') ? $this->input('assigned_to') : null,
            'estimated_minutes' => $this->filled('estimated_minutes') ? $this->input('estimated_minutes') : null,
            'recurrence' => $this->filled('recurrence') ? $this->input('recurrence') : TaskRecurrence::None->value,
            'recurrence_interval' => $this->filled('recurrence_interval') ? $this->input('recurrence_interval') : 1,
            'recurrence_ends_at' => $this->filled('recurrence_ends_at') ? $this->input('recurrence_ends_at') : null,
            'dependency_ids' => array_values(array_unique(array_filter((array) $this->input('dependency_ids', [])))),
        ]);
    }

    public function rules(): array
    {
        return [
            'project_id' => [
                'required',
                'integer',
                Rule::exists('projects', 'id')->whereNull('deleted_at')->where('is_template', 0),
            ],
            'parent_id' => ['nullable', 'integer', Rule::exists('tasks', 'id')->whereNull('deleted_at')],
            'milestone_id' => ['nullable', 'integer', Rule::exists('milestones', 'id')],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'title' => ['required', 'string', 'max:180'],
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'recurrence' => ['required', Rule::enum(TaskRecurrence::class)],
            'recurrence_interval' => ['required', 'integer', 'min:1', 'max:365'],
            'recurrence_ends_at' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'dependency_ids' => ['array'],
            'dependency_ids.*' => ['integer', 'distinct', Rule::exists('tasks', 'id')->whereNull('deleted_at')],
            'description' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $projectId = (int) $this->input('project_id');
            $parentId = $this->input('parent_id');
            $milestoneId = $this->input('milestone_id');
            $dependencyIds = array_map('intval', (array) $this->input('dependency_ids', []));

            if ($parentId && ! Task::query()->whereKey($parentId)->where('project_id', $projectId)->exists()) {
                $validator->errors()->add('parent_id', __('The parent task must belong to the selected project.'));
            }

            if ($milestoneId && ! Milestone::query()->whereKey($milestoneId)->where('project_id', $projectId)->exists()) {
                $validator->errors()->add('milestone_id', __('The milestone must belong to the selected project.'));
            }

            if ($dependencyIds && Task::query()
                ->whereIn('id', $dependencyIds)
                ->where('project_id', '!=', $projectId)
                ->exists()) {
                $validator->errors()->add('dependency_ids', __('Dependencies must belong to the selected project.'));
            }

            if ($this->filled('due_date') && $this->filled('recurrence_ends_at')) {
                $dueDate = Carbon::parse((string) $this->input('due_date'))->startOfDay();
                $recurrenceEndsAt = Carbon::parse((string) $this->input('recurrence_ends_at'))->startOfDay();

                if ($recurrenceEndsAt->lt($dueDate)) {
                    $validator->errors()->add('recurrence_ends_at', __('The recurrence end date must be on or after the task due date.'));
                }
            }
        });
    }
}
