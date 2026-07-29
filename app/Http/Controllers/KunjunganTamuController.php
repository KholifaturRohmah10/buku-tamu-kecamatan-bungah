<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKunjunganTamuRequest;
use App\Http\Requests\StoreSurveiTamuRequest;
use App\Models\KunjunganTamu;
use App\Models\SurveiTamu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KunjunganTamuController extends Controller
{
    public function index(Request $request): View
    {
        return view('kunjungan-tamu.index');
    }

    public function store(StoreKunjunganTamuRequest $request): RedirectResponse
    {
        $kunjunganTamu = KunjunganTamu::create([
            'nama' => $request->string('nama')->squish()->toString(),
            'nomor_telepon' => $request->string('nomor_telepon')->toString(),
            'nik' => $request->string('nik')->toString(),
            'tanggal_lahir' => $request->input('tanggal_lahir'),
            'umur' => (int) $request->input('umur'),
            'keperluan' => $request->string('keperluan')->toString(),
            'detail_keperluan' => $request->string('detail_keperluan')->squish()->toString(),
            'waktu_kunjungan' => now(),
        ]);

        return to_route('kunjungan-tamu.survey.create', $kunjunganTamu)
            ->with('status', 'Keperluan berhasil dikirim. Silakan lanjutkan ke halaman survei kepuasan masyarakat.');
    }

    public function createSurvey(Request $request, KunjunganTamu $kunjunganTamu): View|RedirectResponse
    {
        if ($kunjunganTamu->survey_waktu_dikirim !== null) {
            return to_route('kunjungan-tamu.index')
                ->with('status', 'Survei tamu untuk pengajuan ini sudah pernah diisi.');
        }

        return view('kunjungan-tamu.survey', [
            'kunjunganTamu' => $kunjunganTamu,
            'surveyQuestions' => KunjunganTamu::PERTANYAAN_SURVEI,
            'surveyScoreLabels' => KunjunganTamu::OPSI_SKOR_SURVEI,
            'surveyCategoryLabels' => KunjunganTamu::KATEGORI_SURVEI,
        ]);
    }

    public function storeSurvey(
        StoreSurveiTamuRequest $request,
        KunjunganTamu $kunjunganTamu
    ): RedirectResponse {
        if ($kunjunganTamu->survey_waktu_dikirim !== null) {
            return to_route('kunjungan-tamu.index')
                ->with('status', 'Survei tamu untuk pengajuan ini sudah pernah diisi.');
        }

        $responses = collect($request->validated('jawaban'))
            ->map(fn (mixed $value): int => (int) $value)
            ->all();

        $categoryaverages = collect(KunjunganTamu::rataRataKategoriSurveiDariRespon($responses));

        $submittedAt = now();

        $surveyPayload = [
            'id_kunjungan_tamu' => $kunjunganTamu->id,
            'nilai_pelayanan' => (int) round((float) $categoryaverages['pelayanan']),
            'nilai_kecepatan' => (int) round((float) $categoryaverages['kecepatan']),
            'nilai_fasilitas' => (int) round((float) $categoryaverages['fasilitas']),
            'saran' => $request->filled('saran')
                ? $request->string('saran')->squish()->toString()
                : null,
            'kritik' => $request->filled('kritik')
                ? $request->string('kritik')->squish()->toString()
                : null,
            'jawaban_survei' => KunjunganTamu::saringJawabanSurvei($responses),
            'waktu_dikirim' => $submittedAt,
        ];

        $survey = SurveiTamu::create($surveyPayload);

        $kunjunganTamu->update(
            $this->buildSurveySnapshotPayload($survey)
        );

        return to_route('kunjungan-tamu.success', $kunjunganTamu)->with(
            'status',
            'Survei kepuasan masyarakat berhasil dikirim. Terima kasih atas kunjungan Anda.'
        );
    }

    public function success(Request $request, KunjunganTamu $kunjunganTamu): View
    {
        return view('kunjungan-tamu.success', [
            'kunjunganTamu' => $kunjunganTamu,
        ]);
    }


    /**
     * @return array<string, mixed>
     */
    private function buildSurveySnapshotPayload(SurveiTamu $survey): array
    {
        return [
            'id_survei_tamu' => $survey->id,
            'nilai_pelayanan' => $survey->nilai_pelayanan,
            'nilai_kecepatan' => $survey->nilai_kecepatan,
            'nilai_fasilitas' => $survey->nilai_fasilitas,
            'saran' => $survey->saran,
            'jawaban_survei' => KunjunganTamu::saringJawabanSurvei($survey->jawaban_survei),
            'survey_waktu_dikirim' => $survey->waktu_dikirim,
        ];
    }
}
