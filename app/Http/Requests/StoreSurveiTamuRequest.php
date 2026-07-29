<?php

namespace App\Http\Requests;

use App\Models\KunjunganTamu;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSurveiTamuRequest extends FormRequest
{
    protected $errorBag = 'surveiTamu';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $aturanPertanyaan = [];

        foreach (KunjunganTamu::PERTANYAAN_SURVEI as $pertanyaan) {
            $aturanPertanyaan['jawaban.'.$pertanyaan['key']] = ['required', 'integer', 'between:1,3'];
        }

        return [
            ...$aturanPertanyaan,
            'saran' => ['nullable', 'string', 'max:1000'],
            'kritik' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'jawaban.*.required' => 'Semua jawaban survei wajib diisi.',
            'jawaban.*.integer' => 'Jawaban survei harus berupa angka.',
            'jawaban.*.between' => 'Jawaban survei harus bernilai 1 sampai 3.',
        ];
    }
}
