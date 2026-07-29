<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bukti Laporan Buku Tamu - {{ $kunjunganTamu->nama }}</title>
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
            max-width: 800px;
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
        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table {
            margin-top: 24px;
        }

        .info-table td {
            padding: 6px 0;
            vertical-align: top;
        }

        .info-table td:first-child {
            width: 180px;
            font-weight: bold;
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

        .detail-table th,
        .detail-table td {
            border: 1px solid #374151;
            padding: 8px 10px;
            vertical-align: top;
        }

        .detail-table th {
            background: #e5e7eb;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            width: 35%;
        }

        .signature {
            margin-top: 50px;
            display: flex;
            justify-content: flex-end;
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
    @endphp

    <div class="page">
        <section class="panel">
            <div class="letterhead">
                <div class="gov">{{ $office['government_name'] ?? 'Pemerintah Kabupaten Gresik' }}</div>
                <div class="district">{{ $office['district_name'] ?? 'Kecamatan Bungah' }}</div>
                <div class="meta">
                    {{ $office['address'] ?? 'Jl. Raya Bungah No. 1, Bungah' }}<br>
                    Telepon: {{ $office['phone'] ?? '(031) 3941001' }}
                </div>
            </div>

            <h1 class="report-title">Bukti Laporan Pelayanan Tamu</h1>
            <p class="report-subtitle">Sistem Administrasi Pelayanan Kecamatan</p>

            <table class="info-table">
                <tr>
                    <td>Nama Pengunjung</td>
                    <td>: {{ $kunjunganTamu->nama }}</td>
                </tr>
                <tr>
                    <td>Nomor Induk Kependudukan</td>
                    <td>: {{ $kunjunganTamu->nik }}</td>
                </tr>
                <tr>
                    <td>Nomor Telepon</td>
                    <td>: {{ $kunjunganTamu->nomor_telepon }}</td>
                </tr>
                <tr>
                    <td>Tanggal Lahir / Umur</td>
                    <td>: {{ \Carbon\Carbon::parse($kunjunganTamu->tanggal_lahir)->translatedFormat('d F Y') }} ({{ $kunjunganTamu->umur }} Tahun)</td>
                </tr>
                <tr>
                    <td>Waktu Kunjungan</td>
                    <td>: {{ $kunjunganTamu->waktu_kunjungan->translatedFormat('d F Y, H:i') }} WIB</td>
                </tr>
                <tr>
                    <td>Status Layanan</td>
                    <td>: <strong>{{ $kunjunganTamu->status_label }}</strong></td>
                </tr>
            </table>

            <section class="section">
                <h2>Rincian Keperluan</h2>
                <table class="detail-table">
                    <tbody>
                        <tr>
                            <th>Jenis Keperluan</th>
                            <td>{{ $kunjunganTamu->keperluan_label }}</td>
                        </tr>
                        <tr>
                            <th>Keterangan Tambahan</th>
                            <td>{{ filled($kunjunganTamu->detail_keperluan) ? $kunjunganTamu->detail_keperluan : '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="section">
                <h2>Survei Kepuasan Masyarakat</h2>
                <table class="detail-table">
                    <tbody>
                        <tr>
                            <th>Status Survei</th>
                            <td>{{ $kunjunganTamu->survey_waktu_dikirim ? 'Selesai' : 'Belum Selesai' }}</td>
                        </tr>
                        @if ($kunjunganTamu->survey_waktu_dikirim)
                            <tr>
                                <th>Rata-rata Nilai</th>
                                <td>{{ $kunjunganTamu->average_rating !== null ? number_format($kunjunganTamu->average_rating, 1) . ' / 5' : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Nilai Aspek</th>
                                <td>
                                    Pelayanan: {{ $kunjunganTamu->nilai_pelayanan ?? '-' }} |
                                    Kecepatan: {{ $kunjunganTamu->nilai_kecepatan ?? '-' }} |
                                    Fasilitas: {{ $kunjunganTamu->nilai_fasilitas ?? '-' }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </section>

            <div class="signature">
                <div class="signature-box">
                    {{ $office['city'] ?? 'Gresik' }}, {{ now()->translatedFormat('d F Y') }}<br>
                    Administrator / Petugas
                    <div class="signature-space"></div>
                    <strong>{{ auth()->user()->name }}</strong>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
