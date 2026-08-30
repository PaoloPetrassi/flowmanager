<?php

namespace App\Http\Requests;

use App\Enums\AssetStatus;
use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'asset_tag' => strtoupper(trim((string) $this->input('asset_tag'))),
            'name' => trim((string) $this->input('name')),
            'serial_number' => $this->filled('serial_number')
                ? strtoupper(trim((string) $this->input('serial_number')))
                : null,
            'company_id' => $this->filled('company_id') ? $this->input('company_id') : null,
            'assigned_to' => $this->filled('assigned_to') ? $this->input('assigned_to') : null,
            'purchase_cost' => $this->filled('purchase_cost') ? $this->input('purchase_cost') : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Asset $asset */
        $asset = $this->route('asset');

        return [
            'company_id' => [
                'nullable',
                'integer',
                Rule::exists('companies', 'id')->whereNull('deleted_at'),
            ],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'asset_tag' => [
                'required',
                'string',
                'max:80',
                Rule::unique('assets', 'asset_tag')->ignore($asset),
            ],
            'name' => ['required', 'string', 'max:180'],
            'category' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:120'],
            'serial_number' => [
                'nullable',
                'string',
                'max:120',
                Rule::unique('assets', 'serial_number')->ignore($asset),
            ],
            'status' => ['required', Rule::enum(AssetStatus::class)],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'warranty_expires_at' => ['nullable', 'date', 'after_or_equal:purchase_date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
