@php
    $office = config('app.office');
    $showActions = $showActions ?? true;
    
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
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <title>Cetak Detail Survey Kepuasan</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 13px;
                line-height: 1.5;
                color: #111827;
                background: #f3f4f6;
                margin: 0;
                padding: 20px;
            }

            .page {
                background: #ffffff;
                max-width: 800px;
                margin: 0 auto;
                padding: 40px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            .actions {
                max-width: 800px;
                margin: 0 auto 20px;
                display: flex;
                gap: 10px;
            }

            .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 8px 16px;
                font-size: 14px;
                font-weight: 600;
                border-radius: 6px;
                text-decoration: none;
                cursor: pointer;
                border: 1px solid #d1d5db;
                background: #ffffff;
                color: #374151;
            }

            .button-primary {
                background: #2563eb;
                color: #ffffff;
                border-color: #2563eb;
            }

            .letterhead {
                text-align: center;
                border-bottom: 3px solid #111827;
                padding-bottom: 16px;
                margin-bottom: 24px;
            }

            .letterhead .gov {
                font-size: 16px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .letterhead .district {
                font-size: 20px;
                font-weight: 700;
                text-transform: uppercase;
                margin: 4px 0;
            }

            .letterhead .meta {
                font-size: 12px;
            }

            .report-title {
                text-align: center;
                font-size: 16px;
                font-weight: 700;
                text-transform: uppercase;
                margin: 0 0 4px;
            }

            .report-subtitle {
                text-align: center;
                font-size: 14px;
                margin: 0 0 24px;
            }

            .info-table {
                width: 100%;
                margin-bottom: 24px;
            }

            .info-table td {
                padding: 4px 0;
                vertical-align: top;
            }

            .info-table td:first-child {
                width: 200px;
            }

            .section h2 {
                margin: 24px 0 12px;
                font-size: 15px;
                font-weight: 700;
                border-bottom: 1px solid #d1d5db;
                padding-bottom: 6px;
            }

            .table-data {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 24px;
            }

            .table-data th,
            .table-data td {
                border: 1px solid #9ca3af;
                padding: 8px;
                vertical-align: top;
            }

            .table-data th {
                background: #f3f4f6;
                text-align: left;
                font-size: 13px;
            }

            .table-data td.center {
                text-align: center;
            }

            .saran-box {
                padding: 12px;
                border: 1px solid #9ca3af;
                background: #f9fafb;
                font-style: italic;
            }

            .signature {
                margin-top: 40px;
                display: flex;
                justify-content: flex-end;
            }

            .signature-box {
                width: 280px;
                text-align: center;
            }

            .signature-space {
                height: 80px;
            }

            @media print {
                body {
                    background: #ffffff;
                    padding: 0;
                }

                .actions {
                    display: none;
                }

                .page {
                    max-width: none;
                    padding: 0;
                    box-shadow: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="page">
            @if ($showActions)
                <div class="actions">
                    <a href="{{ route($panel['route_prefix'] . '.show', ['survey' => $survey->id] + request()->query()) }}" class="button">Kembali ke Detail</a>
                    <button type="button" onclick="window.print()" class="button button-primary">Cetak Sekarang</button>
                </div>
            @endif

            <div class="letterhead">
                <div class="gov">{{ $office['government_name'] }}</div>
                <div class="district">{{ $office['district_name'] }}</div>
                <div class="meta">
                    {{ $office['address'] }}<br>
                    Telepon: {{ $office['phone'] }}
                </div>
            </div>

            <h1 class="report-title">Laporan Detail Survey Kepuasan Tamu</h1>
            <p class="report-subtitle">Panel {{ $panel['label'] }} - Sistem Administrasi Pelayanan Kecamatan</p>

            <table class="info-table">
                <tr>
                    <td>Nama Pengunjung</td>
                    <td>: <strong>{{ $guestName }}</strong></td>
                </tr>
                <tr>
                    <td>Nomor Telepon</td>
                    <td>: {{ $guestPhone }}</td>
                </tr>
                <tr>
                    <td>Keperluan Kunjungan</td>
                    <td>: {{ $keperluan }}</td>
                </tr>
                <tr>
                    <td>Waktu Pengisian</td>
                    <td>: {{ $survey->waktu_dikirim?->translatedFormat('d F Y, H:i') ?? '-' }} WIB</td>
                </tr>
                <tr>
                    <td>Tanggal Cetak</td>
                    <td>: {{ now()->translatedFormat('d F Y H:i') }} WIB</td>
                </tr>
            </table>

            <div class="section">
                <h2>Rata-rata Penilaian</h2>
                <table class="table-data">
                    <tr>
                        <th>Pelayanan</th>
                        <th>Kecepatan</th>
                        <th>Fasilitas</th>
                        <th>Keseluruhan</th>
                    </tr>
                    <tr>
                        <td class="center">{{ $survey->nilai_pelayanan !== null ? number_format($survey->nilai_pelayanan, 1) : '-' }}</td>
                        <td class="center">{{ $survey->nilai_kecepatan !== null ? number_format($survey->nilai_kecepatan, 1) : '-' }}</td>
                        <td class="center">{{ $survey->nilai_fasilitas !== null ? number_format($survey->nilai_fasilitas, 1) : '-' }}</td>
                        <td class="center" style="font-weight: bold; font-size: 14px;">{{ $survey->average_rating !== null ? number_format($survey->average_rating, 1) : '-' }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <h2>Rincian Jawaban</h2>
                <table class="table-data">
                    <tr>
                        <th style="width: 40px; text-align: center;">No</th>
                        <th>Aspek & Pertanyaan</th>
                        <th style="width: 100px; text-align: center;">Penilaian</th>
                    </tr>
                    @foreach ($questions as $index => $q)
                        @php
                            $answerValue = data_get($survey->jawaban_survei, $q['key']);
                            $answerLabel = is_numeric($answerValue) ? ($scoreOptions[(int)$answerValue] ?? $answerValue) : '-';
                            $catKey = getCategoryForQuestion($q['key'], $summaryGroups);
                            $catName = $categories[$catKey] ?? 'Umum';
                        @endphp
                        <tr>
                            <td class="center">{{ $index + 1 }}</td>
                            <td>
                                <div style="font-size: 11px; font-weight: bold; color: #4b5563; text-transform: uppercase; margin-bottom: 4px;">{{ $catName }}</div>
                                {{ $q['question'] }}
                            </td>
                            <td class="center" style="font-weight: bold;">{{ $answerLabel }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>

            <div class="section">
                <h2>Kritik</h2>
                <div class="saran-box">
                    @if (filled($survey->kritik))
                        "{{ $survey->kritik }}"
                    @else
                        Tidak ada kritik yang diberikan.
                    @endif
                </div>
            </div>

            <div class="section">
                <h2>Saran</h2>
                <div class="saran-box">
                    @if (filled($survey->saran))
                        "{{ $survey->saran }}"
                    @else
                        Tidak ada saran yang diberikan.
                    @endif
                </div>
            </div>

            <div class="signature">
                <div class="signature-box">
                    <p>{{ $office['district_name'] }}, {{ now()->translatedFormat('d F Y') }}</p>
                    <p>Petugas {{ $panel['label'] }}</p>
                    <div class="signature-space"></div>
                    <p style="font-weight: bold; text-decoration: underline;">{{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>
    </body>
</html>
