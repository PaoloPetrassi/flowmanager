<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => trim(
                (string) $this->input('first_name')
            ),

            'last_name' => trim(
                (string) $this->input('last_name')
            ),

            'email' => $this->filled('email')
                ? strtolower(trim((string) $this->input('email')))
                : null,

            'phone' => $this->filled('phone')
                ? trim((string) $this->input('phone'))
                : null,

            'mobile' => $this->filled('mobile')
                ? trim((string) $this->input('mobile'))
                : null,

            'is_primary' => $this->boolean('is_primary'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_id' => [
                'nullable',
                'integer',
                Rule::exists('companies', 'id')
                    ->whereNull('deleted_at'),
            ],

            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'job_title' => [
                'nullable',
                'string',
                'max:150',
            ],

            'department' => [
                'nullable',
                'string',
                'max:100',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:50',
            ],

            'is_primary' => [
                'boolean',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }
}
