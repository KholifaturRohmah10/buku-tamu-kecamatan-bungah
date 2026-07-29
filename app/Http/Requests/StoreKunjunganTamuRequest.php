<?php

namespace App\Http\Requests;

use App\Models\KunjunganTamu;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreKunjunganTamuRequest extends FormRequest
{
    protected $errorBag = 'kunjunganTamu';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'nomor_telepon' => ['required', 'digits_between:10,15'],
            'nik' => ['required', 'digits:16'],
            'umur' => ['required', 'integer', 'min:0', 'max:150'],
            'tanggal_lahir' => ['required', 'date'],
            'keperluan' => ['required', 'in:'.implode(',', array_keys(KunjunganTamu::KEPERLUAN))],
            'detail_keperluan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nomor_telepon.required' => 'Nomor telepon wajib diisi.',
            'nomor_telepon.digits_between' => 'Nomor telepon harus terdiri dari 10 sampai 15 digit angka.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit angka.',
            'umur.required' => 'Umur wajib diisi.',
            'umur.integer' => 'Umur harus berupa angka.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'keperluan.required' => 'Keperluan wajib dipilih.',
            'keperluan.in' => 'Keperluan yang dipilih tidak tersedia.',
        ];
    }
}
