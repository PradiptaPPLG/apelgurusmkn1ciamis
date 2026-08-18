<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a2e; }

    .kop {
        border-bottom: 3px double #1a1a2e;
        padding-bottom: 8px;
        margin-bottom: 12px;
        display: table;
        width: 100%;
    }
    .kop-logo { display: table-cell; width: 72px; vertical-align: middle; }
    .kop-logo img { width: 68px; height: auto; }
    .kop-text { display: table-cell; vertical-align: middle; text-align: center; }
    .kop-dinas { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #555; }
    .kop-sekolah { font-size: 15px; font-weight: bold; text-transform: uppercase; margin: 2px 0; }
    .kop-alamat { font-size: 8px; color: #555; }

    .doc-title {
        text-align: center;
        font-size: 13px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 10px 0 4px;
        text-decoration: underline;
    }
    .doc-sub {
        text-align: center;
        font-size: 10px;
        margin-bottom: 12px;
        color: #333;
    }

    .info-grid { margin-bottom: 10px; }
    .info-grid table { width: 100%; }
    .info-grid td { padding: 2px 4px; font-size: 9.5px; }
    .info-grid .label { width: 110px; font-weight: bold; }
    .info-grid .sep { width: 10px; }

    table.absen {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
    }
    table.absen thead tr {
        background-color: #1a1a2e;
        color: #ffffff;
    }
    table.absen thead th {
        padding: 6px 8px;
        text-align: center;
        font-size: 9.5px;
        border: 1px solid #1a1a2e;
    }
    table.absen tbody tr:nth-child(even) { background-color: #f0f4ff; }
    table.absen tbody td {
        padding: 5px 8px;
        border: 1px solid #cdd5e0;
        font-size: 9px;
        vertical-align: middle;
    }
    table.absen tbody td.center { text-align: center; }
    table.absen tbody td.num { text-align: center; width: 30px; }
    .td-ttd { height: 30px; }

    .footer-sign {
        margin-top: 24px;
        display: table;
        width: 100%;
    }
    .sign-block { display: table-cell; text-align: center; width: 50%; }
    .sign-block .sign-title { font-size: 9px; }
    .sign-block .sign-name { margin-top: 50px; font-weight: bold; font-size: 9px; text-decoration: underline; }
    .sign-block .sign-nip { font-size: 8.5px; }

    .empty-row td { text-align: center; color: #999; padding: 20px; font-style: italic; }
    .badge-filter {
        display: inline-block;
        background: #e8f0fe;
        color: #1a1a2e;
        border-radius: 4px;
        padding: 1px 6px;
        font-size: 8px;
        margin: 0 2px;
    }
    .filter-info { text-align: center; font-size: 8px; color: #666; margin-bottom: 6px; }
</style>
</head>
<body>

{{-- ===== KOP SEKOLAH ===== --}}
<div class="kop">
    <div class="kop-logo">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo SMKN 1 Ciamis">
        @endif
    </div>
    <div class="kop-text">
        <div class="kop-dinas">Dinas Pendidikan Cabang Dinas Pendidikan Wilayah XIII</div>
        <div class="kop-sekolah">SMK Negeri 1 Ciamis</div>
        <div class="kop-alamat">Jl. Jenderal Sudirman No. 269 Telp. (0265) 771204 – Ciamis 46215</div>
        <div class="kop-alamat">Website: www.smkn1ciamis.sch.id | Email: info@smkn1ciamis.sch.id</div>
    </div>
</div>

{{-- ===== JUDUL DOKUMEN ===== --}}
<div class="doc-title">Daftar Hadir Apel</div>
<div class="doc-sub">{{ $session->title }}</div>

{{-- ===== INFO SESI ===== --}}
<div class="info-grid">
    <table>
        <tr>
            <td class="label">Kode Sesi</td>
            <td class="sep">:</td>
            <td>{{ $session->code }}</td>
            <td class="label">Jenis</td>
            <td class="sep">:</td>
            <td>{{ ucfirst($session->type) }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal</td>
            <td class="sep">:</td>
            <td>{{ $session->date->translatedFormat('l, d F Y') }}
                @if($session->valid_days > 1)
                    s/d {{ $session->end_date->translatedFormat('d F Y') }}
                @endif
            </td>
            <td class="label">Waktu</td>
            <td class="sep">:</td>
            <td>{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="label">Total Hadir</td>
            <td class="sep">:</td>
            <td>{{ $attendances->count() }} orang</td>
            <td class="label">Cetak</td>
            <td class="sep">:</td>
            <td>{{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
    </table>
</div>

{{-- ===== TABEL ABSENSI ===== --}}
<table class="absen">
    <thead>
        <tr>
            <th style="width:28px">No</th>
            <th style="width:150px">Nama</th>
            <th style="width:110px">NIP/NIK</th>
            <th style="width:90px">Jabatan</th>
            <th style="width:60px">Waktu Hadir</th>
            <th style="width:80px">Tanda Tangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($attendances as $i => $a)
        <tr>
            <td class="num">{{ $i + 1 }}</td>
            <td>{{ $a->participant->name ?? $a->participant_nik }}</td>
            <td class="center">{{ $a->participant->nip ?? $a->participant_nik }}</td>
            <td class="center">{{ $a->participant->jabatan ?? ($a->participant->role ?? '-') }}</td>
            <td class="center">{{ $a->signed_in_at->format('H:i') }}</td>
            <td class="td-ttd center">{{ $i + 1 }}.</td>
        </tr>
        @empty
        <tr class="empty-row">
            <td colspan="6">Tidak ada data kehadiran</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- ===== TANDA TANGAN ===== --}}
<div class="footer-sign">
    <div class="sign-block">
        <div class="sign-title">Mengetahui,</div>
        <div class="sign-title">Kepala SMKN 1 Ciamis</div>
        <div class="sign-name">______________________</div>
        <div class="sign-nip">NIP. ___________________</div>
    </div>
    <div class="sign-block">
        <div class="sign-title">Ciamis, {{ now()->translatedFormat('d F Y') }}</div>
        <div class="sign-title">Operator Presensi</div>
        <div class="sign-name">______________________</div>
        <div class="sign-nip">NIP. ___________________</div>
    </div>
</div>

</body>
</html>
