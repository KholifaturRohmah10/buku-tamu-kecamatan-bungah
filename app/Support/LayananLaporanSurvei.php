<?php

namespace App\Support;

use App\Http\Requests\FilterKunjunganTamuRequest;
use App\Models\KunjunganTamu;
use App\Models\SurveiTamu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LayananLaporanSurvei
{
    /**
     * @return array{surveys: LengthAwarePaginator<int, SurveiTamu>, summary: array<string, mixed>, filters: array<string, string|null>}
     */
    public function indexPayload(FilterKunjunganTamuRequest $request): array
    {
        $query = $this->filteredSurveysQuery($request);

        $paginatedSurveys = (clone $query)
            ->with(['pengguna', 'kunjunganTamu.validator'])
            ->latest('waktu_dikirim')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $reportSurveys = (clone $query)
            ->with(['pengguna', 'kunjunganTamu.validator'])
            ->latest('waktu_dikirim')
            ->latest('id')
            ->get();

        return [
            'surveys' => $paginatedSurveys,
            'summary' => $this->summary($reportSurveys, $request),
            'filters' => $this->filters($request),
        ];
    }

    /**
     * @return array{surveys: Collection<int, SurveiTamu>, summary: array<string, mixed>, filters: array<string, string|null>}
     */
    public function reportPayload(FilterKunjunganTamuRequest $request): array
    {
        $surveys = $this->filteredSurveysQuery($request)
            ->with(['pengguna', 'kunjunganTamu.validator'])
            ->latest('waktu_dikirim')
            ->latest('id')
            ->get();

        return [
            'surveys' => $surveys,
            'summary' => $this->summary($surveys, $request),
            'filters' => $this->filters($request),
        ];
    }

    private function filteredSurveysQuery(FilterKunjunganTamuRequest $request): Builder
    {
        $query = SurveiTamu::query()->whereNotNull('waktu_dikirim');
        $startMonth = $request->input('start_month');
        $endMonth = $request->input('end_month');

        if ($startMonth && $endMonth) {
            $query->whereBetween('waktu_dikirim', [
                Carbon::createFromFormat('Y-m', $startMonth)->startOfMonth(),
                Carbon::createFromFormat('Y-m', $endMonth)->endOfMonth(),
            ]);
        }

        match ($request->input('completion_status', 'all')) {
            'completed' => $query->whereHas('kunjunganTamu', fn (Builder $entryQuery): Builder => $entryQuery->where('status_selesai', true)),
            'pending' => $query->whereHas('kunjunganTamu', fn (Builder $entryQuery): Builder => $entryQuery->where('status_selesai', false)),
            default => null,
        };

        if ($guestName = $request->input('guest_name')) {
            $query->where(function (Builder $q) use ($guestName): void {
                $q->whereHas('pengguna', fn (Builder $u): Builder => $u->where('name', 'like', "%{$guestName}%"))
                  ->orWhereHas('kunjunganTamu', fn (Builder $e): Builder => $e->where('name', 'like', "%{$guestName}%"));
            });
        }

        return $query;
    }

    /**
     * @param  Collection<int, SurveiTamu>  $surveys
     * @return array<string, mixed>
     */
    private function summary(Collection $surveys, FilterKunjunganTamuRequest $request): array
    {
        $serviceaverage = $this->averageFor($surveys, 'nilai_pelayanan');
        $speedaverage = $this->averageFor($surveys, 'nilai_kecepatan');
        $facilityaverage = $this->averageFor($surveys, 'nilai_fasilitas');

        $overallValues = collect([
            $serviceaverage,
            $speedaverage,
            $facilityaverage,
        ])->filter(fn (?float $value): bool => $value !== null);

        return [
            'total_surveys' => $surveys->count(),
            'respondent_total' => $surveys->filter(fn (SurveiTamu $survey): bool => $survey->id_petugas !== null)->count(),
            'saran_total' => $surveys->filter(fn (SurveiTamu $survey): bool => filled($survey->saran))->count(),
            'kritik_total' => $surveys->filter(fn (SurveiTamu $survey): bool => filled($survey->kritik))->count(),
            'latest_waktu_dikirim' => $surveys->first()?->waktu_dikirim,
            'period_label' => $this->resolvePeriodLabel(
                $request->input('start_month'),
                $request->input('end_month')
            ),
            'completion_status_label' => $this->resolveCompletionStatusLabel(
                $request->input('completion_status', 'all')
            ),
            'survey' => [
                'service' => $serviceaverage,
                'speed' => $speedaverage,
                'facility' => $facilityaverage,
                'overall' => $overallValues->isEmpty() ? null : round($overallValues->avg(), 1),
            ],
            'keperluan_breakdown' => $this->keperluanBreakdown($surveys),
            'question_breakdown' => $this->questionBreakdown($surveys),
        ];
    }

    /**
     * @param  Collection<int, SurveiTamu>  $surveys
     * @return array<int, array{key: string, label: string, total: int}>
     */
    private function keperluanBreakdown(Collection $surveys): array
    {
        return $surveys
            ->map(fn (SurveiTamu $survey): ?string => $survey->kunjunganTamu?->keperluan)
            ->filter()
            ->countBy()
            ->map(fn (int $total, string $keperluan): array => [
                'key' => $keperluan,
                'label' => KunjunganTamu::KEPERLUAN[$keperluan] ?? $keperluan,
                'total' => $total,
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, SurveiTamu>  $surveys
     * @return array<int, array{key: string, label: string, category_key: string, category_label: string, question: string, average: float|null, responses: int}>
     */
    private function questionBreakdown(Collection $surveys): array
    {
        $questionCategories = $this->questionCategoryMap();

        return collect(KunjunganTamu::PERTANYAAN_SURVEI)
            ->map(function (array $question) use ($surveys, $questionCategories): array {
                $values = $surveys
                    ->map(fn (SurveiTamu $survey): mixed => data_get($survey->jawaban_survei, $question['key']))
                    ->filter(fn (mixed $value): bool => is_numeric($value))
                    ->map(fn (mixed $value): int => (int) $value)
                    ->values();

                $categoryKey = $questionCategories[$question['key']] ?? 'service';

                return [
                    'key' => $question['key'],
                    'label' => 'Soal ' . str_replace('q', '', $question['key']),
                    'category_key' => $categoryKey,
                    'category_label' => KunjunganTamu::KATEGORI_SURVEI[$categoryKey] ?? $categoryKey,
                    'question' => $question['question'],
                    'average' => $values->isEmpty() ? null : round($values->avg(), 1),
                    'responses' => $values->count(),
                ];
            })
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function filters(FilterKunjunganTamuRequest $request): array
    {
        return [
            'start_month' => $request->input('start_month'),
            'end_month' => $request->input('end_month'),
            'completion_status' => $request->input('completion_status', 'all'),
            'guest_name' => $request->input('guest_name'),
        ];
    }

    private function averageFor(Collection $surveys, string $field): ?float
    {
        $values = $surveys
            ->pluck($field)
            ->filter(fn (mixed $value): bool => $value !== null)
            ->map(fn (mixed $value): int => (int) $value);

        if ($values->isEmpty()) {
            return null;
        }

        return round($values->avg(), 1);
    }

    /**
     * @return array<string, string>
     */
    private function questionCategoryMap(): array
    {
        return collect(KunjunganTamu::GRUP_RINGKASAN_SURVEI)
            ->flatMap(fn (array $questionKeys, string $category): Collection => collect($questionKeys)
                ->mapWithKeys(fn (string $questionKey): array => [$questionKey => $category]))
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
}
