<?php

namespace App\Support;

use Carbon\CarbonImmutable;

class NikParser
{
    public function parse(string $nik): ?array
    {
        $cleanNik = preg_replace('/\D+/', '', $nik);

        if ($cleanNik === null || strlen($cleanNik) !== 16) {
            return null;
        }

        $dayCode = (int) substr($cleanNik, 6, 2);
        $month = (int) substr($cleanNik, 8, 2);
        $year = (int) substr($cleanNik, 10, 2);
        $day = $dayCode > 40 ? $dayCode - 40 : $dayCode;

        if ($day < 1 || $month < 1 || $month > 12) {
            return null;
        }

        $currentYear = (int) now()->format('y');
        $fullYear = $year > $currentYear ? 1900 + $year : 2000 + $year;

        if (! checkdate($month, $day, $fullYear)) {
            return null;
        }

        $birthDate = CarbonImmutable::create(
            $fullYear,
            $month,
            $day,
            0,
            0,
            0,
            config('app.timezone')
        );

        if ($birthDate->isFuture()) {
            $birthDate = $birthDate->subCentury();
        }

        if ($birthDate->umur < 0 || $birthDate->umur > 120) {
            return null;
        }

        return [
            'nik' => $cleanNik,
            'tanggal_lahir' => $birthDate->toDateString(),
            'umur' => $birthDate->umur,
        ];
    }
}
