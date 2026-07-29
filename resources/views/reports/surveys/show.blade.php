@extends('layouts.app')

@section('title', 'Detail Survey Tamu')

@php
    $scoreOptions = \App\Models\KunjunganTamu::OPSI_SKOR_SURVEI;
    $questions = \App\Models\KunjunganTamu::PERTANYAAN_SURVEI;
    $categories = \App\Models\KunjunganTamu::KATEGORI_SURVEI;
    $summaryGroups = \App\Models\KunjunganTamu::GRUP_RINGKASAN_SURVEI;

    $guestName = $survey->pengguna?->name ?? $survey->kunjunganTamu?->nama ?? 'Tanpa Nama';
    $guestPhone = $survey->kunjunganTamu?->nomor_telepon ?? '-';
    $keperluan = $survey->kunjunganTamu?->keperluan_label ?? '-';
    
    function getCategoryForQuestion($qKey, $summaryGroups) {
        foreach ($summaryGroups as $category => $keys) {
            if (in_array($qKey, $keys)) return $category;
        }
        return 'service';
    }
@endphp

@section('content')
    <div class="min-h-screen pb-12" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);">
        <main class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            {{-- Header Actions --}}
            <section class="flex flex-col gap-4 rounded-[2rem] p-6 shadow-md sm:flex-row sm:items-center sm:justify-between" style="background: linear-gradient(to right, #ffffff, #f8fafc); border: 1px solid #e2e8f0;">
                <div>
                    <h1 class="text-3xl font-extrabold" style="color: #0f172a;">Detail Survey Tamu</h1>
                    <p class="mt-2 text-sm font-medium" style="color: #64748b;">Melihat rincian jawaban survey dan penilaian dari <strong>{{ $guestName }}</strong>.</p>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ route($panel['route_prefix'] . '.index', request()->query()) }}"
                        class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-bold shadow-sm transition-transform hover:-translate-y-0.5"
                        style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1;"
                    >
                        Kembali
                    </a>
                    <a
                        href="{{ route($panel['route_prefix'] . '.print-single', ['survey' => $survey->id] + request()->query()) }}"
                        target="_blank"
                        rel="noreferrer"
                        class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-transform hover:-translate-y-0.5"
                        style="background: linear-gradient(to right, #3b82f6, #2563eb);"
                    >
                        Cetak Survey Ini
                    </a>
                </div>
            </section>

            {{-- Info Tamu & Rata-rata --}}
            <section class="grid gap-6 sm:grid-cols-2">
                <article class="rounded-[2rem] p-6 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0;">
                    <h2 class="text-lg font-extrabold mb-4" style="color: #0f172a;">Informasi Pengunjung</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between border-b pb-2 border-slate-100">
                            <span class="font-semibold text-slate-500">Nama Lengkap</span>
                            <span class="font-bold text-slate-900">{{ $guestName }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2 border-slate-100">
                            <span class="font-semibold text-slate-500">Nomor Telepon</span>
                            <span class="font-bold text-slate-900">{{ $guestPhone }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2 border-slate-100">
                            <span class="font-semibold text-slate-500">Keperluan</span>
                            <span class="font-bold text-slate-900">{{ $keperluan }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2 border-slate-100">
                            <span class="font-semibold text-slate-500">Waktu Submit</span>
                            <span class="font-bold text-slate-900">{{ $survey->waktu_dikirim?->translatedFormat('d F Y, H:i') ?? '-' }}</span>
                        </div>
                    </div>
                </article>

                <article class="rounded-[2rem] p-6 shadow-sm flex flex-col justify-center items-center text-center" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border: 1px solid #ddd6fe;">
                    <p class="text-sm font-bold uppercase tracking-wider text-violet-800 opacity-80 mb-2">Nilai Rata-rata Keseluruhan</p>
                    <div class="text-6xl font-black text-violet-900">
                        {{ $survey->average_rating !== null ? number_format($survey->average_rating, 1) : '-' }}
                    </div>
                    <div class="mt-4 flex gap-4 text-sm font-bold text-violet-800">
                        <span>Pelayanan: {{ $survey->nilai_pelayanan !== null ? number_format($survey->nilai_pelayanan, 1) : '-' }}</span>
                        <span>&bull;</span>
                        <span>Kecepatan: {{ $survey->nilai_kecepatan !== null ? number_format($survey->nilai_kecepatan, 1) : '-' }}</span>
                        <span>&bull;</span>
                        <span>Fasilitas: {{ $survey->nilai_fasilitas !== null ? number_format($survey->nilai_fasilitas, 1) : '-' }}</span>
                    </div>
                </article>
            </section>

            {{-- Rincian Jawaban Survey --}}
            <section class="rounded-[2rem] p-6 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0;">
                <h2 class="text-xl font-extrabold mb-6" style="color: #0f172a;">Rincian Jawaban Survey</h2>
                
                <div class="space-y-6">
                    @foreach ($questions as $index => $q)
                        @php
                            $answerValue = data_get($survey->jawaban_survei, $q['key']);
                            $answerLabel = is_numeric($answerValue) ? ($scoreOptions[(int)$answerValue] ?? $answerValue) : '-';
                            $catKey = getCategoryForQuestion($q['key'], $summaryGroups);
                            $catName = $categories[$catKey] ?? 'Umum';
                            
                            $badgeColor = match((int)$answerValue) {
                                3 => 'background: #dcfce7; color: #166534;', // green
                                2 => 'background: #fef9c3; color: #854d0e;', // yellow
                                1 => 'background: #fee2e2; color: #991b1b;', // red
                                default => 'background: #f1f5f9; color: #475569;' // gray
                            };
                        @endphp
                        <div class="flex flex-col sm:flex-row gap-4 border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-2xl text-lg font-black bg-slate-50 text-slate-400">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <div class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1">{{ $catName }}</div>
                                <p class="text-sm font-semibold text-slate-800">{{ $q['question'] }}</p>
                            </div>
                            <div class="flex-shrink-0 sm:w-32 sm:text-right flex items-center sm:justify-end">
                                <span class="px-3 py-1.5 rounded-xl text-sm font-bold shadow-sm" style="{{ $badgeColor }}">
                                    {{ $answerLabel }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Kritik & Saran --}}
            <section class="grid gap-6 sm:grid-cols-2">
                <article class="rounded-[2rem] p-6 shadow-sm" style="background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%); border: 1px solid #fecdd3;">
                    <h2 class="text-lg font-extrabold mb-3" style="color: #be123c;">Kritik</h2>
                    @if (filled($survey->kritik))
                        <p class="text-base font-medium text-rose-900 leading-relaxed italic">"{{ $survey->kritik }}"</p>
                    @else
                        <p class="text-sm font-medium text-rose-700 opacity-70 italic">Tidak ada kritik yang diberikan oleh tamu ini.</p>
                    @endif
                </article>

                <article class="rounded-[2rem] p-6 shadow-sm" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #fde68a;">
                    <h2 class="text-lg font-extrabold mb-3" style="color: #92400e;">Saran</h2>
                    @if (filled($survey->saran))
                        <p class="text-base font-medium text-amber-900 leading-relaxed italic">"{{ $survey->saran }}"</p>
                    @else
                        <p class="text-sm font-medium text-amber-700 opacity-70 italic">Tidak ada saran yang diberikan oleh tamu ini.</p>
                    @endif
                </article>
            </section>

        </main>
    </div>
@endsection
