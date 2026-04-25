<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('users.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => filled($this->input('email')) ? $this->input('email') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'username'   => ['required', 'string', 'max:100', Rule::unique('users', 'username')],
            'email'      => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'role_names' => ['required', 'array', 'min:1'],
            'role_names.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ];
    }
}