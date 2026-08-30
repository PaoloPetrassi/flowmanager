<?php

namespace App\Http\Requests;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Contact;
use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reference' => strtoupper(trim((string) $this->input('reference'))),
            'subject' => trim((string) $this->input('subject')),
            'company_id' => $this->filled('company_id') ? $this->input('company_id') : null,
            'contact_id' => $this->filled('contact_id') ? $this->input('contact_id') : null,
            'assigned_to' => $this->filled('assigned_to') ? $this->input('assigned_to') : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Ticket $ticket */
        $ticket = $this->route('ticket');

        return [
            'company_id' => [
                'nullable',
                'integer',
                Rule::exists('companies', 'id')->whereNull('deleted_at'),
            ],
            'contact_id' => [
                'nullable',
                'integer',
                Rule::exists('contacts', 'id')->whereNull('deleted_at'),
            ],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'reference' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tickets', 'reference')->ignore($ticket),
            ],
            'subject' => ['required', 'string', 'max:200'],
            'category' => ['required', Rule::enum(TicketCategory::class)],
            'status' => ['required', Rule::enum(TicketStatus::class)],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'description' => ['required', 'string'],
            'resolution' => ['nullable', 'string'],
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
                    'The selected contact must belong to the selected company.'
                );
            }
        });
    }
}
