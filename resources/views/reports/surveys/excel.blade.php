@php
    $survey = $summary['survey'];
    $questionBreakdown = $summary['question_breakdown'];
    $keperluanBreakdown = $summary['keperluan_breakdown'];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <title>Export Rekap Survey Kepuasan</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                color: #111827;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 16px;
            }

            th,
            td {
                border: 1px solid #cbd5e1;
                padding: 6px 8px;
                vertical-align: top;
            }

            th {
                background: #e2e8f0;
                text-align: left;
            }

            .title {
                font-size: 18px;
                font-weight: 700;
                margin-bottom: 12px;
            }
        </style>
    </head>
    <body>
        <div class="title">Laporan Rekap Survey Kepuasan Masyarakat - {{ $panel['label'] }}</div>

        <table>
            <tbody>
                <tr>
                    <td style="width: 220px;">Periode Survey</td>
                    <td>{{ $summary['period_label'] }}</td>
                </tr>
                <tr>
                    <td>Status Proses Terkait</td>
                    <td>{{ $summary['completion_status_label'] }}</td>
                </tr>
                @if(filled($filters['guest_name']))
                    <tr>
                        <td>Filter Nama Tamu</td>
                        <td>{{ $filters['guest_name'] }}</td>
                    </tr>
                @endif
                <tr>
                    <td>Tanggal Export</td>
                    <td>{{ now()->translatedFormat('d F Y H:i') }} WIB</td>
                </tr>
                <tr>
                    <td>Petugas Export</td>
                    <td>{{ auth()->user()->name }}</td>
                </tr>
            </tbody>
        </table>

        <table>
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
                    <td>{{ number_format($summary['total_surveys']) }}</td>
                    <td>{{ number_format($summary['respondent_total']) }}</td>
                    <td>{{ number_format($summary['saran_total']) }}</td>
                    <td>{{ $survey['overall'] !== null ? number_format($survey['overall'], 1) . ' / 5' : '-' }}</td>
                </tr>
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Aspek</th>
                    <th>Rata-rata</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Pelayanan</td>
                    <td>{{ $survey['service'] !== null ? number_format($survey['service'], 1) . ' / 5' : '-' }}</td>
                </tr>
                <tr>
                    <td>Kecepatan</td>
                    <td>{{ $survey['speed'] !== null ? number_format($survey['speed'], 1) . ' / 5' : '-' }}</td>
                </tr>
                <tr>
                    <td>Fasilitas</td>
                    <td>{{ $survey['facility'] !== null ? number_format($survey['facility'], 1) . ' / 5' : '-' }}</td>
                </tr>
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Soal</th>
                    <th>Aspek</th>
                    <th>Pertanyaan</th>
                    <th>Rata-rata</th>
                    <th>Jumlah Respon</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($questionBreakdown as $question)
                    <tr>
                        <td>{{ $question['label'] }}</td>
                        <td>{{ $question['category_label'] }}</td>
                        <td>{{ $question['question'] }}</td>
                        <td>{{ $question['average'] !== null ? number_format($question['average'], 1) . ' / 5' : '-' }}</td>
                        <td>{{ number_format($question['responses']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Tidak ada jawaban detail survey pada filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jenis Keperluan</th>
                    <th>Jumlah Survey</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($keperluanBreakdown as $keperluan)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $keperluan['label'] }}</td>
                        <td>{{ number_format($keperluan['total']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Tidak ada rekap keperluan pada data survey yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal Survey</th>
                    <th>Responden</th>
                    <th>Email</th>
                    <th>Keperluan Awal</th>
                    <th>Status</th>
                    <th>Pelayanan</th>
                    <th>Kecepatan</th>
                    <th>Fasilitas</th>
                    <th>Rata-rata</th>
                    <th>Kritik</th>
                    <th>Saran</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($surveys as $surveyRow)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $surveyRow->waktu_dikirim?->translatedFormat('d-m-Y H:i') ?? '-' }}</td>
                        <td>{{ $surveyRow->pengguna?->name ?? $surveyRow->kunjunganTamu?->nama ?? 'Tanpa akun' }}</td>
                        <td>{{ $surveyRow->pengguna?->email ?? '-' }}</td>
                        <td>{{ $surveyRow->kunjunganTamu?->keperluan_label ?? '-' }}</td>
                        <td>{{ $surveyRow->kunjunganTamu?->status_label ?? 'Tidak terhubung' }}</td>
                        <td>{{ $surveyRow->nilai_pelayanan ?? '-' }}</td>
                        <td>{{ $surveyRow->nilai_kecepatan ?? '-' }}</td>
                        <td>{{ $surveyRow->nilai_fasilitas ?? '-' }}</td>
                        <td>{{ $surveyRow->average_rating !== null ? number_format($surveyRow->average_rating, 1) : '-' }}</td>
                        <td>{{ filled($surveyRow->kritik) ? $surveyRow->kritik : '-' }}</td>
                        <td>{{ filled($surveyRow->saran) ? $surveyRow->saran : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">Tidak ada data survey pada filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>
