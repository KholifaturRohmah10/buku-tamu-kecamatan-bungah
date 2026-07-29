<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterKunjunganTamuRequest;
use App\Http\Requests\UpdateValidasiKunjunganTamuRequest;
use App\Models\KunjunganTamu;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ValidatorKunjunganTamuController extends Controller
{
    public function index(FilterKunjunganTamuRequest $request): View
    {
        $entriesQuery = $this->filteredEntriesQuery($request);

        return view('validator.kunjungan-tamu.index', [
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
            ],
        ]);
    }

    public function update(
        UpdateValidasiKunjunganTamuRequest $request,
        KunjunganTamu $kunjunganTamu
    ): RedirectResponse {
        if ($kunjunganTamu->status_selesai) {
            return back()->withErrors([
                'validation_status' => 'Status keperluan tamu yang sudah selesai tidak dapat diubah lagi.',
            ]);
        }

        $kunjunganTamu->update([
            'status_selesai' => $request->boolean('status_selesai'),
            'id_validator' => $request->user()->id,
            'waktu_divalidasi' => now(),
        ]);

        return back()->with('status', 'Status keperluan tamu berhasil diperbarui.');
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

    public function printReceipt(KunjunganTamu $kunjunganTamu): Response
    {
        return Pdf::loadView('validator.kunjungan-tamu.receipt', [
            'kunjunganTamu' => $kunjunganTamu->load(['petugas', 'validator']),
        ])
            ->setPaper('a4', 'portrait')
            ->download('bukti_laporan_tamu_' . $kunjunganTamu->id . '_' . now()->format('Ymd_His') . '.pdf');
    }

    public function print(FilterKunjunganTamuRequest $request): View
    {
        return view('validator.kunjungan-tamu.print', [
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
        return Pdf::loadView('validator.kunjungan-tamu.print', [
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

        return response()->streamDownload(function () use (
            $entries,
            $summary,
            $keperluanBreakdown
        ): void {
            $output = fopen('php://output', 'wb');
            if (! is_resource($output)) { return; }
            fwrite($output, "\xEF\xBB\xBF");
            $writeRow = static function (array $row) use ($output): void {
                fputcsv($output, $row, ';');
            };

            $writeRow(['Laporan Buku Tamu']);
            $writeRow(['Periode', $summary['period_label']]);
            $writeRow(['Status Proses', $summary['completion_status_label']]);
            $writeRow(['Tanggal Export', now()->translatedFormat('d F Y H:i') . ' WIB']);
            $writeRow(['Petugas Export', auth()->user()?->name ?? '-']);
            $writeRow([]);

            $writeRow(['Ringkasan Laporan']);
            $writeRow(['Total Tamu', 'Sudah Selesai', 'Belum Selesai']);
            $writeRow([
                $summary['total_visitors'],
                $summary['completed_total'],
                $summary['pending_total'],
            ]);
            $writeRow([]);

            $writeRow(['Rekap Jenis Keperluan']);
            $writeRow(['No', 'Jenis Keperluan', 'Jumlah']);
            if ($keperluanBreakdown === []) {
                $writeRow(['-', 'Tidak ada data rekap keperluan pada periode yang dipilih.', '-']);
            } else {
                foreach ($keperluanBreakdown as $index => $keperluan) {
                    $writeRow([$index + 1, $keperluan['label'], $keperluan['total']]);
                }
            }
            $writeRow([]);

            $writeRow(['Rincian Data Tamu']);
            $writeRow([
                'No', 'Tanggal', 'Nama', 'Email', 'NIK', 'Telepon', 
                'Jenis Keperluan', 'Uraian Keperluan', 'Status', 'Validator'
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
}
