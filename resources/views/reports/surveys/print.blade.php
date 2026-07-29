<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laporan Rekap Survey Kepuasan Masyarakat</title>
        <style>
            :root {
                color-scheme: light;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: 'Times New Roman', Times, serif;
                color: #111827;
                background: #f3f4f6;
                font-size: 12px;
            }

            .page {
                max-width: 1240px;
                margin: 0 auto;
                padding: 24px 18px 40px;
            }

            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin-bottom: 20px;
            }

            .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 10px 16px;
                border-radius: 999px;
                border: 1px solid #cbd5e1;
                background: #ffffff;
                color: #111827;
                text-decoration: none;
                font-family: Arial, sans-serif;
                font-size: 13px;
                font-weight: 700;
            }

            .button-primary {
                border-color: #0f172a;
                background: #0f172a;
                color: #ffffff;
            }

            .panel {
                background: #ffffff;
                padding: 28px 30px 36px;
                box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
            }

            .letterhead {
                text-align: center;
                border-bottom: 3px solid #111827;
                padding-bottom: 16px;
                margin-bottom: 20px;
            }

            .letterhead .gov {
                font-size: 18px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .letterhead .district {
                margin-top: 4px;
                font-size: 24px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .letterhead .meta {
                margin-top: 6px;
                font-size: 13px;
                line-height: 1.5;
            }

            .report-title {
                margin: 0;
                text-align: center;
                font-size: 18px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.06em;
            }

            .report-subtitle {
                margin: 6px 0 0;
                text-align: center;
                font-size: 13px;
            }

            .info-table,
            .summary-table,
            .aspect-table,
            .question-table,
            .keperluan-table,
            .detail-table {
                width: 100%;
                border-collapse: collapse;
            }

            .info-table {
                margin-top: 18px;
            }

            .info-table td {
                padding: 4px 0;
                vertical-align: top;
            }

            .info-table td:first-child {
                width: 200px;
            }

            .section {
                margin-top: 24px;
            }

            .section h2 {
                margin: 0 0 10px;
                font-size: 15px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .summary-table th,
            .summary-table td,
            .aspect-table th,
            .aspect-table td,
            .question-table th,
            .question-table td,
            .keperluan-table th,
            .keperluan-table td,
            .detail-table th,
            .detail-table td {
                border: 1px solid #374151;
                padding: 8px 7px;
                vertical-align: top;
            }

            .summary-table th,
            .aspect-table th,
            .question-table th,
            .keperluan-table th,
            .detail-table th {
                background: #e5e7eb;
                text-align: center;
                font-size: 12px;
                font-weight: 700;
            }

            .empty {
                padding: 20px;
                border: 1px solid #9ca3af;
                text-align: center;
            }

            .signature {
                margin-top: 32px;
                display: flex;
                justify-content: space-between;
                gap: 32px;
            }

            .signature-box {
                width: 280px;
                text-align: center;
            }

            .signature-space {
                height: 70px;
            }

            @media print {
                body {
                    background: #ffffff;
                }

                .actions {
                    display: none;
                }

                .page {
                    max-width: none;
                    padding: 0;
                }

                .panel {
                    box-shadow: none;
                }
            }
        </style>
    </head>
    <body>
        @php
            $office = config('app.office');
            $showActions = $showActions ?? true;
            $survey = $summary['survey'];
            $questionBreakdown = $summary['question_breakdown'];
            $keperluanBreakdown = $summary['keperluan_breakdown'];
        @endphp

        <div class="page">
            @if ($showActions)
                <div class="actions">
                    <a href="{{ route($panel['dashboard_route']) }}" class="button">Kembali ke {{ $panel['label'] }}</a>
                    <a href="{{ route($panel['route_prefix'] . '.export', ['format' => 'pdf'] + $filters) }}" class="button">Export PDF</a>
                    <a href="{{ route($panel['route_prefix'] . '.export', ['format' => 'excel'] + $filters) }}" class="button">Export Excel</a>
                    <button type="button" onclick="window.print()" class="button button-primary">Cetak Sekarang</button>
                </div>
            @endif

            <section class="panel">
                <div class="letterhead">
                    <div class="gov">{{ $office['government_name'] }}</div>
                    <div class="district">{{ $office['district_name'] }}</div>
                    <div class="meta">
                        {{ $office['address'] }}<br>
                        Telepon: {{ $office['phone'] }}
                    </div>
                </div>

                <h1 class="report-title">Laporan Rekap Survey Kepuasan Masyarakat</h1>
                <p class="report-subtitle">Panel {{ $panel['label'] }} - Sistem Administrasi Pelayanan Kecamatan</p>

                <table class="info-table">
                    <tr>
                        <td>Periode Survey</td>
                        <td>: {{ $summary['period_label'] }}</td>
                    </tr>
                    <tr>
                        <td>Status Proses Terkait</td>
                        <td>: {{ $summary['completion_status_label'] }}</td>
                    </tr>
                    @if(filled($filters['guest_name']))
                        <tr>
                            <td>Filter Nama Tamu</td>
                            <td>: {{ $filters['guest_name'] }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Tanggal Cetak</td>
                        <td>: {{ now()->translatedFormat('d F Y H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <td>Petugas Pencetak</td>
                        <td>: {{ auth()->user()->name }}</td>
                    </tr>
                </table>

                <section class="section">
                    <h2>Ringkasan Survey</h2>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Total Survey</th>
                                <th>Responden Berakun</th>
                                <th>Survey dengan Saran</th>
                                <th>Rata-rata Keseluruhan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align:center;">{{ number_format($summary['total_surveys']) }}</td>
                                <td style="text-align:center;">{{ number_format($summary['respondent_total']) }}</td>
                                <td style="text-align:center;">{{ number_format($summary['saran_total']) }}</td>
                                <td style="text-align:center;">{{ $survey['overall'] !== null ? number_format($survey['overall'], 1) . ' / 5' : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section class="section">
                    <h2>Nilai Survey per Aspek</h2>
                    <table class="aspect-table">
                        <thead>
                            <tr>
                                <th>Aspek Penilaian</th>
                                <th>Rata-rata Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Pelayanan</td>
                                <td style="text-align:center;">{{ $survey['service'] !== null ? number_format($survey['service'], 1) . ' / 5' : '-' }}</td>
                            </tr>
                            <tr>
                                <td>Kecepatan</td>
                                <td style="text-align:center;">{{ $survey['speed'] !== null ? number_format($survey['speed'], 1) . ' / 5' : '-' }}</td>
                            </tr>
                            <tr>
                                <td>Fasilitas</td>
                                <td style="text-align:center;">{{ $survey['facility'] !== null ? number_format($survey['facility'], 1) . ' / 5' : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section class="section">
                    <h2>Rekap Jawaban per Soal</h2>

                    @if ($questionBreakdown === [])
                        <div class="empty">Tidak ada jawaban detail survey pada periode yang dipilih.</div>
                    @else
                        <table class="question-table">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">Soal</th>
                                    <th style="width: 120px;">Aspek</th>
                                    <th>Pertanyaan</th>
                                    <th style="width: 110px;">Rata-rata</th>
                                    <th style="width: 100px;">Respon</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($questionBreakdown as $question)
                                    <tr>
                                        <td style="text-align:center;">{{ $question['label'] }}</td>
                                        <td style="text-align:center;">{{ $question['category_label'] }}</td>
                                        <td>{{ $question['question'] }}</td>
                                        <td style="text-align:center;">{{ $question['average'] !== null ? number_format($question['average'], 1) . ' / 5' : '-' }}</td>
                                        <td style="text-align:center;">{{ number_format($question['responses']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </section>

                <section class="section">
                    <h2>Asal Keperluan Survey</h2>

                    @if ($keperluanBreakdown === [])
                        <div class="empty">Tidak ada rekap keperluan pada data survey yang dipilih.</div>
                    @else
                        <table class="keperluan-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th>Jenis Keperluan</th>
                                    <th style="width: 120px;">Jumlah Survey</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($keperluanBreakdown as $keperluan)
                                    <tr>
                                        <td style="text-align:center;">{{ $loop->iteration }}</td>
                                        <td>{{ $keperluan['label'] }}</td>
                                        <td style="text-align:center;">{{ number_format($keperluan['total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </section>

                <section class="section">
                    <h2>Rincian Data Survey</h2>

                    @if ($surveys->isEmpty())
                        <div class="empty">Tidak ada data survey pada filter yang dipilih.</div>
                    @else
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th style="width: 45px;">No</th>
                                    <th style="width: 110px;">Tanggal Survey</th>
                                    <th style="width: 130px;">Responden</th>
                                    <th style="width: 160px;">Email</th>
                                    <th style="width: 130px;">Keperluan Awal</th>
                                    <th style="width: 95px;">Status</th>
                                    <th style="width: 70px;">Pel.</th>
                                    <th style="width: 70px;">Cep.</th>
                                    <th style="width: 70px;">Fas.</th>
                                    <th style="width: 80px;">Rata-rata</th>
                                    <th>Kritik</th>
                                    <th>Saran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($surveys as $surveyRow)
                                    <tr>
                                        <td style="text-align:center;">{{ $loop->iteration }}</td>
                                        <td>{{ $surveyRow->waktu_dikirim?->translatedFormat('d-m-Y H:i') ?? '-' }}</td>
                                        <td>{{ $surveyRow->pengguna?->name ?? $surveyRow->kunjunganTamu?->nama ?? 'Tanpa akun' }}</td>
                                        <td>{{ $surveyRow->pengguna?->email ?? '-' }}</td>
                                        <td>{{ $surveyRow->kunjunganTamu?->keperluan_label ?? '-' }}</td>
                                        <td>{{ $surveyRow->kunjunganTamu?->status_label ?? 'Tidak terhubung' }}</td>
                                        <td style="text-align:center;">{{ $surveyRow->nilai_pelayanan ?? '-' }}</td>
                                        <td style="text-align:center;">{{ $surveyRow->nilai_kecepatan ?? '-' }}</td>
                                        <td style="text-align:center;">{{ $surveyRow->nilai_fasilitas ?? '-' }}</td>
                                        <td style="text-align:center;">{{ $surveyRow->average_rating !== null ? number_format($surveyRow->average_rating, 1) : '-' }}</td>
                                        <td>{{ filled($surveyRow->kritik) ? $surveyRow->kritik : '-' }}</td>
                                        <td>{{ filled($surveyRow->saran) ? $surveyRow->saran : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </section>

                <div class="signature">
                    <div class="signature-box">
                        Mengetahui,<br>
                        {{ $office['head_title'] }}
                        <div class="signature-space"></div>
                        <strong>{{ $office['head_name'] }}</strong>
                    </div>

                    <div class="signature-box">
                        {{ $office['city'] }}, {{ now()->translatedFormat('d F Y') }}<br>
                        Petugas {{ $panel['label'] }}
                        <div class="signature-space"></div>
                        <strong>{{ auth()->user()->name }}</strong>
                    </div>
                </div>
            </section>
        </div>
    </body>
</html>
