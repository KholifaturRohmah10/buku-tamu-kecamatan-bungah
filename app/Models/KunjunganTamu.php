<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganTamu extends Model
{
    protected $table = 'kunjungan_tamu';

    public const KEPERLUAN = [
        'akta_kematian' => 'Layanan Akta Kematian',
        'akta_kelahiran' => 'Layanan Akta Kelahiran',
        'ahli_waris' => 'Layanan Pengurusan Ahli Waris',
        'pbb' => 'Pelayanan Pembayaran PBB',
        'dispensasi_nikah' => 'Pelayanan Dispensasi Nikah',
        'rekam_ktp' => 'Pelayanan Rekam Ktp dan cetak ktp hilang',
        'nib' => 'Pendaftaran NIB',
        'kk' => 'Pelayanan KK',
    ];

    public const KATEGORI_SURVEI = [
        'pelayanan' => 'Pelayanan',
        'kecepatan' => 'Kecepatan',
        'fasilitas' => 'Fasilitas',
    ];

    public const GRUP_RINGKASAN_SURVEI = [
        'pelayanan' => ['q1', 'q2', 'q3', 'q4'],
        'kecepatan' => ['q5', 'q6', 'q7', 'q8'],
        'fasilitas' => ['q9', 'q10', 'q11'],
    ];

    public const OPSI_SKOR_SURVEI = [
        3 => 'Puas',
        2 => 'Cukup Puas',
        1 => 'Tidak Puas',
    ];

    public const PERTANYAAN_SURVEI = [
        ['key' => 'q1', 'question' => 'Bagaimana pendapat Saudara tentang kesesuaian persyaratan pelayanan dengan jenis pelayanannya.'],
        ['key' => 'q2', 'question' => 'Bagaimana pemahaman Saudara tentang kemudahan prosedur pelayanan di unit ini.'],
        ['key' => 'q3', 'question' => 'Bagaimana pendapat Saudara tentang kecepatan waktu dalam memberikan pelayanan.'],
        ['key' => 'q4', 'question' => 'Bagaimana pendapat Saudara tentang kewajaran biaya/tarif dalam pelayanan.'],
        ['key' => 'q5', 'question' => 'Bagaimana pendapat Saudara tentang kesusaian produk pelayanan antara yang tercantum dalam standar pelayanan dengan hasil yang diberikan.'],
        ['key' => 'q6', 'question' => 'Bagaimana pendapat Saudara tentang kopentisi/kemampuan petugas dalam pelayanan.'],
        ['key' => 'q7', 'question' => 'Bagaimana pendapat Saudara perilaku petugas dalam pelayanan terkait kesopanan dan keramahan.'],
        ['key' => 'q8', 'question' => 'Bagaimana pendapat Saudara tentang penanganan pengaduan pengguna layanan.'],
        ['key' => 'q9', 'question' => 'Bagaimana pendapat Saudara tentang kualitas sarana dan prasarana.'],
        ['key' => 'q10', 'question' => 'Bagaimana pendapat anda tentang transparasi pelayanan yang diberikan.'],
        ['key' => 'q11', 'question' => 'Bagaimana integritas petugas pelayanan dalam memberikan pelayanan.'],
    ];

    protected $fillable = [
        'id_petugas',
        'id_survei_tamu',
        'nama',
        'nomor_telepon',
        'nik',
        'tanggal_lahir',
        'umur',
        'keperluan',
        'detail_keperluan',
        'status_selesai',
        'id_validator',
        'waktu_divalidasi',
        'nilai_pelayanan',
        'nilai_kecepatan',
        'nilai_fasilitas',
        'saran',
        'jawaban_survei',
        'survey_waktu_dikirim',
        'waktu_kunjungan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'status_selesai' => 'boolean',
            'jawaban_survei' => 'array',
            'survey_waktu_dikirim' => 'datetime',
            'waktu_divalidasi' => 'datetime',
            'waktu_kunjungan' => 'datetime',
        ];
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_petugas');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_validator');
    }

    public function surveiTamu(): BelongsTo
    {
        return $this->belongsTo(SurveiTamu::class, 'id_survei_tamu');
    }

    protected function keperluanLabel(): Attribute
    {
        return Attribute::get(
            fn (): string => self::KEPERLUAN[$this->keperluan] ?? $this->keperluan
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(
            fn (): string => $this->status_selesai ? 'Sudah Selesai' : 'Belum Selesai'
        );
    }

    protected function statusSurveiLabel(): Attribute
    {
        return Attribute::get(
            fn (): string => $this->id_survei_tamu !== null ? 'Sudah diisi' : 'Belum diisi'
        );
    }

    public static function kunciPertanyaanSurvei(): array
    {
        return collect(self::PERTANYAAN_SURVEI)
            ->pluck('key')
            ->all();
    }

    public static function rataRataKategoriSurveiDariRespon(array $respon): array
    {
        return collect(self::GRUP_RINGKASAN_SURVEI)
            ->mapWithKeys(function (array $kunciPertanyaan, string $kategori) use ($respon): array {
                $nilai = collect($kunciPertanyaan)
                    ->map(fn (string $kunci): mixed => $respon[$kunci] ?? null)
                    ->filter(fn ($n): bool => is_numeric($n))
                    ->map(fn ($n): int => (int) $n);

                return [
                    $kategori => $nilai->isEmpty() ? null : round($nilai->avg(), 1),
                ];
            })
            ->all();
    }

    public static function saringJawabanSurvei(?array $jawaban): array
    {
        if (! is_array($jawaban)) {
            return [];
        }

        return collect($jawaban)
            ->only(self::kunciPertanyaanSurvei())
            ->all();
    }
}
