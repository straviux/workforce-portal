<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('users.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => filled($this->input('email')) ? $this->input('email') : null,
            'office' => filled($this->input('office')) ? $this->input('office') : null,
            'designation' => filled($this->input('designation')) ? $this->input('designation') : null,
            'password' => filled($this->input('password')) ? $this->input('password') : null,
        ]);
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name'       => ['required', 'string', 'max:255'],
            'username'   => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($userId)],
            'email'      => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'office'     => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'password'   => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_names' => ['required', 'array', 'min:1'],
            'role_names.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ];
    }
}
