<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('attachments.create') === true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,csv,txt,png,jpg,jpeg,zip'],
            'document_category' => ['nullable', 'string', 'max:60'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
