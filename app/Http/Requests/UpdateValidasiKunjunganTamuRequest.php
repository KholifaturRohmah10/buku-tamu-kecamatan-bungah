<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateValidasiKunjunganTamuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_selesai' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'status_selesai.required' => 'Status penyelesaian wajib dipilih.',
            'status_selesai.boolean' => 'Status penyelesaian tidak valid.',
        ];
    }
}
