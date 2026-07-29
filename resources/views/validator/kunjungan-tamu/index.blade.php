@extends('layouts.app')

@section('title', 'Dashboard Validator')

@php
    $completionRate = $summary['total_visitors'] > 0
        ? round(($summary['completed_total'] / $summary['total_visitors']) * 100)
        : 0;
    $pendingRate = $summary['total_visitors'] > 0
        ? round(($summary['pending_total'] / $summary['total_visitors']) * 100)
        : 0;
    $latestEntry = $entries->first();
    $currentpageTotal = $entries->count();
    $periodLabel = ($filters['start_month'] && $filters['end_month'])
        ? sprintf(
            '%s - %s',
            \Illuminate\Support\Carbon::createFromFormat('Y-m', $filters['start_month'])->translatedFormat('F Y'),
            \Illuminate\Support\Carbon::createFromFormat('Y-m', $filters['end_month'])->translatedFormat('F Y')
        )
        : 'Semua periode';
    $statusLabel = match ($filters['completion_status']) {
        'completed' => 'Sudah selesai',
        'pending' => 'Belum selesai',
        default => 'Semua status',
    };

    $metricCards = [
        [
            'label' => 'Total Data',
            'value' => number_format($summary['total_visitors']),
            'accent' => 'text-slate-900',
            'surface' => 'bg-slate-100',
        ],
        [
            'label' => 'Belum Selesai',
            'value' => number_format($summary['pending_total']),
            'accent' => 'text-amber-700',
            'surface' => 'bg-amber-50',
        ],
        [
            'label' => 'Sudah Selesai',
            'value' => number_format($summary['completed_total']),
            'accent' => 'text-emerald-700',
            'surface' => 'bg-emerald-50',
        ],
        [
            'label' => 'Progress Validasi',
            'value' => $completionRate . '%',
            'accent' => 'text-blue-700',
            'surface' => 'bg-blue-50',
        ],
    ];
@endphp

@section('content')
    <div class="min-h-screen bg-slate-50">
        <main class="mx-auto max-w-7xl space-y-4 px-4 py-4 sm:px-6 lg:px-8">
            <section class="rounded-[1.6rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-500">Panel Validator</p>
                        <h1 class="mt-1 text-2xl font-bold text-slate-900">Dashboard Validator</h1>

                        <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-600">
                            <span class="rounded-full bg-slate-100 px-3 py-1">{{ $periodLabel }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1">{{ $statusLabel }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1">{{ $completionRate }}% selesai</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('validator.kunjungan-tamu.print', request()->query()) }}"
                            target="_blank"
                            rel="noreferrer"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Cetak Laporan
                        </a>
                        <a
                            href="{{ route('validator.kunjungan-tamu.export', ['format' => 'pdf'] + request()->query()) }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Export PDF
                        </a>
                        <a
                            href="{{ route('validator.kunjungan-tamu.export', ['format' => 'excel'] + request()->query()) }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Export Excel
                        </a>
                        <button
                            type="submit"
                            form="validator-logout-form"
                            class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-bold shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
                            style="background-color: #ef4444; color: white;"
                        >
                            Logout
                        </button>
                    </div>
                </div>

                <form id="validator-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </section>

            @if (session('status'))
                <div class="rounded-[1.5rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
                    Mohon cek kembali filter atau status yang dikirim.
                </div>
            @endif

            <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($metricCards as $card)
                    <article class="rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <div class="mt-4 rounded-[1.25rem] px-4 py-4 {{ $card['surface'] }}">
                            <p class="text-3xl font-bold {{ $card['accent'] }}">{{ $card['value'] }}</p>
                        </div>
                        @if (isset($card['action_href'], $card['action_label']))
                            <a
                                href="{{ $card['action_href'] }}"
                                class="mt-3 inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-800"
                            >
                                {{ $card['action_label'] }}
                            </a>
                        @endif
                    </article>
                @endforeach
            </section>

            <section>
                <article class="rounded-[2rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Filter Validasi</h2>
                            <p class="mt-1 text-sm text-slate-600">Atur periode dan status data yang ingin ditindaklanjuti.</p>
                        </div>
                        <a
                            href="{{ route('validator.dashboard') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Reset Filter
                        </a>
                    </div>

                    <form action="{{ route('validator.dashboard') }}" method="GET" class="mt-5 grid gap-4 lg:grid-cols-[1fr_1fr_1fr_auto]">
                        <div class="space-y-2">
                            <label for="start_month" class="text-sm font-semibold text-slate-700">Bulan Awal</label>
                            <input
                                id="start_month"
                                name="start_month"
                                type="month"
                                value="{{ $filters['start_month'] }}"
                                class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                            >
                        </div>

                        <div class="space-y-2">
                            <label for="end_month" class="text-sm font-semibold text-slate-700">Bulan Akhir</label>
                            <input
                                id="end_month"
                                name="end_month"
                                type="month"
                                value="{{ $filters['end_month'] }}"
                                class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                            >
                        </div>

                        <div class="space-y-2">
                            <label for="completion_status" class="text-sm font-semibold text-slate-700">Status Proses</label>
                            <select
                                id="completion_status"
                                name="completion_status"
                                class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                            >
                                <option value="all" @selected($filters['completion_status'] === 'all')>Semua status</option>
                                <option value="pending" @selected($filters['completion_status'] === 'pending')>Belum selesai</option>
                                <option value="completed" @selected($filters['completion_status'] === 'completed')>Sudah selesai</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <a
                                href="{{ route('validator.dashboard') }}"
                                class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 lg:w-auto"
                            >
                                Reset
                            </a>
                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 lg:w-auto"
                            >
                                Terapkan
                            </button>
                        </div>
                    </form>
                </article>
            </section>

            <section class="grid gap-4 xl:grid-cols-[0.92fr_1.08fr]">
                <article class="rounded-[2rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Ringkasan Validasi</h2>
                            <p class="mt-1 text-sm text-slate-600">Info singkat untuk membaca antrean aktif.</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
                            {{ number_format($currentpageTotal) }} baris
                        </span>
                    </div>

                    <div class="mt-5 grid gap-3">
                        <div class="rounded-[1.5rem] bg-slate-50 px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Tamu Terbaru</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $latestEntry?->nama ?? 'Belum ada data' }}</p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $latestEntry ? $latestEntry->waktu_kunjungan->translatedFormat('d F Y, H:i') . ' WIB' : 'Data terbaru akan muncul di sini.' }}
                            </p>
                        </div>

                        <div class="rounded-[1.5rem] bg-slate-50 px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Periode Aktif</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $periodLabel }}</p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-[1.5rem] bg-emerald-50 px-4 py-4">
                                <p class="text-sm font-medium text-emerald-700">Selesai</p>
                                <p class="mt-1 text-2xl font-bold text-emerald-800">{{ $completionRate }}%</p>
                            </div>
                            <div class="rounded-[1.5rem] bg-amber-50 px-4 py-4">
                                <p class="text-sm font-medium text-amber-700">Masih proses</p>
                                <p class="mt-1 text-2xl font-bold text-amber-800">{{ $pendingRate }}%</p>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="rounded-[2rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Daftar Proses Tamu</h2>
                            <p class="mt-1 text-sm text-slate-600">Tandai status layanan untuk setiap kebutuhan tamu.</p>
                        </div>

                        <div class="flex flex-wrap gap-2 text-xs font-semibold">
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">
                                {{ number_format($summary['completed_total']) }} selesai
                            </span>
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-amber-700">
                                {{ number_format($summary['pending_total']) }} proses
                            </span>
                        </div>
                    </div>

                    <div class="mt-5 overflow-hidden rounded-[1.5rem] border border-slate-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                        <th class="px-4 py-3">Tanggal</th>
                                        <th class="px-4 py-3">Tamu</th>
                                        <th class="px-4 py-3">Keperluan</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                    @forelse ($entries as $entry)
                                        <tr class="align-top hover:bg-slate-50/70">
                                            <td class="px-4 py-4">
                                                <p class="font-semibold text-slate-900">{{ $entry->waktu_kunjungan->translatedFormat('d M Y') }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $entry->waktu_kunjungan->translatedFormat('H:i') }} WIB</p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <p class="font-semibold text-slate-900">{{ $entry->nama }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $entry->nomor_telepon }}</p>
                                                <p class="mt-1 text-xs text-slate-400">{{ $entry->petugas?->email ?? 'Tanpa akun' }}</p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <p class="font-semibold text-slate-900">{{ $entry->keperluan_label }}</p>
                                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ filled($entry->detail_keperluan) ? $entry->detail_keperluan : '-' }}</p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $entry->status_selesai ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                    {{ $entry->status_label }}
                                                </span>
                                                <p class="mt-2 text-xs text-slate-500">Validator: {{ $entry->validator?->name ?? '-' }}</p>
                                                <p class="mt-1 text-xs text-slate-400">{{ $entry->waktu_divalidasi?->translatedFormat('d M Y, H:i') ?? '-' }}</p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="flex flex-col gap-2">
                                                    <a
                                                        href="{{ route('validator.kunjungan-tamu.receipt', $entry) }}"
                                                        target="_blank"
                                                        class="inline-flex w-full items-center justify-center whitespace-nowrap rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-800"
                                                    >
                                                        Cetak Bukti
                                                    </a>

                                                    @if (!$entry->status_selesai)
                                                        <form action="{{ route('validator.kunjungan-tamu.status', $entry) }}" method="POST" class="w-full">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status_selesai" value="1">
                                                            <button
                                                                type="submit"
                                                                class="inline-flex w-full items-center justify-center whitespace-nowrap rounded-xl bg-emerald-100 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-200"
                                                            >
                                                                Tandai Selesai
                                                            </button>
                                                        </form>

                                                        <form action="{{ route('validator.kunjungan-tamu.status', $entry) }}" method="POST" class="w-full">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status_selesai" value="0">
                                                            <button
                                                                type="submit"
                                                                class="inline-flex w-full items-center justify-center whitespace-nowrap rounded-xl bg-amber-100 px-3 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-200"
                                                            >
                                                                Tandai Belum
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">
                                                Belum ada data tamu untuk filter yang dipilih.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-5">
                        {{ $entries->links() }}
                    </div>
                </article>
            </section>
        </main>
    </div>
@endsection
