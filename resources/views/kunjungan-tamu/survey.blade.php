@extends('layouts.app')

@section('title', 'Survei Kepuasan')

@php
    $surveyErrors = $errors->getBag('surveiTamu');
    $surveyQuestionCount = count($surveyQuestions);
@endphp

@section('content')
    <div class="min-h-screen bg-slate-50 pb-24">
        <div class="mx-auto max-w-4xl px-4 py-4 sm:px-6 lg:px-8">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-500">Survei Kepuasan</p>
                        <h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">Mohon nilai layanan kami</h1>

                        <div class="mt-3 flex flex-wrap gap-2 text-sm text-slate-600">
                            <span class="rounded-full bg-slate-100 px-3 py-1">{{ $kunjunganTamu->keperluan_label }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1">
                                {{ $kunjunganTamu->waktu_kunjungan->translatedFormat('d F Y, H:i') }} WIB
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a
                            href="{{ route('kunjungan-tamu.index') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Kembali
                        </a>
                        <button
                            type="submit"
                            form="guest-survey-form"
                            class="inline-flex items-center justify-center rounded-2xl px-5 py-3 text-sm font-bold shadow-md transition-all hover:shadow-lg"
                            style="background: linear-gradient(to right, #2563eb, #4f46e5); color: white;"
                        >
                            Submit Survey
                        </button>
                    </div>
                </div>


            </section>

            @if (session('status'))
                <div class="mt-4 rounded-[1.5rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($surveyErrors->any())
                <div class="mt-4 rounded-[1.5rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
                    Semua pertanyaan wajib dijawab sebelum survei dikirim.
                </div>
            @endif



            <form
                id="guest-survey-form"
                action="{{ route('kunjungan-tamu.survey.store', $kunjunganTamu) }}"
                method="POST"
                class="mt-4 space-y-4"
                data-survey-form
            >
                @csrf

                <div id="pertanyaan-survey" class="space-y-3">
                    @foreach ($surveyQuestions as $question)
                        <section
                            class="rounded-[2rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-5"
                            data-question-group
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#17395a] text-sm font-bold text-white">
                                    {{ $loop->iteration }}
                                </div>
                                <p class="pt-1 text-base font-semibold leading-6 text-slate-900 sm:text-lg">
                                    {{ $question['question'] }}
                                </p>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-2 sm:gap-3">
                                @foreach ($surveyScoreLabels as $value => $label)
                                    <div>
                                        <input
                                            id="{{ $question['key'] }}_{{ $value }}"
                                            name="jawaban[{{ $question['key'] }}]"
                                            type="radio"
                                            value="{{ $value }}"
                                            class="peer sr-only"
                                            @checked((string) old('jawaban.'.$question['key']) === (string) $value)
                                        >
                                        <label
                                            for="{{ $question['key'] }}_{{ $value }}"
                                            class="flex min-h-[50px] cursor-pointer flex-col items-center justify-center rounded-xl border border-slate-300 bg-slate-50 px-2 py-3 text-center text-slate-700 transition hover:border-[#1b4f73] hover:bg-white peer-checked:border-[#17395a] peer-checked:bg-[#17395a] peer-checked:text-white peer-checked:ring-2 peer-checked:ring-[#d9e8f3]"
                                        >
                                            <span class="text-sm font-semibold sm:text-base">{{ $label }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            @if ($surveyErrors->has('jawaban.'.$question['key']))
                                <p class="mt-3 text-sm text-rose-600">
                                    {{ $surveyErrors->first('jawaban.'.$question['key']) }}
                                </p>
                            @endif
                        </section>
                    @endforeach
                </div>

                <section
                    id="kritik-survey"
                    class="rounded-[2rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-6 mb-6"
                >
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-lg font-bold text-slate-900">Kritik Tambahan</h2>
                        <p class="text-sm text-slate-500">Opsional</p>
                    </div>

                    <label for="kritik" class="sr-only">Kritik Tambahan</label>
                    <textarea
                        id="kritik"
                        name="kritik"
                        rows="3"
                        class="mt-4 w-full rounded-[1.5rem] border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-[#1b4f73] focus:bg-white focus:ring-4 focus:ring-[#d9e8f3]"
                        placeholder="Tulis kritik jika ada."
                    >{{ old('kritik') }}</textarea>

                    @if ($surveyErrors->has('kritik'))
                        <p class="mt-2 text-sm text-rose-600">{{ $surveyErrors->first('kritik') }}</p>
                    @endif
                </section>

                <section
                    id="saran-survey"
                    class="rounded-[2rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-6"
                >
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-lg font-bold text-slate-900">Saran Tambahan</h2>
                        <p class="text-sm text-slate-500">Opsional</p>
                    </div>

                    <label for="saran" class="sr-only">Saran Tambahan</label>
                    <textarea
                        id="saran"
                        name="saran"
                        rows="3"
                        class="mt-4 w-full rounded-[1.5rem] border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-[#1b4f73] focus:bg-white focus:ring-4 focus:ring-[#d9e8f3]"
                        placeholder="Tulis saran jika ada."
                    >{{ old('saran') }}</textarea>

                    @if ($surveyErrors->has('saran'))
                        <p class="mt-2 text-sm text-rose-600">{{ $surveyErrors->first('saran') }}</p>
                    @endif
                </section>

                <div class="mt-8">
                    <button
                        type="submit"
                        class="flex w-full items-center justify-center rounded-[1.5rem] px-6 py-4 text-lg font-bold shadow-xl transition-all hover:-translate-y-0.5 hover:shadow-2xl focus:outline-none focus:ring-4 focus:ring-[#d9e8f3]"
                        style="background: linear-gradient(to right, #2563eb, #4f46e5); color: white;"
                    >
                        Submit Survey Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection


