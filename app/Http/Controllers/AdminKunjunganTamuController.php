<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Requests\FilterKunjunganTamuRequest;
use App\Http\Requests\UpdateKunjunganTamuRequest;
use App\Models\KunjunganTamu;
use App\Models\SurveiTamu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminKunjunganTamuController extends Controller
{
    public function index(FilterKunjunganTamuRequest $request): View
    {
        $entriesQuery = $this->filteredEntriesQuery($request);

        return view('admin.kunjungan-tamu.index', [
            'entries' => (clone $entriesQuery)
                ->with(['petugas', 'validator'])
                ->latest('waktu_kunjungan')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
            'filters' => [
                'start_month' => $request->input('start_month'),
                'end_month' => $request->input('end_month'),
                'completion_status' => $request->input('completion_status', 'all'),
            ],
            'summary' => [
                'total_visitors' => (clone $entriesQuery)->count(),
                'completed_total' => (clone $entriesQuery)->where('status_selesai', true)->count(),
                'pending_total' => (clone $entriesQuery)->where('status_selesai', false)->count(),
                'period_label' => $this->resolvePeriodLabel(
                    $request->input('start_month'),
                    $request->input('end_month')
                ),
                'completion_status_label' => $this->resolveCompletionStatusLabel(
                    $request->input('completion_status', 'all')
                ),
                'survey' => $this->surveySummary(clone $entriesQuery),
                'keperluan_breakdown' => $this->keperluanBreakdown(clone $entriesQuery),
            ],
        ]);
    }

    public function edit(KunjunganTamu $kunjunganTamu): View
    {
        return view('admin.kunjungan-tamu.edit', [
            'entry' => $kunjunganTamu->load(['petugas', 'validator']),
        ]);
    }

    public function update(UpdateKunjunganTamuRequest $request, KunjunganTamu $kunjunganTamu): RedirectResponse
    {
        $originalSurveyState = [
            'nilai_pelayanan' => $kunjunganTamu->nilai_pelayanan,
            'nilai_kecepatan' => $kunjunganTamu->nilai_kecepatan,
            'nilai_fasilitas' => $kunjunganTamu->nilai_fasilitas,
            'jawaban_survei' => KunjunganTamu::saringJawabanSurvei($kunjunganTamu->jawaban_survei),
            'survey_waktu_dikirim' => $kunjunganTamu->survey_waktu_dikirim,
        ];

        $surveyPayload = [
            'nilai_pelayanan' => $request->filled('nilai_pelayanan') ? (int) $request->input('nilai_pelayanan') : null,
            'nilai_kecepatan' => $request->filled('nilai_kecepatan') ? (int) $request->input('nilai_kecepatan') : null,
            'nilai_fasilitas' => $request->filled('nilai_fasilitas') ? (int) $request->input('nilai_fasilitas') : null,
            'saran' => $request->filled('saran')
                ? $request->string('saran')->squish()->toString()
                : null,
        ];

        $kunjunganTamu->update([
            'nama' => $request->string('nama')->squish()->toString(),
            'nomor_telepon' => $request->string('nomor_telepon')->toString(),
            'nik' => $request->string('nik')->toString(),
            'tanggal_lahir' => $request->input('tanggal_lahir'),
            'umur' => (int) $request->input('umur'),
            'keperluan' => $request->string('keperluan')->toString(),
            'detail_keperluan' => $request->string('detail_keperluan')->squish()->toString(),
            ...$surveyPayload,
            'waktu_kunjungan' => Carbon::createFromFormat(
                'Y-m-d\TH:i',
                $request->string('waktu_kunjungan')->toString(),
                config('app.timezone')
            ),
        ]);

        $this->syncSurveySnapshot($kunjunganTamu, $surveyPayload, $originalSurveyState);

        return to_route('admin.dashboard')->with('status', 'Data tamu berhasil diperbarui.');
    }

    public function destroy(KunjunganTamu $kunjunganTamu): RedirectResponse
    {
        \Illuminate\Support\Facades\Log::info('Destroy method called for ID: ' . $kunjunganTamu->id);
        // Temukan semua survey yang terkait dengan guest entry ini
        $surveyIds = \App\Models\SurveiTamu::query()
            ->where('id_kunjungan_tamu', $kunjunganTamu->id)
            ->pluck('id');

        // Putuskan referensi dari kunjungan_tamu
        $kunjunganTamu->update(['id_survei_tamu' => null]);

        // Hapus survey yang terkait secara manual agar tidak terjadi constraint violation
        if ($surveyIds->isNotEmpty()) {
            \App\Models\SurveiTamu::query()
                ->whereIn('id', $surveyIds)
                ->delete();
        }

        // Hapus data tamu
        $kunjunganTamu->delete();

        return to_route('admin.dashboard')->with('status', 'Data tamu berhasil dihapus.');
    }

    public function printReceipt(KunjunganTamu $kunjunganTamu): Response
    {
        return Pdf::loadView('admin.kunjungan-tamu.receipt', [
            'kunjunganTamu' => $kunjunganTamu->load(['petugas', 'validator']),
        ])
            ->setPaper('a4', 'portrait')
            ->download('bukti_laporan_tamu_' . $kunjunganTamu->id . '_' . now()->format('Ymd_His') . '.pdf');
    }

    public function print(FilterKunjunganTamuRequest $request): View
    {
        return view('admin.kunjungan-tamu.print', [
            ...$this->reportPayload($request),
            'showActions' => true,
        ]);
    }

    public function export(FilterKunjunganTamuRequest $request, string $format): Response|StreamedResponse
    {
        $reportPayload = $this->reportPayload($request);
        $fileBaseName = $this->reportFileBaseName($request);

        return match ($format) {
            'pdf' => $this->exportPdf($reportPayload, $fileBaseName),
            'excel' => $this->exportExcel($reportPayload, $fileBaseName),
            default => abort(404),
        };
    }

    private function filteredEntriesQuery(FilterKunjunganTamuRequest $request): Builder
    {
        $query = KunjunganTamu::query();
        $startMonth = $request->input('start_month');
        $endMonth = $request->input('end_month');

        if ($startMonth && $endMonth) {
            $query->whereBetween('waktu_kunjungan', [
                Carbon::createFromFormat('Y-m', $startMonth)->startOfMonth(),
                Carbon::createFromFormat('Y-m', $endMonth)->endOfMonth(),
            ]);
        }

        match ($request->input('completion_status', 'all')) {
            'completed' => $query->where('status_selesai', true),
            'pending' => $query->where('status_selesai', false),
            default => null,
        };

        return $query;
    }

    private function surveySummary(Builder $query): array
    {
        $summary = $query->selectRaw(
            'AVG(nilai_pelayanan) as service_avg, AVG(nilai_kecepatan) as speed_avg, AVG(nilai_fasilitas) as facility_avg'
        )->first();

        $averages = collect([
            'service' => $summary?->service_avg !== null ? round((float) $summary->service_avg, 1) : null,
            'speed' => $summary?->speed_avg !== null ? round((float) $summary->speed_avg, 1) : null,
            'facility' => $summary?->facility_avg !== null ? round((float) $summary->facility_avg, 1) : null,
        ]);

        $overall = $averages->filter(fn (?float $value): bool => $value !== null);

        return [
            ...$averages->all(),
            'overall' => $overall->isEmpty() ? null : round($overall->avg(), 1),
        ];
    }

    private function keperluanBreakdown(Builder $query): array
    {
        $totals = $query
            ->selectRaw('keperluan, COUNT(*) as total')
            ->groupBy('keperluan')
            ->pluck('total', 'keperluan');

        return collect(KunjunganTamu::KEPERLUAN)
            ->map(fn (string $label, string $key): array => [
                'key' => $key,
                'label' => $label,
                'total' => (int) ($totals[$key] ?? 0),
            ])
            ->filter(fn (array $item): bool => $item['total'] > 0)
            ->values()
            ->all();
    }

    private function resolvePeriodLabel(?string $startMonth, ?string $endMonth): string
    {
        if (! $startMonth || ! $endMonth) {
            return 'Semua periode';
        }

        return sprintf(
            '%s - %s',
            Carbon::createFromFormat('Y-m', $startMonth)->translatedFormat('F Y'),
            Carbon::createFromFormat('Y-m', $endMonth)->translatedFormat('F Y')
        );
    }

    private function resolveCompletionStatusLabel(string $status): string
    {
        return match ($status) {
            'completed' => 'Sudah selesai',
            'pending' => 'Belum selesai',
            default => 'Semua status',
        };
    }

    /**
     * @return array{entries: \Illuminate\Support\Collection<int, KunjunganTamu>, summary: array<string, mixed>, filters: array<string, string|null>}
     */
    private function reportPayload(FilterKunjunganTamuRequest $request): array
    {
        $entriesQuery = $this->filteredEntriesQuery($request);

        return [
            'entries' => (clone $entriesQuery)
                ->with(['petugas', 'validator'])
                ->latest('waktu_kunjungan')
                ->latest('id')
                ->get(),
            'summary' => [
                'total_visitors' => (clone $entriesQuery)->count(),
                'completed_total' => (clone $entriesQuery)->where('status_selesai', true)->count(),
                'pending_total' => (clone $entriesQuery)->where('status_selesai', false)->count(),
                'period_label' => $this->resolvePeriodLabel(
                    $request->input('start_month'),
                    $request->input('end_month')
                ),
                'completion_status_label' => $this->resolveCompletionStatusLabel(
                    $request->input('completion_status', 'all')
                ),
                'survey' => $this->surveySummary(clone $entriesQuery),
                'keperluan_breakdown' => $this->keperluanBreakdown(clone $entriesQuery),
            ],
            'filters' => [
                'start_month' => $request->input('start_month'),
                'end_month' => $request->input('end_month'),
                'completion_status' => $request->input('completion_status', 'all'),
            ],
        ];
    }

    private function exportPdf(array $reportPayload, string $fileBaseName): Response
    {
        return Pdf::loadView('admin.kunjungan-tamu.print', [
            ...$reportPayload,
            'showActions' => false,
        ])
            ->setPaper('a4', 'landscape')
            ->download($fileBaseName . '.pdf');
    }

    private function exportExcel(array $reportPayload, string $fileBaseName): StreamedResponse
    {
        $entries = $reportPayload['entries'];
        $summary = $reportPayload['summary'];
        $keperluanBreakdown = $summary['keperluan_breakdown'];
        $saranEntries = $entries->filter(fn (KunjunganTamu $entry): bool => filled($entry->saran));

        return response()->streamDownload(function () use (
            $entries,
            $summary,
            $keperluanBreakdown,
            $saranEntries
        ): void {
            $output = fopen('php://output', 'wb');

            if (! is_resource($output)) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");

            $writeRow = static function (array $row) use ($output): void {
                fputcsv($output, $row, ';');
            };

            $writeRow(['Laporan Buku Tamu dan Survei Kepuasan Masyarakat']);
            $writeRow(['Periode', $summary['period_label']]);
            $writeRow(['Status Proses', $summary['completion_status_label']]);
            $writeRow(['Tanggal Export', now()->translatedFormat('d F Y H:i') . ' WIB']);
            $writeRow(['Petugas Export', auth()->user()?->name ?? '-']);
            $writeRow([]);

            $writeRow(['Ringkasan Laporan']);
            $writeRow(['Total Tamu', 'Sudah Selesai', 'Belum Selesai', 'Rata-rata Survei']);
            $writeRow([
                $summary['total_visitors'],
                $summary['completed_total'],
                $summary['pending_total'],
                $summary['survey']['overall'] !== null ? number_format($summary['survey']['overall'], 1) . ' / 5' : '-',
            ]);
            $writeRow([]);

            $writeRow(['Nilai Survei']);
            $writeRow(['Aspek', 'Rata-rata']);
            $writeRow(['Pelayanan', $summary['survey']['service'] !== null ? number_format($summary['survey']['service'], 1) . ' / 5' : '-']);
            $writeRow(['Kecepatan', $summary['survey']['speed'] !== null ? number_format($summary['survey']['speed'], 1) . ' / 5' : '-']);
            $writeRow(['Fasilitas', $summary['survey']['facility'] !== null ? number_format($summary['survey']['facility'], 1) . ' / 5' : '-']);
            $writeRow([]);

            $writeRow(['Rekap Jenis Keperluan']);
            $writeRow(['No', 'Jenis Keperluan', 'Jumlah']);
            if ($keperluanBreakdown === []) {
                $writeRow(['-', 'Tidak ada data rekap keperluan pada periode yang dipilih.', '-']);
            } else {
                foreach ($keperluanBreakdown as $index => $keperluan) {
                    $writeRow([
                        $index + 1,
                        $keperluan['label'],
                        $keperluan['total'],
                    ]);
                }
            }
            $writeRow([]);

            $writeRow(['Rincian Data Tamu']);
            $writeRow([
                'No',
                'Tanggal',
                'Nama',
                'Email',
                'NIK',
                'Telepon',
                'Jenis Keperluan',
                'Uraian Keperluan',
                'Status',
                'Validator',
                'Nilai Pelayanan',
                'Nilai Kecepatan',
                'Nilai Fasilitas',
                'Saran Perbaikan',
            ]);

            if ($entries->isEmpty()) {
                $writeRow(['-', '-', 'Tidak ada data tamu pada filter yang dipilih.']);
            } else {
                foreach ($entries as $index => $entry) {
                    $writeRow([
                        $index + 1,
                        $entry->waktu_kunjungan->translatedFormat('d-m-Y H:i'),
                        $entry->nama,
                        $entry->petugas?->email ?? 'Tanpa akun',
                        $entry->nik,
                        $entry->nomor_telepon,
                        $entry->keperluan_label,
                        filled($entry->detail_keperluan) ? $entry->detail_keperluan : '-',
                        $entry->status_label,
                        $entry->validator?->name ?? '-',
                        $entry->nilai_pelayanan ?? '-',
                        $entry->nilai_kecepatan ?? '-',
                        $entry->nilai_fasilitas ?? '-',
                        filled($entry->saran) ? $entry->saran : '-',
                    ]);
                }
            }
            $writeRow([]);

            $writeRow(['Saran Perbaikan dari Masyarakat']);
            $writeRow(['No', 'Nama Tamu', 'Saran Perbaikan']);
            if ($saranEntries->isEmpty()) {
                $writeRow(['-', '-', 'Tidak terdapat saran perbaikan pada data yang ditampilkan.']);
            } else {
                foreach ($saranEntries->values() as $index => $entry) {
                    $writeRow([
                        $index + 1,
                        $entry->nama,
                        $entry->saran,
                    ]);
                }
            }

            fclose($output);
        }, $fileBaseName . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function reportFileBaseName(FilterKunjunganTamuRequest $request): string
    {
        $status = $request->input('completion_status', 'all');
        $startMonth = $request->input('start_month');
        $endMonth = $request->input('end_month');

        $periodSegment = $startMonth && $endMonth
            ? sprintf('%s_sampai_%s', $startMonth, $endMonth)
            : 'semua_periode';

        return sprintf(
            'laporan-buku-tamu_%s_%s_%s',
            $periodSegment,
            $status,
            now()->format('Ymd_His')
        );
    }

    /**
     * @param  array<string, mixed>  $surveyPayload
     * @param  array<string, mixed>  $originalSurveyState
     */
    private function syncSurveySnapshot(
        KunjunganTamu $kunjunganTamu,
        array $surveyPayload,
        array $originalSurveyState
    ): void {
        $hasSurveyValues = collect([
            $surveyPayload['nilai_pelayanan'],
            $surveyPayload['nilai_kecepatan'],
            $surveyPayload['nilai_fasilitas'],
            $surveyPayload['saran'],
        ])->contains(fn (mixed $value): bool => $value !== null && $value !== '');

        if (! $hasSurveyValues) {
            $this->clearSurveySnapshot($kunjunganTamu);

            return;
        }

        $surveyAnswers = $this->shouldPreserveSurveyAnswers($surveyPayload, $originalSurveyState)
            ? KunjunganTamu::saringJawabanSurvei($originalSurveyState['jawaban_survei'] ?? null)
            : null;

        $submittedAt = $originalSurveyState['survey_waktu_dikirim'] ?? now();

        $survey = $this->persistSurveyRecord($kunjunganTamu, $surveyPayload, $surveyAnswers, $submittedAt);

        $propagatedSurveyPayload = [
            ...$surveyPayload,
            'id_survei_tamu' => $survey->id,
            'jawaban_survei' => $surveyAnswers,
            'survey_waktu_dikirim' => $survey->waktu_dikirim,
        ];

        if ($kunjunganTamu->id_petugas !== null) {
            KunjunganTamu::query()
                ->where('id_petugas', $kunjunganTamu->id_petugas)
                ->update($propagatedSurveyPayload);

            return;
        }

        $kunjunganTamu->update($propagatedSurveyPayload);
    }

    /**
     * @param  array<string, mixed>  $surveyPayload
     * @param  array<string, mixed>  $originalSurveyState
     */
    private function shouldPreserveSurveyAnswers(array $surveyPayload, array $originalSurveyState): bool
    {
        return $surveyPayload['nilai_pelayanan'] === $originalSurveyState['nilai_pelayanan']
            && $surveyPayload['nilai_kecepatan'] === $originalSurveyState['nilai_kecepatan']
            && $surveyPayload['nilai_fasilitas'] === $originalSurveyState['nilai_fasilitas'];
    }

    /**
     * @param  array<string, mixed>  $surveyPayload
     * @param  array<string, mixed>|null  $surveyAnswers
     */
    private function persistSurveyRecord(
        KunjunganTamu $kunjunganTamu,
        array $surveyPayload,
        ?array $surveyAnswers,
        Carbon $submittedAt
    ): SurveiTamu {
        $attributes = [
            'user_id' => $kunjunganTamu->id_petugas,
            'id_kunjungan_tamu' => $kunjunganTamu->id,
            'nilai_pelayanan' => $surveyPayload['nilai_pelayanan'],
            'nilai_kecepatan' => $surveyPayload['nilai_kecepatan'],
            'nilai_fasilitas' => $surveyPayload['nilai_fasilitas'],
            'saran' => $surveyPayload['saran'],
            'jawaban_survei' => $surveyAnswers,
            'waktu_dikirim' => $submittedAt,
        ];

        if ($kunjunganTamu->id_petugas !== null) {
            return SurveiTamu::query()->updateOrCreate(
                ['id_petugas' => $kunjunganTamu->id_petugas],
                $attributes
            );
        }

        $survey = $kunjunganTamu->surveiTamu ?? new SurveiTamu();
        $survey->fill($attributes);
        $survey->save();

        return $survey;
    }

    private function clearSurveySnapshot(KunjunganTamu $kunjunganTamu): void
    {
        $propagatedSurveyPayload = [
            'id_survei_tamu' => null,
            'nilai_pelayanan' => null,
            'nilai_kecepatan' => null,
            'nilai_fasilitas' => null,
            'saran' => null,
            'jawaban_survei' => null,
            'survey_waktu_dikirim' => null,
        ];

        if ($kunjunganTamu->id_petugas !== null) {
            SurveiTamu::query()
                ->where('id_petugas', $kunjunganTamu->id_petugas)
                ->delete();

            KunjunganTamu::query()
                ->where('id_petugas', $kunjunganTamu->id_petugas)
                ->update($propagatedSurveyPayload);

            return;
        }

        $kunjunganTamu->surveiTamu?->delete();
        $kunjunganTamu->update($propagatedSurveyPayload);
    }
}
