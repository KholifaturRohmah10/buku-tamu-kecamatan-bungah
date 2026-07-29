@extends('layouts.app')

@section('title', 'Rekap Survey ' . $panel['label'])

@php
    $survey = $summary['survey'];
    $questionBreakdown = $summary['question_breakdown'];
    $keperluanBreakdown = $summary['keperluan_breakdown'];
    $latestSurvey = $surveys->first();
    $currentpageTotal = $surveys->count();
    $maxPurposeTotal = max(collect($keperluanBreakdown)->max('total') ?? 0, 1);

    $metricCards = [
        [
            'label' => 'Total Survey',
            'value' => number_format($summary['total_surveys']),
            'color' => '#1e40af', // blue-800
            'bg' => 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)', // blue-50 to blue-100
            'border' => '#bfdbfe' // blue-200
        ],
        [
            'label' => 'Berakun',
            'value' => number_format($summary['respondent_total']),
            'color' => '#065f46', // emerald-800
            'bg' => 'linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%)', // emerald-50 to emerald-100
            'border' => '#a7f3d0' // emerald-200
        ],
        [
            'label' => 'Ada Saran',
            'value' => number_format($summary['saran_total']),
            'color' => '#92400e', // amber-800
            'bg' => 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)', // amber-50 to amber-100
            'border' => '#fde68a' // amber-200
        ],
        [
            'label' => 'Ada Kritik',
            'value' => number_format($summary['kritik_total']),
            'color' => '#be123c', // rose-700
            'bg' => 'linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%)', // rose-50 to rose-100
            'border' => '#fecdd3' // rose-200
        ],
        [
            'label' => 'Rata-rata Penilaian',
            'value' => $survey['overall'] !== null ? number_format($survey['overall'], 1) . ' / 5' : '-',
            'color' => '#4c1d95', // violet-800
            'bg' => 'linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%)', // violet-50 to violet-100
            'border' => '#ddd6fe' // violet-200
        ],
    ];

    $aspectCards = [
        [
            'label' => 'Pelayanan',
            'formatted' => $survey['service'] !== null ? number_format($survey['service'], 1) : '-',
            'bg' => 'linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%)', // rose-50 to rose-100
            'color' => '#be123c', // rose-700
            'border' => '#fecdd3' // rose-200
        ],
        [
            'label' => 'Kecepatan',
            'formatted' => $survey['speed'] !== null ? number_format($survey['speed'], 1) : '-',
            'bg' => 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)', // green-50 to green-100
            'color' => '#15803d', // green-700
            'border' => '#bbf7d0' // green-200
        ],
        [
            'label' => 'Fasilitas',
            'formatted' => $survey['facility'] !== null ? number_format($survey['facility'], 1) : '-',
            'bg' => 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%)', // sky-50 to sky-100
            'color' => '#0369a1', // sky-700
            'border' => '#bae6fd' // sky-200
        ],
    ];
@endphp

@section('content')
    {{-- Soft pastel background for the entire page --}}
    <div class="min-h-screen pb-12" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);">
        <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            {{-- Header Actions with vibrant gradient --}}
            <section class="flex flex-col gap-4 rounded-[2rem] p-6 shadow-md sm:flex-row sm:items-center sm:justify-between" style="background: linear-gradient(to right, #ffffff, #f8fafc); border: 1px solid #e2e8f0;">
                <div>
                    <h1 class="text-3xl font-extrabold" style="color: #0f172a;">Laporan Survei & Kepuasan</h1>
                    <p class="mt-2 text-sm font-medium" style="color: #64748b;">Pantau indeks kepuasan masyarakat dengan visualisasi yang menarik dan cerah.</p>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ route($panel['dashboard_route']) }}"
                        class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-bold shadow-sm transition-transform hover:-translate-y-0.5"
                        style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1;"
                    >
                        Kembali
                    </a>
                    <a
                        href="{{ route($panel['route_prefix'] . '.print', request()->query()) }}"
                        target="_blank"
                        rel="noreferrer"
                        class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-transform hover:-translate-y-0.5"
                        style="background: linear-gradient(to right, #3b82f6, #2563eb);"
                    >
                        Cetak Laporan
                    </a>
                    <a
                        href="{{ route($panel['route_prefix'] . '.export', ['format' => 'excel'] + request()->query()) }}"
                        class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-transform hover:-translate-y-0.5"
                        style="background: linear-gradient(to right, #10b981, #059669);"
                    >
                        Export Excel
                    </a>
                </div>
            </section>

            @if ($errors->any())
                <div class="rounded-2xl px-5 py-4 text-sm font-semibold shadow-sm" style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c;">
                    Filter rekap survey belum valid. Mohon periksa kembali.
                </div>
            @endif

            {{-- Filter Form --}}
            <section class="rounded-[2rem] p-6 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0;">
                <form action="{{ route($panel['route_prefix'] . '.index') }}" method="GET" class="flex flex-col gap-4 md:flex-row md:items-end md:flex-wrap lg:flex-nowrap">
                    <div class="flex-1 space-y-2 min-w-[150px]">
                        <label for="start_month" class="text-sm font-bold" style="color: #334155;">Dari Bulan</label>
                        <input id="start_month" name="start_month" type="month" value="{{ $filters['start_month'] }}" class="w-full rounded-xl px-4 py-3 text-sm font-medium outline-none transition focus:ring-4" style="background: #f8fafc; border: 1px solid #cbd5e1; color: #0f172a; --tw-ring-color: #e2e8f0;">
                    </div>
                    <div class="flex-1 space-y-2 min-w-[150px]">
                        <label for="end_month" class="text-sm font-bold" style="color: #334155;">Sampai Bulan</label>
                        <input id="end_month" name="end_month" type="month" value="{{ $filters['end_month'] }}" class="w-full rounded-xl px-4 py-3 text-sm font-medium outline-none transition focus:ring-4" style="background: #f8fafc; border: 1px solid #cbd5e1; color: #0f172a; --tw-ring-color: #e2e8f0;">
                    </div>
                    <div class="flex-1 space-y-2 min-w-[150px]">
                        <label for="completion_status" class="text-sm font-bold" style="color: #334155;">Status Penyelesaian</label>
                        <select id="completion_status" name="completion_status" class="w-full rounded-xl px-4 py-3 text-sm font-medium outline-none transition focus:ring-4" style="background: #f8fafc; border: 1px solid #cbd5e1; color: #0f172a; --tw-ring-color: #e2e8f0;">
                            <option value="all" @selected($filters['completion_status'] === 'all')>Semua</option>
                            <option value="pending" @selected($filters['completion_status'] === 'pending')>Belum Selesai</option>
                            <option value="completed" @selected($filters['completion_status'] === 'completed')>Sudah Selesai</option>
                        </select>
                    </div>
                    <div class="flex-1 space-y-2 min-w-[150px]">
                        <label for="guest_name" class="text-sm font-bold" style="color: #334155;">Cari Nama Tamu</label>
                        <input id="guest_name" name="guest_name" type="text" value="{{ $filters['guest_name'] }}" placeholder="Masukkan nama..." class="w-full rounded-xl px-4 py-3 text-sm font-medium outline-none transition focus:ring-4" style="background: #f8fafc; border: 1px solid #cbd5e1; color: #0f172a; --tw-ring-color: #e2e8f0;">
                    </div>
                    <div class="w-full md:w-auto mt-2 md:mt-0 flex flex-col md:flex-row gap-2">
                        <a href="{{ route($panel['route_prefix'] . '.index') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 shadow-sm transition-transform hover:-translate-y-0.5 md:w-auto hover:bg-slate-50">
                            Reset
                        </a>
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl px-6 py-3 text-sm font-bold text-white shadow-sm transition-transform hover:-translate-y-0.5 md:w-auto" style="background: linear-gradient(to right, #475569, #334155);">
                            Terapkan Filter
                        </button>
                    </div>
                </form>
            </section>

            {{-- Summary Metrics --}}
            <section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($metricCards as $card)
                    <article class="rounded-[2rem] p-6 shadow-sm transition-transform hover:-translate-y-1" style="background: {{ $card['bg'] }}; border: 1px solid {{ $card['border'] }};">
                        <div class="flex flex-col h-full justify-between">
                            <p class="text-xs font-bold uppercase tracking-wider" style="color: {{ $card['color'] }}; opacity: 0.8;">{{ $card['label'] }}</p>
                            <p class="mt-4 text-4xl font-extrabold" style="color: {{ $card['color'] }};">{{ $card['value'] }}</p>
                        </div>
                    </article>
                @endforeach
            </section>

            {{-- Aspek Penilaian & Keperluan --}}
            <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <article class="rounded-[2rem] p-6 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0;">
                    <h2 class="text-xl font-extrabold" style="color: #0f172a;">Skor Penilaian Aspek</h2>
                    <p class="mt-1 text-sm font-medium" style="color: #64748b;">Rincian nilai rata-rata tiap komponen layanan.</p>
                    
                    <div class="mt-6 grid grid-cols-3 gap-5 text-center">
                        @foreach ($aspectCards as $card)
                            <div class="rounded-2xl p-5 shadow-sm transition-transform hover:scale-105" style="background: {{ $card['bg'] }}; border: 1px solid {{ $card['border'] }};">
                                <p class="text-sm font-bold uppercase tracking-wide" style="color: {{ $card['color'] }}; opacity: 0.9;">{{ $card['label'] }}</p>
                                <p class="mt-3 text-3xl font-black" style="color: {{ $card['color'] }};">{{ $card['formatted'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="rounded-[2rem] p-6 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0;">
                    <h2 class="text-xl font-extrabold" style="color: #0f172a;">Sumber Keperluan Kunjungan</h2>
                    <p class="mt-1 text-sm font-medium" style="color: #64748b;">Distribusi survei berdasarkan layanan.</p>

                    @if (empty($keperluanBreakdown))
                        <div class="mt-6 flex h-28 items-center justify-center rounded-2xl border border-dashed" style="background: #f8fafc; border-color: #cbd5e1; color: #94a3b8;">
                            <span class="text-sm font-semibold">Belum ada data distribusi layanan.</span>
                        </div>
                    @else
                        <div class="mt-6 space-y-4">
                            @foreach ($keperluanBreakdown as $index => $keperluan)
                                @php 
                                    $width = (int) round(($keperluan['total'] / $maxPurposeTotal) * 100); 
                                    // Berikan gradasi warna berbeda untuk setiap bar
                                    $barGradients = [
                                        'linear-gradient(to right, #fbbf24, #f59e0b)',
                                        'linear-gradient(to right, #34d399, #10b981)',
                                        'linear-gradient(to right, #60a5fa, #3b82f6)',
                                        'linear-gradient(to right, #f472b6, #ec4899)',
                                        'linear-gradient(to right, #a78bfa, #8b5cf6)'
                                    ];
                                    $bgBar = $barGradients[$index % count($barGradients)];
                                @endphp
                                <div class="flex items-center gap-4">
                                    <div class="w-32 truncate text-sm font-bold" style="color: #475569;" title="{{ $keperluan['label'] }}">{{ $keperluan['label'] }}</div>
                                    <div class="flex-1 h-3.5 rounded-full" style="background: #f1f5f9; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
                                        <div class="h-full rounded-full transition-all duration-1000" style="width: {{ max($width, 3) }}%; background: {{ $bgBar }}; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
                                    </div>
                                    <div class="w-10 text-right text-sm font-extrabold" style="color: #0f172a;">{{ number_format($keperluan['total']) }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
            </section>

            {{-- Tabel Data --}}
            <section class="rounded-[2rem] p-6 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0;">
                <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold" style="color: #0f172a;">Detail Survei Pengunjung</h2>
                        <p class="mt-1 text-sm font-medium" style="color: #64748b;">Daftar respon survei beserta saran yang masuk.</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl" style="border: 1px solid #e2e8f0;">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y" style="border-color: #f1f5f9;">
                            <thead style="background: #f8fafc;">
                                <tr class="text-left text-xs font-bold uppercase tracking-wider" style="color: #64748b;">
                                    <th class="px-5 py-4">Waktu</th>
                                    <th class="px-5 py-4">Pengunjung</th>
                                    <th class="px-5 py-4">Keperluan</th>
                                    <th class="px-5 py-4">Rata-rata</th>
                                    <th class="px-5 py-4">Kritik</th>
                                    <th class="px-5 py-4">Saran</th>
                                    <th class="px-5 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" style="border-color: #f1f5f9; background: #ffffff;">
                                @forelse ($surveys as $surveyRow)
                                    <tr class="align-top transition-colors hover:bg-slate-50">
                                        <td class="px-5 py-4">
                                            <p class="text-sm font-bold" style="color: #0f172a;">{{ $surveyRow->waktu_dikirim?->translatedFormat('d M Y') ?? '-' }}</p>
                                            <p class="mt-1 text-xs font-medium" style="color: #64748b;">{{ $surveyRow->waktu_dikirim?->translatedFormat('H:i') ?? '-' }}</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="text-sm font-bold" style="color: #0f172a;">{{ $surveyRow->pengguna?->name ?? $surveyRow->kunjunganTamu?->nama ?? 'Tanpa akun' }}</p>
                                            <p class="mt-1 text-xs font-medium" style="color: #64748b;">{{ $surveyRow->kunjunganTamu?->nomor_telepon ?? '-' }}</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold" style="background: #f1f5f9; color: #475569;">
                                                {{ $surveyRow->kunjunganTamu?->keperluan_label ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            @if ($surveyRow->average_rating !== null)
                                                <span class="inline-flex items-center justify-center rounded-lg px-3 py-1 text-sm font-extrabold shadow-sm" style="background: linear-gradient(to right, #4f46e5, #6366f1); color: #ffffff;">
                                                    <svg class="mr-1 h-3.5 w-3.5 text-yellow-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 20">
                                                        <path d="M20.924 7.625a1.523 1.523 0 0 0-1.238-1.044l-5.051-.734-2.259-4.577a1.534 1.534 0 0 0-2.752 0L7.365 5.847l-5.051.734A1.535 1.535 0 0 0 1.463 9.2l3.656 3.563-.863 5.031a1.532 1.532 0 0 0 2.226 1.616L11 17.033l4.518 2.375a1.534 1.534 0 0 0 2.226-1.617l-.863-5.03L20.537 9.2a1.523 1.523 0 0 0 .387-1.575Z"/>
                                                    </svg>
                                                    {{ number_format($surveyRow->average_rating, 1) }}
                                                </span>
                                            @else
                                                <span class="text-sm font-bold text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            @if (filled($surveyRow->kritik))
                                                <div class="rounded-xl p-3 text-sm font-medium" style="background: #fff1f2; border: 1px solid #fecdd3; color: #be123c;">
                                                    {{ $surveyRow->kritik }}
                                                </div>
                                            @else
                                                <span class="text-sm italic" style="color: #94a3b8;">Tidak ada kritik</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            @if (filled($surveyRow->saran))
                                                <div class="rounded-xl p-3 text-sm font-medium" style="background: #f8fafc; border: 1px solid #e2e8f0; color: #475569;">
                                                    {{ $surveyRow->saran }}
                                                </div>
                                            @else
                                                <span class="text-sm italic" style="color: #94a3b8;">Tidak ada saran</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-center align-middle">
                                            <a
                                                href="{{ route($panel['route_prefix'] . '.show', ['survey' => $surveyRow->id] + request()->query()) }}"
                                                class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-xs font-bold text-white shadow-sm transition-transform hover:-translate-y-0.5"
                                                style="background: linear-gradient(to right, #3b82f6, #2563eb);"
                                            >
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 py-10 text-center text-sm font-medium" style="color: #94a3b8;">
                                            Belum ada data detail survei yang sesuai filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mt-6">
                    {{ $surveys->links() }}
                </div>
            </section>
        </main>
    </div>
@endsection
