<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laporan Buku Tamu Kecamatan</title>
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
                font-size: 13px;
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
                width: 180px;
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
            .keperluan-table th,
            .keperluan-table td,
            .detail-table th,
            .detail-table td {
                border: 1px solid #374151;
                padding: 8px 7px;
                vertical-align: top;
            }

            .summary-table th,
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
            $keperluanBreakdown = $summary['keperluan_breakdown'];
            $showActions = $showActions ?? true;
        @endphp

        <div class="page">
            @if ($showActions)
                <div class="actions">
                    <a href="{{ route('validator.dashboard', $filters) }}" class="button">Kembali ke Dashboard</a>
                    <a href="{{ route('validator.kunjungan-tamu.export', ['format' => 'pdf'] + $filters) }}" class="button">Export PDF</a>
                    <a href="{{ route('validator.kunjungan-tamu.export', ['format' => 'excel'] + $filters) }}" class="button">Export Excel</a>
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

                <h1 class="report-title">Laporan Buku Tamu Kecamatan</h1>
                <p class="report-subtitle">Sistem Administrasi Pelayanan Kecamatan</p>

                <table class="info-table">
                    <tr>
                        <td>Periode Laporan</td>
                        <td>: {{ $summary['period_label'] }}</td>
                    </tr>
                    <tr>
                        <td>Status Proses</td>
                        <td>: {{ $summary['completion_status_label'] }}</td>
                    </tr>
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
                    <h2>Ringkasan Laporan</h2>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Total Tamu</th>
                                <th>Sudah Selesai</th>
                                <th>Belum Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align:center;">{{ number_format($summary['total_visitors']) }}</td>
                                <td style="text-align:center;">{{ number_format($summary['completed_total']) }}</td>
                                <td style="text-align:center;">{{ number_format($summary['pending_total']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section class="section">
                    <h2>Rekap Jenis Keperluan</h2>

                    @if ($keperluanBreakdown === [])
                        <div class="empty">Tidak ada data rekap keperluan pada periode yang dipilih.</div>
                    @else
                        <table class="keperluan-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th>Jenis Keperluan</th>
                                    <th style="width: 120px;">Jumlah</th>
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
                    <h2>Rincian Data Tamu</h2>

                    @if ($entries->isEmpty())
                        <div class="empty">Tidak ada data tamu pada filter yang dipilih.</div>
                    @else
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th style="width: 45px;">No</th>
                                    <th style="width: 110px;">Tanggal</th>
                                    <th style="width: 135px;">Nama</th>
                                    <th style="width: 120px;">NIK</th>
                                    <th style="width: 95px;">Telepon</th>
                                    <th style="width: 140px;">Jenis Keperluan</th>
                                    <th>Uraian Keperluan</th>
                                    <th style="width: 105px;">Status</th>
                                    <th style="width: 110px;">Validator</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($entries as $entry)
                                    <tr>
                                        <td style="text-align:center;">{{ $loop->iteration }}</td>
                                        <td>{{ $entry->waktu_kunjungan->translatedFormat('d-m-Y H:i') }}</td>
                                        <td>{{ $entry->nama }}</td>
                                        <td>{{ $entry->nik }}</td>
                                        <td>{{ $entry->nomor_telepon }}</td>
                                        <td>{{ $entry->keperluan_label }}</td>
                                        <td>{{ filled($entry->detail_keperluan) ? $entry->detail_keperluan : '-' }}</td>
                                        <td>{{ $entry->status_label }}</td>
                                        <td>{{ $entry->validator?->name ?? '-' }}</td>
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
                        Petugas Validator
                        <div class="signature-space"></div>
                        <strong>{{ auth()->user()->name }}</strong>
                    </div>
                </div>
            </section>
        </div>
    </body>
</html>
