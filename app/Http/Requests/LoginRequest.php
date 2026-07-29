<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errorBag = $this->string('form_context')->toString() === 'internal-login'
            ? 'internalLogin'
            : 'guestLogin';

        throw (new ValidationException($validator))
            ->errorBag($errorBag)
            ->redirectTo($this->getRedirectUrl());
    }
}
