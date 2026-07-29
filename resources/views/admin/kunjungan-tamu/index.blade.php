@extends('layouts.app')

@section('title', 'Admin Buku Tamu')

@php
    $survey = $summary['survey'];
    $keperluanBreakdown = $summary['keperluan_breakdown'];
    $maxPurposeTotal = max(collect($keperluanBreakdown)->max('total') ?? 0, 1);
    $completionRate = $summary['total_visitors'] > 0
        ? round(($summary['completed_total'] / $summary['total_visitors']) * 100)
        : 0;
    $pendingRate = $summary['total_visitors'] > 0
        ? round(($summary['pending_total'] / $summary['total_visitors']) * 100)
        : 0;
    $dominantPurpose = collect($keperluanBreakdown)->sortByDesc('total')->first();
    $latestEntry = $entries->first();
    $currentpageTotal = $entries->count();

    $metricCards = [
        [
            'label' => 'Total Tamu',
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
            'label' => 'Rata-rata Survei',
            'value' => $survey['overall'] !== null ? number_format($survey['overall'], 1) . ' / 5' : '-',
            'accent' => 'text-blue-700',
            'surface' => 'bg-blue-50',
            'action_label' => 'Lihat Hasil Survei',
            'action_href' => route('admin.survey-reports.index', request()->query()),
        ],
    ];

@endphp

@section('content')
    <div class="min-h-screen bg-slate-50">
        <main class="mx-auto max-w-7xl space-y-4 px-4 py-4 sm:px-6 lg:px-8">
            <section class="rounded-[1.6rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-500">Panel Admin</p>
                        <h1 class="mt-1 text-2xl font-bold text-slate-900">Admin Buku Tamu</h1>

                        <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-600">
                            <span class="rounded-full bg-slate-100 px-3 py-1">{{ $summary['period_label'] }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1">{{ $summary['completion_status_label'] }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1">{{ $completionRate }}% selesai</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('admin.kunjungan-tamu.print', request()->query()) }}"
                            target="_blank"
                            rel="noreferrer"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Cetak Data
                        </a>
                        <a
                            href="{{ route('admin.kunjungan-tamu.export', ['format' => 'pdf'] + request()->query()) }}"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Export PDF
                        </a>
                        <a
                            href="{{ route('admin.kunjungan-tamu.export', ['format' => 'excel'] + request()->query()) }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Export Excel
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-bold shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
                                style="background-color: #ef4444; color: white;"
                            >
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            @if (session('status'))
                <div class="rounded-[1.5rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
                    Data filter belum valid. Mohon periksa kembali isian filter.
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
                            <h2 class="text-lg font-bold text-slate-900">Filter Laporan</h2>
                            <p class="mt-1 text-sm text-slate-600">Atur periode dan status data yang ingin ditampilkan.</p>
                        </div>
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Reset Filter
                        </a>
                    </div>

                    <form action="{{ route('admin.dashboard') }}" method="GET" class="mt-5 grid gap-4 lg:grid-cols-[1fr_1fr_1fr_auto]">
                        <div class="space-y-2">
                            <label for="start_month" class="text-sm font-semibold text-slate-700">Bulan Awal</label>
                            <input
                                id="start_month"
                                name="start_month"
                                type="month"
                                value="{{ $filters['start_month'] }}"
                                class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                            >
                            @error('start_month')
                                <p class="text-sm text-rose-600">{{ $Message }}</p>
                            @enderror
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
                            @error('end_month')
                                <p class="text-sm text-rose-600">{{ $Message }}</p>
                            @enderror
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
                            @error('completion_status')
                                <p class="text-sm text-rose-600">{{ $Message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-end gap-2">
                            <a
                                href="{{ route('admin.dashboard') }}"
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

            <section class="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
                <article class="rounded-[2rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Rekap Keperluan</h2>
                            <p class="mt-1 text-sm text-slate-600">Jenis layanan yang paling banyak masuk pada filter aktif.</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
                            {{ number_format(count($keperluanBreakdown)) }} jenis
                        </span>
                    </div>

                    @if ($keperluanBreakdown === [])
                        <div class="mt-5 rounded-[1.5rem] border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500">
                            Belum ada rekap keperluan pada filter yang dipilih.
                        </div>
                    @else
                        <div class="mt-5 space-y-3">
                            @foreach ($keperluanBreakdown as $keperluan)
                                @php
                                    $width = (int) round(($keperluan['total'] / $maxPurposeTotal) * 100);
                                @endphp
                                <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-4 py-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ $keperluan['label'] }}</p>
                                            <p class="mt-1 text-xs text-slate-500">Jumlah data pada filter aktif</p>
                                        </div>
                                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm">
                                            {{ number_format($keperluan['total']) }}
                                        </span>
                                    </div>

                                    <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-200">
                                        <div
                                            class="h-full rounded-full bg-slate-900"
                                            style="width: {{ max($width, 8) }}%;"
                                        ></div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </article>

                <article class="rounded-[2rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Data Tamu</h2>
                            <p class="mt-1 text-sm text-slate-600">Data operasional yang siap dibaca dan ditindaklanjuti.</p>
                        </div>

                        <div class="flex flex-wrap gap-2 text-xs font-semibold">
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">
                                {{ number_format($summary['completed_total']) }} selesai
                            </span>
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-amber-700">
                                {{ number_format($summary['pending_total']) }} proses
                            </span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">
                                {{ number_format($currentpageTotal) }} baris
                            </span>
                        </div>
                    </div>

                    <div class="mt-5 overflow-hidden rounded-[1.5rem] border border-slate-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                        <th class="px-4 py-3">No</th>
                                        <th class="px-4 py-3">Tamu</th>
                                        <th class="px-4 py-3">Keperluan</th>
                                        <th class="px-4 py-3">Survei</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                    @forelse ($entries as $entry)
                                        <tr class="align-top hover:bg-slate-50/70">
                                            <td class="px-4 py-4 font-semibold text-slate-900">
                                                {{ ($entries->firstItem() ?? 1) + $loop->index }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <p class="font-semibold text-slate-900">{{ $entry->nama }}</p>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    {{ $entry->waktu_kunjungan->translatedFormat('d M Y, H:i') }} WIB
                                                </p>
                                                <p class="mt-1 text-xs text-slate-500">NIK: {{ $entry->nik }}</p>
                                                <p class="mt-1 text-xs text-slate-500">Telp: {{ $entry->nomor_telepon }}</p>
                                                <p class="mt-1 text-xs text-slate-400">{{ $entry->petugas?->email ?? 'Tanpa akun' }}</p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <p class="font-semibold text-slate-900">{{ $entry->keperluan_label }}</p>
                                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                                    {{ filled($entry->detail_keperluan) ? $entry->detail_keperluan : '-' }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <p class="font-semibold text-slate-900">
                                                    {{ $entry->average_rating !== null ? number_format($entry->average_rating, 1) . ' / 5' : '-' }}
                                                </p>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    P: {{ $entry->nilai_pelayanan ?? '-' }} |
                                                    K: {{ $entry->nilai_kecepatan ?? '-' }} |
                                                    F: {{ $entry->nilai_fasilitas ?? '-' }}
                                                </p>
                                                <p class="mt-1 text-xs text-slate-400">
                                                    {{ filled($entry->saran) ? 'Ada saran' : 'Tanpa saran' }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $entry->status_selesai ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                    {{ $entry->status_label }}
                                                </span>
                                                <p class="mt-2 text-xs text-slate-500">Validator: {{ $entry->validator?->name ?? '-' }}</p>
                                                <p class="mt-1 text-xs text-slate-400">
                                                    {{ $entry->waktu_divalidasi?->translatedFormat('d M Y, H:i') ?? '-' }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="flex flex-col gap-2">
                                                    <a
                                                        href="{{ route('admin.kunjungan-tamu.receipt', $entry) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-800"
                                                    >
                                                        Cetak Bukti
                                                    </a>
                                                    <a
                                                        href="{{ route('admin.kunjungan-tamu.edit', $entry) }}"
                                                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                                                    >
                                                        Edit
                                                    </a>
                                                    <form
                                                        action="{{ route('admin.kunjungan-tamu.destroy', $entry) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="submit"
                                                            class="inline-flex w-full items-center justify-center rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100"
                                                        >
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                                                Belum ada data tamu pada filter yang dipilih.
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
