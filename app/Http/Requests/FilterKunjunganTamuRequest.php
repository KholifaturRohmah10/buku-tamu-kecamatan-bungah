<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterKunjunganTamuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bulan_awal' => ['nullable', 'date_format:Y-m', 'required_with:bulan_akhir'],
            'bulan_akhir' => ['nullable', 'date_format:Y-m', 'required_with:bulan_awal', 'after_or_equal:bulan_awal'],
            'status_selesai' => ['nullable', 'in:semua,selesai,belum'],
            'nama_tamu' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'bulan_awal.date_format' => 'Format bulan awal harus YYYY-MM.',
            'bulan_awal.required_with' => 'Bulan awal wajib diisi jika bulan akhir dipilih.',
            'bulan_akhir.date_format' => 'Format bulan akhir harus YYYY-MM.',
            'bulan_akhir.required_with' => 'Bulan akhir wajib diisi jika bulan awal dipilih.',
            'bulan_akhir.after_or_equal' => 'Bulan akhir harus sama atau setelah bulan awal.',
            'status_selesai.in' => 'Filter status proses tidak valid.',
        ];
    }
}
