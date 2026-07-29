<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveiTamu extends Model
{
    protected $table = 'survei_tamu';

    protected $fillable = [
        'id_pengguna',
        'id_kunjungan_tamu',
        'nilai_pelayanan',
        'nilai_kecepatan',
        'nilai_fasilitas',
        'saran',
        'kritik',
        'jawaban_survei',
        'waktu_dikirim',
    ];

    protected function casts(): array
    {
        return [
            'jawaban_survei' => 'array',
            'waktu_dikirim' => 'datetime',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    public function kunjunganTamu(): BelongsTo
    {
        return $this->belongsTo(KunjunganTamu::class, 'id_kunjungan_tamu');
    }

    public function daftarKunjunganTamu(): HasMany
    {
        return $this->hasMany(KunjunganTamu::class, 'id_survei_tamu');
    }
}
