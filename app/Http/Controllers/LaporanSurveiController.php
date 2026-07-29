<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterKunjunganTamuRequest;
use App\Models\SurveiTamu;
use App\Support\LayananLaporanSurvei;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanSurveiController extends Controller
{
    public function __construct(
        private LayananLaporanSurvei $surveyReportService
    ) {
    }

    public function index(FilterKunjunganTamuRequest $request): View
    {
        return view('reports.surveys.index', [
            ...$this->surveyReportService->indexPayload($request),
            'panel' => $this->panelContext($request),
        ]);
    }

    public function print(FilterKunjunganTamuRequest $request): View
    {
        return view('reports.surveys.print', [
            ...$this->surveyReportService->reportPayload($request),
            'panel' => $this->panelContext($request),
            'showActions' => true,
        ]);
    }

    public function show(Request $request, SurveiTamu $survey): View
    {
        $survey->loadMissing(['pengguna', 'kunjunganTamu.validator']);

        return view('reports.surveys.show', [
            'survey' => $survey,
            'panel' => $this->panelContext($request),
        ]);
    }

    public function printSingle(Request $request, SurveiTamu $survey): View
    {
        $survey->loadMissing(['pengguna', 'kunjunganTamu.validator']);

        return view('reports.surveys.show-print', [
            'survey' => $survey,
            'panel' => $this->panelContext($request),
            'showActions' => true,
        ]);
    }

    public function export(FilterKunjunganTamuRequest $request, string $format): Response|StreamedResponse
    {
        $panel = $this->panelContext($request);
        $reportPayload = $this->surveyReportService->reportPayload($request);
        $fileBaseName = $this->reportFileBaseName($request, $panel['key']);

        return match ($format) {
            'pdf' => Pdf::loadView('reports.surveys.print', [
                ...$reportPayload,
                'panel' => $panel,
                'showActions' => false,
            ])->setPaper('a4', 'landscape')->download($fileBaseName . '.pdf'),
            'excel' => $this->exportExcel($reportPayload, $panel, $fileBaseName),
            default => abort(404),
        };
    }

    /**
     * @return array{key: string, label: string, route_prefix: string, dashboard_route: string}
     */
    private function panelContext(Request $request): array
    {
        $panelKey = $request->route()->defaults['panel'] ?? 'admin';

        return [
            'key' => $panelKey,
            'label' => $panelKey === 'validator' ? 'Validator' : 'Admin',
            'route_prefix' => $panelKey === 'validator' ? 'validator.survey-reports' : 'admin.survey-reports',
            'dashboard_route' => $panelKey === 'validator' ? 'validator.dashboard' : 'admin.dashboard',
        ];
    }

    /**
     * @param  array{surveys: \Illuminate\Support\Collection<int, \App\Models\SurveiTamu>, summary: array<string, mixed>, filters: array<string, string|null>}  $reportPayload
     * @param  array{key: string, label: string, route_prefix: string, dashboard_route: string}  $panel
     */
    private function exportExcel(array $reportPayload, array $panel, string $fileBaseName): StreamedResponse
    {
        $html = view('reports.surveys.excel', [
            ...$reportPayload,
            'panel' => $panel,
        ])->render();

        return response()->streamDownload(function () use ($html): void {
            echo "\xEF\xBB\xBF";
            echo $html;
        }, $fileBaseName . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    private function reportFileBaseName(FilterKunjunganTamuRequest $request, string $panelKey): string
    {
        $status = $request->input('completion_status', 'all');
        $startMonth = $request->input('start_month');
        $endMonth = $request->input('end_month');

        $periodSegment = $startMonth && $endMonth
            ? sprintf('%s_sampai_%s', $startMonth, $endMonth)
            : 'semua_periode';

        return sprintf(
            'laporan-rekap-survey_%s_%s_%s_%s',
            $panelKey,
            $periodSegment,
            $status,
            now()->format('Ymd_His')
        );
    }
}
