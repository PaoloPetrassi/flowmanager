<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'roles' => array_values(array_filter((array) $this->input('roles', []))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'roles' => ['array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var User $user */
            $user = $this->route('user');

            $administratorRole = Role::query()
                ->where('slug', 'administrator')
                ->first();

            if (! $administratorRole || ! $user->hasRole('administrator')) {
                return;
            }

            $submittedRoleIds = array_map(
                'intval',
                (array) $this->input('roles', [])
            );

            if (in_array($administratorRole->id, $submittedRoleIds, true)) {
                return;
            }

            $anotherAdministratorExists = $administratorRole->users()
                ->where('users.id', '!=', $user->id)
                ->exists();

            if (! $anotherAdministratorExists) {
                $validator->errors()->add(
                    'roles',
                    'The last administrator cannot lose the Administrator role.'
                );
            }
        });
    }
}
