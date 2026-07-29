@extends('layouts.app')

@section('title', 'Laporan Berhasil Disimpan')

@section('content')
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
        <article class="w-full max-w-xl rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col items-center text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="mt-6 text-2xl font-bold text-slate-900">Terima Kasih!</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Data keperluan dan survei kepuasan masyarakat Anda telah berhasil disimpan di sistem kami.
                </p>

                @if (session('status'))
                    <div class="mt-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 w-full border border-emerald-100">
                        {{ session('status') }}
                    </div>
                @endif
            </div>

            <div class="mt-8 rounded-[1.5rem] bg-slate-50 p-5">
                <h2 class="text-sm font-bold uppercase tracking-widest text-slate-500 mb-4 text-center">Ringkasan Data Anda</h2>
                <div class="space-y-3">
                    <div class="flex justify-between border-b border-slate-200 pb-2">
                        <span class="text-sm text-slate-500">Nama</span>
                        <span class="text-sm font-semibold text-slate-900">{{ $kunjunganTamu->nama }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-200 pb-2">
                        <span class="text-sm text-slate-500">Waktu Kunjungan</span>
                        <span class="text-sm font-semibold text-slate-900">{{ $kunjunganTamu->waktu_kunjungan->translatedFormat('d F Y, H:i') }} WIB</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-200 pb-2">
                        <span class="text-sm text-slate-500">Keperluan</span>
                        <span class="text-sm font-semibold text-slate-900">{{ $kunjunganTamu->keperluan_label }}</span>
                    </div>
                    <div class="flex justify-between pb-1">
                        <span class="text-sm text-slate-500">Status Survei</span>
                        <span class="text-sm font-semibold text-emerald-600">Selesai</span>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <a href="{{ route('welcome') }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Kembali ke Halaman Utama
                </a>
            </div>
        </article>
    </div>
@endsection
