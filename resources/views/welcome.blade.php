@extends('layouts.app')

<?php
    $internalLoginErrors = $errors->getBag('internalLogin');
    $oldContext = old('form_context');
    $showInternalModal = request('modal') === 'internal' || $internalLoginErrors->any();
?>


@section('title', 'Portal Layanan - Buku Tamu Kecamatan Bungah')

@php
    $experiencePillars = [
        [
            'number' => '01',
            'title' => 'Pencatatan Digital Terpusat',
            'description' => 'Seluruh riwayat kunjungan dicatat secara sistematis untuk keperluan arsip, pelacakan, dan pelaporan yang akurat.',
            'accent' => 'from-[#fff1e6] to-white',
            'border' => 'border-[#ffd4b8]',
            'badge' => 'bg-[#fff1e6] text-[#9a3412]',
        ],
        [
            'number' => '02',
            'title' => 'Distribusi Layanan Cepat',
            'description' => 'Sistem antrean dan disposisi cerdas mempercepat proses pengarahan tamu menuju bidang layanan yang sesuai.',
            'accent' => 'from-[#ebfffb] to-white',
            'border' => 'border-[#b6f0e6]',
            'badge' => 'bg-[#ebfffb] text-[#115e59]',
        ],
        [
            'number' => '03',
            'title' => 'Keamanan Privasi Data',
            'description' => 'Identitas serta rekam jejak keperluan tamu dikelola ketat dengan standar keamanan data instansi pemerintah.',
            'accent' => 'from-[#eef5ff] to-white',
            'border' => 'border-[#cfe0ff]',
            'badge' => 'bg-[#eef5ff] text-[#1d4ed8]',
        ],
    ];

    $journeySteps = [
        [
            'title' => '1. Registrasi Kunjungan',
            'description' => 'Tamu mendaftarkan diri secara mandiri atau dibantu petugas pada fasilitas portal digital di lobi utama kecamatan.',
        ],
        [
            'title' => '2. Verifikasi & Disposisi',
            'description' => 'Petugas resepsionis memvalidasi data dan meneruskan informasi kunjungan ke bidang yang dituju secara langsung.',
        ],
        [
            'title' => '3. Pelayanan Publik',
            'description' => 'Tamu diarahkan ke meja atau ruangan layanan yang tepat untuk menyelesaikan permohonan atau keperluannya.',
        ],
        [
            'title' => '4. Penilaian Layanan',
            'description' => 'Sebelum pulang, tamu dapat memberikan penilaian (survei) atas kualitas pelayanan sebagai bahan evaluasi instansi.',
        ],
    ];

    $serviceNotes = [
        'Transparan dan Terukur',
        'Responsif Terhadap Aspirasi',
        'Pendataan Terintegrasi',
    ];

    $quickFacts = [
        ['value' => 'Satu Pintu', 'label' => 'Sistem Pelayanan', 'description' => 'Layanan administrasi diintegrasikan dalam satu jalur untuk efisiensi birokrasi.'],
        ['value' => 'Digital', 'label' => 'Pencatatan Data', 'description' => 'Bebas dari risiko kehilangan data berkat arsip riwayat tamu berbasis *cloud*.'],
        ['value' => 'Aman', 'label' => 'Privasi Terjaga', 'description' => 'Perlindungan maksimal atas data pribadi setiap warga yang datang berkunjung.'],
    ];
@endphp

@section('content')
    <div class="relative isolate overflow-hidden bg-[linear-gradient(180deg,_#0f172a_0%,_#12263b_18%,_#f6efe1_18.2%,_#f8f4eb_56%,_#eff7f7_100%)]">
        <div class="landing-grid pointer-events-none absolute inset-0 -z-10 opacity-25"></div>
        <div class="pointer-events-none absolute left-[-8rem] top-16 -z-10 h-72 w-72 rounded-full bg-[#f59e0b]/16 blur-3xl"></div>
        <div class="pointer-events-none absolute right-[-7rem] top-24 -z-10 h-80 w-80 rounded-full bg-[#2dd4bf]/16 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 left-1/2 -z-10 h-72 w-72 -translate-x-1/2 rounded-full bg-[#60a5fa]/14 blur-3xl"></div>

        <main class="mx-auto flex min-h-screen max-w-7xl flex-col gap-8 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <section class="relative overflow-hidden rounded-[2.75rem] border border-white/10 bg-[linear-gradient(135deg,_rgba(15,23,42,0.96)_0%,_rgba(17,40,58,0.94)_42%,_rgba(18,58,69,0.92)_100%)] p-6 text-white shadow-[0_40px_120px_rgba(15,23,42,0.32)] sm:p-8 lg:p-10">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.10),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(245,158,11,0.16),_transparent_24%)]"></div>
                <div class="pointer-events-none absolute right-10 top-10 h-40 w-40 rounded-full border border-white/10"></div>
                <div class="pointer-events-none absolute bottom-10 left-10 h-28 w-28 rounded-full border border-white/8"></div>

                <header class="landing-fade-up relative flex flex-col gap-4 border-b border-white/10 pb-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-[1.35rem] bg-white/10 text-lg font-bold tracking-[0.28em] text-[#f6d7a0] shadow-inner">
                            KB
                        </div>
                        <div>
                            <p class="text-[0.7rem] font-semibold uppercase tracking-[0.34em] text-[#f6d7a0]">Portal Layanan Digital</p>
                            <p class="mt-1 text-sm font-semibold text-white">Kecamatan Bungah, Kabupaten Gresik</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a
                            href="{{ route('kunjungan-tamu.index') }}"
                            class="inline-flex items-center justify-center rounded-full bg-white px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-slate-100"
                        >
                            Isi Buku Tamu
                        </a>
                        <a
                            href="#" data-open-internal-modal
                            class="inline-flex items-center justify-center rounded-full border border-white/15 bg-white/8 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/14"
                        >
                            Akses Internal
                        </a>
                    </div>
                </header>

                @if (session('status'))
                    <div class="landing-fade-up-delay relative mt-6 rounded-[1.6rem] border border-emerald-300/30 bg-emerald-300/10 px-4 py-3 text-sm font-medium text-emerald-100">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="relative mt-8 grid gap-8 xl:grid-cols-[1.08fr_0.92fr] xl:items-center">
                    <div class="landing-fade-up space-y-7">
                        <div class="flex flex-wrap gap-3">
                            <span class="inline-flex rounded-full border border-[#f6d7a0]/30 bg-[#f6d7a0]/10 px-4 py-1.5 text-[0.72rem] font-semibold uppercase tracking-[0.28em] text-[#f6d7a0]">
                                Buku Tamu Digital Kecamatan Bungah
                            </span>
                            <span class="inline-flex rounded-full border border-white/12 bg-white/6 px-4 py-1.5 text-[0.72rem] font-semibold uppercase tracking-[0.28em] text-white/72">
                                Tertib, Efisien, Responsif
                            </span>
                        </div>

                        <div class="space-y-5">
                            <h1 class="font-display max-w-4xl text-4xl leading-[1.02] font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl">
                                Sistem Buku Tamu Terpadu Kecamatan Bungah.
                            </h1>
                            <p class="max-w-3xl text-base leading-8 text-white/76 sm:text-lg">
                                Selamat datang di Portal Layanan Administrasi Kecamatan Bungah. Kami berkomitmen memberikan pelayanan publik yang tertib, transparan, dan profesional melalui digitalisasi pendataan tamu.
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            @foreach ($quickFacts as $fact)
                                <article class="rounded-[1.7rem] border border-white/10 bg-white/7 p-4 backdrop-blur">
                                    <p class="font-display text-3xl font-semibold text-white">{{ $fact['value'] }}</p>
                                    <p class="mt-2 text-sm font-semibold text-white">{{ $fact['label'] }}</p>
                                    <p class="mt-2 text-sm leading-6 text-white/65">{{ $fact['description'] }}</p>
                                </article>
                            @endforeach
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <a
                                href="{{ route('kunjungan-tamu.index') }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#f59e0b_0%,_#f97316_100%)] px-6 py-3.5 text-sm font-semibold text-white shadow-[0_20px_50px_rgba(249,115,22,0.28)] transition hover:-translate-y-0.5 hover:brightness-105"
                            >
                                Isi Buku Tamu
                            </a>
                            <a
                                href="#tentang-bungah"
                                class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/7 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-white/12"
                            >
                                Info Sistem
                            </a>
                        </div>
                    </div>

                    <div class="landing-fade-up-delay-2 relative">
                        <div class="overflow-hidden rounded-[2.4rem] border border-white/12 bg-white/6 shadow-[0_30px_90px_rgba(15,23,42,0.24)]">
                            <div class="relative aspect-[4/4.35] overflow-hidden">
                                <img
                                    src="{{ asset('images/bungah-melirang.jpg') }}"
                                    alt="Kawasan di Kecamatan Bungah, Kabupaten Gresik"
                                    class="h-full w-full object-cover"
                                >
                                <div class="absolute inset-0 bg-[linear-gradient(180deg,_rgba(15,23,42,0.08),_rgba(15,23,42,0.76))]"></div>

                                <div class="absolute left-5 top-5 rounded-[1.4rem] border border-white/15 bg-slate-950/35 px-4 py-3 backdrop-blur">
                                    <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-[#f6d7a0]">Layanan Terpadu</p>
                                    <p class="mt-2 text-sm font-semibold text-white">Kecamatan Bungah</p>
                                </div>

                                <div class="absolute bottom-0 left-0 right-0 p-6">
                                    <p class="text-[0.72rem] font-semibold uppercase tracking-[0.3em] text-white/70">Pemerintah Kabupaten Gresik</p>
                                    <h2 class="font-display mt-2 text-2xl font-semibold text-white sm:text-3xl">Pusat Layanan Masyarakat.</h2>
                                    <p class="mt-3 max-w-lg text-sm leading-6 text-white/72 sm:leading-7">
                                        Berdedikasi untuk memberikan pelayanan terbaik bagi warga dengan sistem yang cepat, terpadu, dan akuntabel.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:mt-0">
                            <div class="rounded-[1.6rem] border border-white/20 bg-white/92 p-4 text-slate-900 shadow-[0_22px_60px_rgba(15,23,42,0.18)] xl:absolute xl:-bottom-6 xl:-left-4 xl:w-52 xl:bg-white/92 xl:p-4 xl:shadow-[0_22px_60px_rgba(15,23,42,0.18)]">
                                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-slate-400">Visi Pelayanan</p>
                                <p class="mt-2 text-lg font-bold text-slate-900">Cepat dan Tepat Sasaran.</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Mengurangi birokrasi berbelit dan memastikan warga mendapat arahan secara langsung.</p>
                            </div>

                            <div class="rounded-[1.6rem] border border-white/12 bg-slate-950/72 p-4 text-white shadow-[0_22px_60px_rgba(15,23,42,0.22)] backdrop-blur xl:absolute xl:-right-4 xl:top-10 xl:w-48">
                                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-[#f6d7a0]/82">Komitmen Kami</p>
                                <p class="mt-2 text-lg font-bold">Kenyamanan Publik</p>
                                <p class="mt-2 text-sm leading-6 text-white/72">Kepuasan masyarakat adalah prioritas utama setiap petugas kami di lapangan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="landing-fade-up-delay rounded-[2.2rem] border border-white/70 bg-white/82 px-5 py-5 shadow-[0_24px_70px_rgba(15,23,42,0.06)] backdrop-blur sm:px-6">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Nilai Utama Kami</p>
                        <p class="mt-1 text-sm leading-6 text-slate-500">Kami hadir untuk memberikan standar layanan administrasi yang responsif, tertata, dan terintegrasi.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($serviceNotes as $note)
                            <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-600">
                                {{ $note }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.02fr_0.98fr]">
                <article class="landing-fade-up rounded-[2.4rem] border border-white/70 bg-white/84 p-6 shadow-[0_24px_70px_rgba(15,23,42,0.06)] backdrop-blur sm:p-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-[0.72rem] font-semibold uppercase tracking-[0.32em] text-[#9a3412]">Fitur Utama</p>
                            <h2 class="font-display mt-3 text-3xl leading-tight font-semibold text-slate-950">Inovasi pelayanan yang memudahkan aparatur dan masyarakat.</h2>
                        </div>
                        <span class="rounded-full bg-[#fff1e6] px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-[#9a3412]">
                            Kualitas Layanan
                        </span>
                    </div>

                    <div class="mt-6 grid gap-4">
                        @foreach ($experiencePillars as $pillar)
                            <article class="rounded-[1.9rem] border {{ $pillar['border'] }} bg-[linear-gradient(135deg,_var(--tw-gradient-stops))] {{ $pillar['accent'] }} p-5 shadow-sm">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex items-start gap-4">
                                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.1rem] {{ $pillar['badge'] }} text-sm font-bold shadow-sm">
                                            {{ $pillar['number'] }}
                                        </span>
                                        <div>
                                            <h3 class="text-lg font-bold text-slate-950">{{ $pillar['title'] }}</h3>
                                            <p class="mt-2 text-sm leading-7 text-slate-600">{{ $pillar['description'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </article>

                <article class="landing-fade-up-delay rounded-[2.4rem] border border-white/70 bg-[linear-gradient(180deg,_#fff8f0_0%,_#ffffff_34%,_#f3fbfb_100%)] p-6 shadow-[0_24px_70px_rgba(15,23,42,0.06)] sm:p-8">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-[0.72rem] font-semibold uppercase tracking-[0.32em] text-[#115e59]">Alur Layanan</p>
                            <h2 class="font-display mt-3 text-3xl leading-tight font-semibold text-slate-950">Prosedur operasional standar kunjungan tamu terpadu.</h2>
                        </div>
                        <span class="rounded-full bg-[#ebfffb] px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-[#115e59]">
                            4 Langkah
                        </span>
                    </div>

                    <div class="mt-6 space-y-4">
                        @foreach ($journeySteps as $step)
                            <div class="rounded-[1.7rem] border border-slate-200 bg-white/92 p-5 shadow-sm">
                                <div class="flex gap-4">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-950 text-sm font-bold text-white">
                                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-950">{{ $step['title'] }}</h3>
                                        <p class="mt-2 text-sm leading-7 text-slate-600">{{ $step['description'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            </section>

            <section
                id="tentang-bungah"
                class="grid gap-6 xl:grid-cols-[1fr_1fr]"
            >
                <article class="landing-fade-up overflow-hidden rounded-[2.45rem] border border-white/70 bg-white/84 shadow-[0_24px_70px_rgba(15,23,42,0.06)] backdrop-blur">
                    <div class="relative aspect-[16/10] overflow-hidden">
                        <img
                            src="{{ asset('images/bungah-melirang.jpg') }}"
                            alt="Kawasan di Kecamatan Bungah, Kabupaten Gresik"
                            class="h-full w-full object-cover"
                        >
                        <div class="absolute inset-0 bg-[linear-gradient(180deg,_rgba(15,23,42,0.08),_rgba(15,23,42,0.72))]"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-6 text-white sm:p-8">
                            <p class="text-[0.72rem] font-semibold uppercase tracking-[0.3em] text-white/72">Pemerintah Daerah</p>
                            <h2 class="font-display mt-3 text-3xl font-semibold">Berdedikasi melayani dan terus bergerak maju.</h2>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-white/76">
                                Kecamatan Bungah sebagai bagian integral dari Kabupaten Gresik, senantiasa beradaptasi dengan perkembangan teknologi untuk meningkatkan efisiensi dan transparansi pelayanan administrasi masyarakat.
                            </p>
                        </div>
                    </div>
                </article>

                <article class="landing-fade-up-delay rounded-[2.45rem] border border-white/70 bg-white/84 p-6 shadow-[0_24px_70px_rgba(15,23,42,0.06)] backdrop-blur sm:p-8">
                    <p class="text-[0.72rem] font-semibold uppercase tracking-[0.32em] text-[#1d4ed8]">Standar Pelayanan</p>
                    <h2 class="font-display mt-3 text-3xl leading-tight font-semibold text-slate-950">Menghadirkan pelayanan modern tanpa meninggalkan nilai-nilai lokal.</h2>

                    <div class="mt-5 space-y-4 text-sm leading-8 text-slate-600">
                        <p>
                            Penerapan Buku Tamu Digital dirancang agar petugas dapat mendokumentasikan setiap kunjungan secara terintegrasi, sehingga laporan evaluasi kinerja pelayanan dapat disajikan dengan cepat dan transparan.
                        </p>
                        <p>
                            Melalui sistem ini, instansi diharapkan bisa lebih mudah menindaklanjuti setiap aspirasi, keluhan, maupun permohonan yang diajukan oleh masyarakat secara lebih terstruktur.
                        </p>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-[1.7rem] border border-[#cfe0ff] bg-[linear-gradient(180deg,_#eef5ff_0%,_#ffffff_100%)] p-5 shadow-sm">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-[#1d4ed8]">Fokus Kinerja</p>
                            <p class="mt-3 text-lg font-bold text-slate-950">Efektivitas Aparatur</p>
                            <p class="mt-2 text-sm leading-7 text-slate-600">Menghemat waktu dan sumber daya melalui otomatisasi pendataan.</p>
                        </div>

                        <div class="rounded-[1.7rem] border border-[#b6f0e6] bg-[linear-gradient(180deg,_#ebfffb_0%,_#ffffff_100%)] p-5 shadow-sm">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-[#115e59]">Kepuasan Publik</p>
                            <p class="mt-3 text-lg font-bold text-slate-950">Layanan Prima</p>
                            <p class="mt-2 text-sm leading-7 text-slate-600">Memastikan semua tamu mendapat sambutan dan tindak lanjut optimal.</p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-[1.9rem] border border-[#ffd4b8] bg-[linear-gradient(135deg,_#fff6ee_0%,_#fffdf8_100%)] p-5 shadow-sm">
                        <p class="text-sm font-semibold text-slate-950">Prinsip Pelayanan Unggulan:</p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-[1.3rem] bg-white px-4 py-4 shadow-sm ring-1 ring-slate-100">
                                <p class="text-sm font-bold text-slate-950">Transparansi</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Terbuka dan dapat dipertanggungjawabkan kepada publik.</p>
                            </div>
                            <div class="rounded-[1.3rem] bg-white px-4 py-4 shadow-sm ring-1 ring-slate-100">
                                <p class="text-sm font-bold text-slate-950">Akuntabilitas</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Memiliki standar pelaporan kunjungan yang jelas dan akurat.</p>
                            </div>
                            <div class="rounded-[1.3rem] bg-white px-4 py-4 shadow-sm ring-1 ring-slate-100">
                                <p class="text-sm font-bold text-slate-950">Responsivitas</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Tanggap dalam memberikan solusi kebutuhan setiap warga.</p>
                            </div>
                        </div>
                    </div>
                </article>
            </section>

            <section class="landing-fade-up-delay-2 rounded-[2.6rem] border border-white/70 bg-[linear-gradient(135deg,_#fff7ec_0%,_#ffffff_35%,_#eef7ff_100%)] p-6 shadow-[0_24px_70px_rgba(15,23,42,0.06)] sm:p-8 lg:p-10">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-[0.72rem] font-semibold uppercase tracking-[0.32em] text-[#9a3412]">Akses Sistem</p>
                        <h2 class="font-display mt-3 text-3xl leading-tight font-semibold text-slate-950 sm:text-4xl">
                            Mari mulai tingkatkan kualitas pelayanan secara bersama.
                        </h2>
                        <p class="mt-4 text-base leading-8 text-slate-600">
                            Silakan masuk ke dalam sistem untuk melakukan pendataan atau mengakses panel kontrol bagi petugas terkait. Kehadiran inovasi ini diharapkan membawa dampak positif bagi kemajuan Kecamatan Bungah.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <a
                            href="{{ route('kunjungan-tamu.index') }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Isi Buku Tamu
                        </a>
                        <a
                            href="#" data-open-internal-modal
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-6 py-3.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Login Internal
                        </a>
                    </div>
                </div>
            </section>
        </main>
    </div>
<div
        id="internal-auth-modal"
        data-open="{{ $showInternalModal ? 'true' : 'false' }}"
        class="fixed inset-0 z-50 {{ $showInternalModal ? 'flex' : 'hidden' }} items-center justify-center p-4 sm:p-6"
    >
        <button type="button" data-close-internal-modal class="absolute inset-0 bg-slate-950/55 backdrop-blur-[4px]"></button>

        <div class="relative z-10 w-full max-w-2xl overflow-hidden rounded-[2.4rem] border border-white/12 bg-white shadow-[0_40px_120px_rgba(15,23,42,0.30)]">
            <div class="grid lg:grid-cols-[0.9fr_1.1fr]">
                <aside class="relative overflow-hidden bg-[linear-gradient(145deg,_#0f172a_0%,_#16263f_54%,_#1f3b36_100%)] p-6 text-white sm:p-7">
                    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.10),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(96,165,250,0.18),_transparent_24%)]"></div>

                    <div class="relative space-y-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[0.72rem] font-semibold uppercase tracking-[0.3em] text-[#bfdbfe]">Akses Internal</p>
                                <h3 class="font-display mt-3 text-3xl leading-tight font-semibold text-white">Admin / Validator</h3>
                            </div>

                            <button
                                type="button"
                                data-close-internal-modal
                                class="flex h-11 w-11 items-center justify-center rounded-full border border-white/14 bg-white/8 text-lg text-white transition hover:bg-white/14"
                            >
                                &#10005;
                            </button>
                        </div>

                        <p class="text-sm leading-7 text-white/72">
                            Jalur ini hanya untuk petugas internal yang bertanggung jawab atas validasi, pengelolaan data, dan laporan.
                        </p>

                        <div class="rounded-[1.5rem] border border-white/10 bg-white/8 p-4 backdrop-blur">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-white/55">Keamanan Akses</p>
                            <p class="mt-2 text-sm font-semibold text-white">Role dibatasi</p>
                            <p class="mt-2 text-sm leading-6 text-white/70">Akun tamu tidak dapat masuk dari form internal ini.</p>
                        </div>
                    </div>
                </aside>

                <div class="bg-[linear-gradient(180deg,_#ffffff_0%,_#fcfbf8_100%)] p-6 sm:p-8">
                    <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-4 py-1.5 text-[0.72rem] font-semibold uppercase tracking-[0.28em] text-slate-600">
                        Login Internal
                    </span>

                    <h3 class="font-display mt-5 text-3xl leading-tight font-semibold text-slate-950">Masuk Internal</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-600">
                        Gunakan akun admin atau validator untuk masuk ke area kerja internal sistem.
                    </p>

                    @if ($internalLoginErrors->any())
                        <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            Login internal belum berhasil.
                        </div>
                    @endif

                    <form action="{{ route('authenticate') }}" method="POST" class="mt-6 space-y-5">
                        @csrf
                        <input type="hidden" name="form_context" value="internal-login">

                        <div class="space-y-2">
                            <label for="internal_email" class="text-sm font-semibold text-slate-700">Email</label>
                            <input
                                id="internal_email"
                                name="email"
                                type="email"
                                value="{{ $oldContext === 'internal-login' ? old('email') : '' }}"
                                class="w-full rounded-[1.35rem] border border-stone-200 bg-stone-50 px-4 py-3.5 text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                placeholder="akun.internal@bungah.go.id"
                            >
                            @if ($internalLoginErrors->has('email'))
                                <p class="text-sm text-rose-600">{{ $internalLoginErrors->first('email') }}</p>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <label for="internal_password" class="text-sm font-semibold text-slate-700">Password</label>
                            <input
                                id="internal_password"
                                name="password"
                                type="password"
                                class="w-full rounded-[1.35rem] border border-stone-200 bg-stone-50 px-4 py-3.5 text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                placeholder="Masukkan password"
                            >
                            @if ($internalLoginErrors->has('password'))
                                <p class="text-sm text-rose-600">{{ $internalLoginErrors->first('password') }}</p>
                            @endif
                        </div>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-[1.35rem] bg-slate-900 px-6 py-3.5 text-sm font-semibold text-white shadow-[0_18px_40px_rgba(15,23,42,0.14)] transition hover:-translate-y-0.5 hover:bg-slate-800"
                        >
                            Masuk Internal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const internalModal = document.getElementById('internal-auth-modal');

            if (!internalModal) {
                return;
            }

            const openInternalButtons = document.querySelectorAll('[data-open-internal-modal]');
            const closeInternalButtons = internalModal.querySelectorAll('[data-close-internal-modal]');

            const internalFirstField = internalModal.querySelector('#internal_email');

            const syncBodyState = () => {
                const hasOpenModal = internalModal.classList.contains('flex');
                document.body.classList.toggle('overflow-hidden', hasOpenModal);
            };

            const setModalOpen = (modal, isOpen, onOpen) => {
                modal.classList.toggle('hidden', !isOpen);
                modal.classList.toggle('flex', isOpen);
                syncBodyState();

                if (isOpen) {
                    setTimeout(() => onOpen?.(), 20);
                }
            };

            openInternalButtons.forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    setModalOpen(internalModal, true, () => internalFirstField?.focus());
                });
            });

            closeInternalButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    setModalOpen(internalModal, false);
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') {
                    return;
                }

                if (internalModal.classList.contains('flex')) {
                    setModalOpen(internalModal, false);
                }
            });

            setModalOpen(internalModal, internalModal.dataset.open === 'true', () => internalFirstField?.focus());
        });
    </script>
@endpush
@endsection
