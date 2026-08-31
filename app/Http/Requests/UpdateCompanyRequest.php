<?php

namespace App\Http\Requests;

use App\Enums\CompanyStatus;
use App\Enums\CompanyType;
use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
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
            'vat_number' => $this->filled('vat_number')
                ? strtoupper(trim((string) $this->input('vat_number')))
                : null,

            'tax_code' => $this->filled('tax_code')
                ? strtoupper(trim((string) $this->input('tax_code')))
                : null,

            'country_code' => strtoupper(
                trim((string) ($this->input('country_code') ?: 'IT'))
            ),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $company = $this->route('company');

        $companyId = $company instanceof Company
            ? $company->getKey()
            : $company;

        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'legal_name' => [
                'nullable',
                'string',
                'max:200',
            ],

            'type' => [
                'required',
                Rule::enum(CompanyType::class),
            ],

            'status' => [
                'required',
                Rule::enum(CompanyStatus::class),
            ],

            'vat_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('companies', 'vat_number')
                    ->ignore($companyId),
            ],

            'tax_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('companies', 'tax_code')
                    ->ignore($companyId),
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

            'website' => [
                'nullable',
                'url',
                'max:255',
            ],

            'industry' => [
                'nullable',
                'string',
                'max:100',
            ],

            'employees' => [
                'nullable',
                'integer',
                'min:0',
                'max:10000000',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'province' => [
                'nullable',
                'string',
                'max:100',
            ],

            'postal_code' => [
                'nullable',
                'string',
                'max:20',
            ],

            'country_code' => [
                'required',
                'string',
                'size:2',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }
}
