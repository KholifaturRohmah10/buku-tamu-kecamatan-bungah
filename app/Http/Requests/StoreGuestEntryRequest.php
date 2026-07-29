<?php

namespace App\Http\Requests;

use App\Models\GuestEntry;
use App\Support\NikParser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreGuestEntryRequest extends FormRequest
{
    protected $errorBag = 'guestEntry';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'digits_between:10,15'],
            'nik' => ['required', 'digits:16'],
            'age' => ['required', 'integer', 'min:0', 'max:150'],
            'birth_date' => ['required', 'date'],
            'purpose' => ['required', 'in:'.implode(',', array_keys(GuestEntry::PURPOSES))],
            'purpose_detail' => ['nullable', 'string', 'max:500'],
        ];
    }



    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'phone_number.required' => 'Nomor telepon wajib diisi.',
            'phone_number.digits_between' => 'Nomor telepon harus terdiri dari 10 sampai 15 digit angka.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit angka.',
            'age.required' => 'Umur wajib diisi.',
            'age.integer' => 'Umur harus berupa angka.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.date' => 'Format tanggal lahir tidak valid.',
            'purpose.required' => 'Keperluan wajib dipilih.',
            'purpose.in' => 'Keperluan yang dipilih tidak tersedia.',
        ];
    }

}
