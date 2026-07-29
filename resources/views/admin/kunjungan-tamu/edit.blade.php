@extends('layouts.app')

@section('title', 'Edit Data Tamu')

@php
    $ratingLabels = \App\Models\KunjunganTamu::OPSI_SKOR_SURVEI;
@endphp

@section('content')
    <div class="min-h-screen bg-stone-100">
        <main class="mx-auto flex min-h-screen max-w-5xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-600">
                            Edit Administrasi
                        </span>
                        <h1 class="mt-3 text-2xl font-bold text-slate-900">Perubahan Data Tamu</h1>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Form ini digunakan untuk memperbaiki identitas tamu, keperluan layanan, dan hasil survei. Status penyelesaian tetap dikelola oleh validator.
                        </p>
                    </div>

                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Kembali ke Admin
                    </a>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-3">
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Status Proses</p>
                    <p class="mt-3 text-lg font-bold text-slate-900">{{ $entry->status_label }}</p>
                    <p class="mt-2 text-sm text-slate-500">Diperbarui oleh validator</p>
                </article>
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Validator</p>
                    <p class="mt-3 text-lg font-bold text-slate-900">{{ $entry->validator?->name ?? '-' }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $entry->waktu_divalidasi?->translatedFormat('d F Y, H:i') ?? 'Belum ada validasi' }}</p>
                </article>
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Akun Tamu</p>
                    <p class="mt-3 text-lg font-bold text-slate-900">{{ $entry->petugas?->name ?? $entry->nama }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $entry->petugas?->email ?? 'Tanpa akun pengguna' }}</p>
                </article>
            </section>

            @if ($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    Perubahan belum dapat disimpan. Mohon periksa kembali data yang diinput.
                </div>
            @endif

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <form action="{{ route('admin.kunjungan-tamu.update', $entry) }}" method="POST" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Identitas Tamu</h2>
                            <p class="mt-1 text-sm text-slate-500">Perbaiki data dasar tamu dan waktu kunjungan.</p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2 md:col-span-2">
                                <label for="nama" class="text-sm font-semibold text-slate-700">Nama Lengkap</label>
                                <input
                                    id="nama"
                                    name="nama"
                                    type="text"
                                    value="{{ old('nama', $entry->nama) }}"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                >
                                @error('nama')
                                    <p class="text-sm text-rose-600">{{ $Message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="nomor_telepon" class="text-sm font-semibold text-slate-700">Nomor Telepon</label>
                                <input
                                    id="nomor_telepon"
                                    name="nomor_telepon"
                                    type="text"
                                    inputmode="numeric"
                                    value="{{ old('nomor_telepon', $entry->nomor_telepon) }}"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                >
                                @error('nomor_telepon')
                                    <p class="text-sm text-rose-600">{{ $Message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="waktu_kunjungan" class="text-sm font-semibold text-slate-700">Waktu Kunjungan</label>
                                <input
                                    id="waktu_kunjungan"
                                    name="waktu_kunjungan"
                                    type="datetime-local"
                                    value="{{ old('waktu_kunjungan', $entry->waktu_kunjungan->format('Y-m-d\TH:i')) }}"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                >
                                @error('waktu_kunjungan')
                                    <p class="text-sm text-rose-600">{{ $Message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="nik" class="text-sm font-semibold text-slate-700">NIK</label>
                                <input
                                    id="nik"
                                    name="nik"
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="16"
                                    value="{{ old('nik', $entry->nik) }}"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                >
                                <p id="nik-help" class="text-sm text-slate-500">NIK digunakan untuk membaca umur dan tanggal lahir secara otomatis.</p>
                                @error('nik')
                                    <p class="text-sm text-rose-600">{{ $Message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="umur" class="text-sm font-semibold text-slate-700">Umur</label>
                                <input
                                    id="umur"
                                    name="umur"
                                    type="number"
                                    value="{{ old('umur', $entry->umur) }}"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                >
                                @error('umur')
                                    <p class="text-sm text-rose-600">{{ $Message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="tanggal_lahir" class="text-sm font-semibold text-slate-700">Tanggal Lahir</label>
                                <input
                                    id="tanggal_lahir"
                                    name="tanggal_lahir"
                                    type="date"
                                    value="{{ old('tanggal_lahir', $entry->tanggal_lahir->format('Y-m-d')) }}"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                >
                                @error('tanggal_lahir')
                                    <p class="text-sm text-rose-600">{{ $Message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 border-t border-slate-200 pt-8">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Keperluan Layanan</h2>
                            <p class="mt-1 text-sm text-slate-500">Pastikan jenis keperluan dan catatan layanan sesuai data yang diterima.</p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="keperluan" class="text-sm font-semibold text-slate-700">Jenis Keperluan</label>
                                <select
                                    id="keperluan"
                                    name="keperluan"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                >
                                    @foreach (\App\Models\KunjunganTamu::KEPERLUAN as $value => $label)
                                        <option value="{{ $value }}" @selected(old('keperluan', $entry->keperluan) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('keperluan')
                                    <p class="text-sm text-rose-600">{{ $Message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <label for="detail_keperluan" class="text-sm font-semibold text-slate-700">Uraian Keperluan</label>
                                <textarea
                                    id="detail_keperluan"
                                    name="detail_keperluan"
                                    rows="4"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                >{{ old('detail_keperluan', $entry->detail_keperluan) }}</textarea>
                                @error('detail_keperluan')
                                    <p class="text-sm text-rose-600">{{ $Message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 border-t border-slate-200 pt-8">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Hasil Survei Kepuasan</h2>
                            <p class="mt-1 text-sm text-slate-500">Nilai survei berlaku untuk akun tamu ini secara keseluruhan, sehingga perubahan di sini akan diterapkan ke seluruh pengajuan tamu yang sama. Jika nilai kategori diubah manual, detail jawaban per soal lama tidak lagi dihitung sebagai recap rinci.</p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-3">
                            <div class="space-y-2">
                                <label for="nilai_pelayanan" class="text-sm font-semibold text-slate-700">Pelayanan</label>
                                <select
                                    id="nilai_pelayanan"
                                    name="nilai_pelayanan"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                >
                                    <option value="">Belum ada nilai</option>
                                    @foreach ($ratingLabels as $value => $label)
                                        <option value="{{ $value }}" @selected((string) old('nilai_pelayanan', $entry->nilai_pelayanan) === (string) $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('nilai_pelayanan')
                                    <p class="text-sm text-rose-600">{{ $Message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="nilai_kecepatan" class="text-sm font-semibold text-slate-700">Kecepatan</label>
                                <select
                                    id="nilai_kecepatan"
                                    name="nilai_kecepatan"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                >
                                    <option value="">Belum ada nilai</option>
                                    @foreach ($ratingLabels as $value => $label)
                                        <option value="{{ $value }}" @selected((string) old('nilai_kecepatan', $entry->nilai_kecepatan) === (string) $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('nilai_kecepatan')
                                    <p class="text-sm text-rose-600">{{ $Message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="nilai_fasilitas" class="text-sm font-semibold text-slate-700">Fasilitas</label>
                                <select
                                    id="nilai_fasilitas"
                                    name="nilai_fasilitas"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                >
                                    <option value="">Belum ada nilai</option>
                                    @foreach ($ratingLabels as $value => $label)
                                        <option value="{{ $value }}" @selected((string) old('nilai_fasilitas', $entry->nilai_fasilitas) === (string) $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('nilai_fasilitas')
                                    <p class="text-sm text-rose-600">{{ $Message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="saran" class="text-sm font-semibold text-slate-700">Saran Perbaikan / Catatan Masyarakat</label>
                            <textarea
                                id="saran"
                                name="saran"
                                rows="4"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100"
                            >{{ old('saran', $entry->saran) }}</textarea>
                            @error('saran')
                                <p class="text-sm text-rose-600">{{ $Message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-slate-200 pt-6">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-2xl bg-forest-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-forest-700"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </section>
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
