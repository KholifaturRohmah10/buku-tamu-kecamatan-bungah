<?php

namespace App\Http\Requests;

use App\Models\KunjunganTamu;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKunjunganTamuRequest extends FormRequest
{
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
            'nilai_pelayanan' => ['nullable', 'integer', 'between:1,3'],
            'nilai_kecepatan' => ['nullable', 'integer', 'between:1,3'],
            'nilai_fasilitas' => ['nullable', 'integer', 'between:1,3'],
            'saran' => ['nullable', 'string', 'max:1000'],
            'waktu_kunjungan' => ['required', 'date_format:Y-m-d\TH:i'],
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
            'waktu_kunjungan.required' => 'Waktu kunjungan wajib diisi.',
            'waktu_kunjungan.date_format' => 'Format waktu kunjungan tidak valid.',
        ];
    }
}
