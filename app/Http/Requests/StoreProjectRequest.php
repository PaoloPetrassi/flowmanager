<?php

namespace App\Http\Requests;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'name' => trim((string) $this->input('name')),
            'company_id' => $this->filled('company_id') ? $this->input('company_id') : null,
            'contact_id' => $this->filled('contact_id') ? $this->input('contact_id') : null,
            'manager_id' => $this->filled('manager_id') ? $this->input('manager_id') : null,
            'budget' => $this->filled('budget') ? $this->input('budget') : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_id' => [
                'required',
                'integer',
                Rule::exists('companies', 'id')->whereNull('deleted_at'),
            ],
            'contact_id' => [
                'nullable',
                'integer',
                Rule::exists('contacts', 'id')->whereNull('deleted_at'),
            ],
            'manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'code' => ['required', 'string', 'max:50', Rule::unique('projects', 'code')],
            'name' => ['required', 'string', 'max:180'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'priority' => ['required', Rule::enum(ProjectPriority::class)],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $contactId = $this->input('contact_id');
            $companyId = $this->input('company_id');

            if (! $contactId || ! $companyId) {
                return;
            }

            $matches = Contact::query()
                ->whereKey($contactId)
                ->where('company_id', $companyId)
                ->exists();

            if (! $matches) {
                $validator->errors()->add(
                    'contact_id',
                    __('The selected contact must belong to the selected company.')
                );
            }
        });
    }
}
