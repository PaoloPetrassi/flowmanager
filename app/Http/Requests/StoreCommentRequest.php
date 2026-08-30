<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('comments.create') === true;
    }

    public function rules(): array
    {
        return [
            'body' => [
                'required',
                'string',
                'max:5000',
            ],
        ];
    }
}
