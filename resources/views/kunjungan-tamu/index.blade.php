@extends('layouts.app')

@section('title', 'Isi Buku Tamu')

@php
    $entryErrors = $errors->getBag('kunjunganTamu');
@endphp

@section('content')
    <div class="min-h-screen bg-slate-50">
        <main class="mx-auto max-w-3xl space-y-4 px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Buku Tamu Digital</h1>
                    <p class="mt-1 text-sm text-slate-600">Kecamatan Bungah, Kabupaten Gresik</p>
                </div>
                <a href="{{ route('welcome') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Kembali
                </a>
            </div>

            @if (session('status'))
                <div class="rounded-[1.5rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($entryErrors->any())
                <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
                    Mohon periksa lagi data yang diisi. Masih ada bagian yang belum benar.
                </div>
            @endif

            <article
                id="form-pengajuan"
                class="rounded-[2rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-6"
            >
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Form Pengajuan Tamu</h2>
                    <p class="mt-1 text-sm text-slate-600">Silakan isi data diri dan keperluan Anda.</p>
                </div>

                <form action="{{ route('kunjungan-tamu.store') }}" method="POST" class="mt-5 space-y-5">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="space-y-2 sm:col-span-2">
                            <label for="nama" class="text-sm font-semibold text-slate-700">Nama Lengkap</label>
                            <input
                                id="nama"
                                name="nama"
                                type="text"
                                value="{{ old('nama') }}"
                                class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                                placeholder="Masukkan nama lengkap"
                            >
                            @if ($entryErrors->has('nama'))
                                <p class="text-sm text-rose-600">{{ $entryErrors->first('nama') }}</p>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <label for="nomor_telepon" class="text-sm font-semibold text-slate-700">Nomor Telepon</label>
                            <input
                                id="nomor_telepon"
                                name="nomor_telepon"
                                type="text"
                                inputmode="numeric"
                                value="{{ old('nomor_telepon') }}"
                                class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                                placeholder="08xxxxxxxxxx"
                            >
                            @if ($entryErrors->has('nomor_telepon'))
                                <p class="text-sm text-rose-600">{{ $entryErrors->first('nomor_telepon') }}</p>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <label for="keperluan" class="text-sm font-semibold text-slate-700">Keperluan</label>
                            <select
                                id="keperluan"
                                name="keperluan"
                                class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                            >
                                <option value="">Pilih keperluan</option>
                                @foreach (\App\Models\KunjunganTamu::KEPERLUAN as $value => $label)
                                    <option value="{{ $value }}" @selected(old('keperluan') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @if ($entryErrors->has('keperluan'))
                                <p class="text-sm text-rose-600">{{ $entryErrors->first('keperluan') }}</p>
                            @endif
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <label for="nik" class="text-sm font-semibold text-slate-700">NIK</label>
                            <input
                                id="nik"
                                name="nik"
                                type="text"
                                inputmode="numeric"
                                maxlength="16"
                                value="{{ old('nik') }}"
                                class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                                placeholder="Masukkan 16 digit NIK"
                            >
                            <p id="nik-help" class="text-sm text-slate-500">Masukkan NIK lengkap agar umur terbaca otomatis.</p>
                            @if ($entryErrors->has('nik'))
                                <p class="text-sm text-rose-600">{{ $entryErrors->first('nik') }}</p>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <label for="umur" class="text-sm font-semibold text-slate-700">Umur</label>
                            <input
                                id="umur"
                                name="umur"
                                type="number"
                                value="{{ old('umur') }}"
                                class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                                placeholder="Masukkan umur"
                            >
                            @if ($entryErrors->has('umur'))
                                <p class="text-sm text-rose-600">{{ $entryErrors->first('umur') }}</p>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <label for="tanggal_lahir" class="text-sm font-semibold text-slate-700">Tanggal Lahir</label>
                            <input
                                id="tanggal_lahir"
                                name="tanggal_lahir"
                                type="date"
                                value="{{ old('tanggal_lahir') }}"
                                class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                            >
                            @if ($entryErrors->has('tanggal_lahir'))
                                <p class="text-sm text-rose-600">{{ $entryErrors->first('tanggal_lahir') }}</p>
                            @endif
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <label for="detail_keperluan" class="text-sm font-semibold text-slate-700">Keterangan Tambahan</label>
                            <textarea
                                id="detail_keperluan"
                                name="detail_keperluan"
                                rows="4"
                                class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-100"
                                placeholder="Contoh: pembaruan KTP, mengurus KK, legalisasi, atau kebutuhan lain."
                            >{{ old('detail_keperluan') }}</textarea>
                            @if ($entryErrors->has('detail_keperluan'))
                                <p class="text-sm text-rose-600">{{ $entryErrors->first('detail_keperluan') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-[1.5rem] bg-slate-50 px-4 py-4 text-sm text-slate-600">
                        Setelah data dikirim, Anda akan diarahkan ke halaman survei kepuasan masyarakat.
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-4 py-3 text-base font-semibold text-white transition hover:bg-slate-800"
                    >
                        Lanjutkan & Isi Survei
                    </button>
                </form>
            </article>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        const nikInput = document.getElementById('nik');
        const umurInput = document.getElementById('umur');
        const birthDateInput = document.getElementById('tanggal_lahir');
        const nikHelp = document.getElementById('nik-help');

        const parseNik = (nik) => {
            const cleanNik = nik.replace(/\D/g, '');

            if (cleanNik.length !== 16) {
                return null;
            }

            let day = Number.parseInt(cleanNik.slice(6, 8), 10);
            const month = Number.parseInt(cleanNik.slice(8, 10), 10);
            const year = Number.parseInt(cleanNik.slice(10, 12), 10);

            if (Number.isNaN(day) || Number.isNaN(month) || Number.isNaN(year)) {
                return null;
            }

            if (day > 40) {
                day -= 40;
            }

            const now = new Date();
            const currentYear = now.getFullYear() % 100;
            let fullYear = year > currentYear ? 1900 + year : 2000 + year;
            let birthDate = new Date(fullYear, month - 1, day);

            if (
                birthDate.getFullYear() !== fullYear ||
                birthDate.getMonth() !== month - 1 ||
                birthDate.getDate() !== day
            ) {
                return null;
            }

            if (birthDate > now) {
                fullYear -= 100;
                birthDate = new Date(fullYear, month - 1, day);
            }

            let umur = now.getFullYear() - birthDate.getFullYear();
            const monthDiff = now.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < birthDate.getDate())) {
                umur -= 1;
            }

            if (umur < 0 || umur > 120) {
                return null;
            }

            return {
                umur,
                birthDate: `${fullYear}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`,
            };
        };

        const syncNikPreview = () => {
            if (! nikInput || ! umurInput || ! birthDateInput || ! nikHelp) {
                return;
            }

            const parsed = parseNik(nikInput.value);

            if (! parsed) {
                nikHelp.textContent = 'Masukkan NIK lengkap agar umur terbaca otomatis.';
                return;
            }

            umurInput.value = parsed.umur;
            birthDateInput.value = parsed.birthDate;
            nikHelp.textContent = 'Umur dan tanggal lahir berhasil dibaca dari NIK (Anda tetap bisa mengubahnya jika tidak sesuai).';
        };

        nikInput?.addEventListener('input', syncNikPreview);
        syncNikPreview();
    </script>
@endpush
