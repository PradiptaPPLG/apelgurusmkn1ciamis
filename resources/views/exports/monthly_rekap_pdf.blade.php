<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="description" content="Rekapitulasi Daftar Hadir Apel Bulanan SMKN 1 Ciamis.">
<link rel="canonical" href="{{ url()->current() }}">
<title>Rekap Presensi Apel - Bulan {{ $month }}/{{ $year }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Times New Roman', 'DejaVu Serif', serif; font-size: 9px; color: #000; }

    @page {
        size: A4 landscape;
        margin: 8mm 10mm;
    }

    /* ── KOP ─────────────────────────────────────── */
    .kop {
        border-bottom: 2.5px solid #000;
        padding-bottom: 5px;
        margin-bottom: 8px;
        display: table;
        width: 100%;
    }
    .kop-logo { display: table-cell; width: 70px; vertical-align: middle; text-align: center; }
    .kop-logo img { width: 62px; height: auto; }
    .kop-text { display: table-cell; vertical-align: middle; text-align: center; padding: 0 8px; }
    .kop-prov  { font-size: 9px; font-weight: normal; text-transform: uppercase; letter-spacing: 0.3px; }
    .kop-dinas { font-size: 9px; font-weight: bold; text-transform: uppercase; }
    .kop-cabang{ font-size: 9px; font-weight: bold; text-transform: uppercase; }
    .kop-school{ font-size: 14px; font-weight: bold; text-transform: uppercase; margin: 2px 0 1px; }
    .kop-alamat{ font-size: 7.5px; }

    /* ── JUDUL ───────────────────────────────────── */
    .doc-title {
        text-align: center;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
        margin-top: 4px;
    }
    .doc-sub {
        text-align: center;
        font-size: 9px;
        color: #333;
        margin-bottom: 8px;
        font-style: italic;
    }

    /* ── TABEL MATRIKS ────────────────────────────── */
    .rekap-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
        font-size: 8.5px;
    }
    .rekap-table th {
        background-color: #1e293b;
        color: #ffffff;
        border: 1px solid #0f172a;
        padding: 5px 3px;
        text-align: center;
        font-weight: bold;
        font-size: 8px;
    }
    .rekap-table td {
        border: 1px solid #cbd5e1;
        padding: 4px 3px;
        vertical-align: middle;
    }
    .rekap-table tr:nth-child(even) td {
        background-color: #f8fafc;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .badge-present { color: #047857; font-weight: bold; font-size: 8px; }
    .badge-absent { color: #94a3b8; font-size: 8px; }

    /* ── TANDA TANGAN ────────────────────────────── */
    .sig-block {
        margin-top: 15px;
        width: 100%;
        display: table;
        page-break-inside: avoid;
    }
    .sig-left  { display: table-cell; width: 65%; }
    .sig-right { display: table-cell; width: 35%; text-align: center; vertical-align: top; font-size: 9px; }
    .sig-space { height: 45px; }
    .sig-name  { font-weight: bold; text-decoration: underline; }
    .sig-nip   { font-size: 8.5px; }

    /* ── PREVIEW BAR ─────────────────────────────── */
    .preview-bar {
        position: fixed;
        top: 0; left: 0; right: 0;
        background: #0f172a;
        color: #f8fafc;
        padding: 8px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-family: sans-serif;
        font-size: 13px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        z-index: 9999;
    }
    .preview-bar .btn {
        background: #6366f1;
        color: #fff;
        border: none;
        padding: 6px 14px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }
    .preview-bar .btn:hover { background: #4f46e5; }
    .preview-container {
        padding-top: 50px;
        max-width: 1050px;
        margin: 0 auto;
    }
    @media print {
        .preview-bar { display: none !important; }
        .preview-container { padding-top: 0 !important; max-width: none !important; }
    }
</style>
</head>
<body>

@if(isset($isPreview) && $isPreview)
<div class="preview-bar">
    <div style="display:flex;align-items:center;gap:8px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        <strong>Pratinjau Rekap Presensi Apel Bulanan</strong>
    </div>
    <div style="display:flex;gap:8px;">
        <button class="btn" onclick="window.print()">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
            Cetak Dokumen
        </button>
        <button class="btn" style="background:#475569;" onclick="window.close()">Tutup</button>
    </div>
</div>
@endif

<div class="{{ isset($isPreview) && $isPreview ? 'preview-container' : '' }}">

    {{-- ===== KOP SEKOLAH ===== --}}
    <div class="kop">
        <div class="kop-logo">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo Pemprov Jawa Barat">
            @endif
        </div>
        <div class="kop-text">
            <div class="kop-prov">PEMERINTAH DAERAH PROVINSI JAWA BARAT</div>
            <div class="kop-dinas">DINAS PENDIDIKAN</div>
            <div class="kop-cabang">CABANG DINAS PENDIDIKAN WILAYAH XIII</div>
            <div class="kop-school">SMK Negeri 1 Ciamis</div>
            <div class="kop-alamat">Jalan : Jenderal Sudirman Nomor : 269 Tlp. (0265) 771204</div>
            <div class="kop-alamat">Faksimile : (0265) 771204/777719  Website : www.smkn1ciamis.sch.id  E-mail : surat@smkn1cms.net</div>
            <div class="kop-alamat">Ciamis – 46215</div>
        </div>
    </div>

    @php
        $monthNames = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $monthName = $monthNames[$month] ?? '';
        $sessionCount = count($sessions);
    @endphp

    <div class="doc-title">
        REKAPITULASI DAFTAR HADIR APEL BULAN {{ strtoupper($monthName) }} {{ $year }}
        @if($jabatan) - {{ strtoupper($jabatan) }} @endif
    </div>
    <div class="doc-sub">
        Total Pelaksanaan: {{ $sessionCount }} Sesi Apel &bull; Total Peserta: {{ count($participants) }} Orang
    </div>

    {{-- ===== TABEL MATRIKS ===== --}}
    <table class="rekap-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 170px;">Nama Lengkap</th>
                <th style="width: 110px;">NIP / NIM</th>
                <th style="width: 100px;">Jabatan / Kategori</th>
                @foreach($sessions as $session)
                    <th style="min-width: 45px;">
                        Tgl {{ \Carbon\Carbon::parse($session->date)->format('d/m') }}<br>
                        <span style="font-weight: normal; font-size: 7px; opacity: 0.85;">{{ $session->code }}</span>
                    </th>
                @endforeach
                <th style="width: 45px;">Total<br>Hadir</th>
                <th style="width: 45px;">%<br>Hadir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($participants as $idx => $p)
                @php
                    $attended = 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-left" style="font-weight: 500;">{{ $p->name }}</td>
                    <td class="text-center">{{ $p->nip ?: ($p->other_id ?: $p->nik) }}</td>
                    <td class="text-left">{{ $p->jabatan ?: $p->role }}</td>
                    @foreach($sessions as $session)
                        @php
                            $att = $matrix[$p->nik][$session->id] ?? null;
                        @endphp
                        <td class="text-center">
                            @if($att)
                                @php $attended++; @endphp
                                <span class="badge-present">&#10003; {{ \Carbon\Carbon::parse($att->signed_in_at)->format('H:i') }}</span>
                            @else
                                <span class="badge-absent">-</span>
                            @endif
                        </td>
                    @endforeach
                    @php
                        $pct = $sessionCount > 0 ? round(($attended / $sessionCount) * 100, 1) : 0;
                    @endphp
                    <td class="text-center" style="font-weight: bold;">{{ $attended }}</td>
                    <td class="text-center" style="font-weight: bold; color: {{ $pct >= 80 ? '#047857' : ($pct >= 50 ? '#d97706' : '#e11d48') }};">
                        {{ $pct }}%
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 4 + $sessionCount + 2 }}" class="text-center" style="padding: 1.5rem; color: #64748b;">
                        Tidak ada data peserta untuk filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ===== TANDA TANGAN ===== --}}
    <div class="sig-block">
        <div class="sig-left"></div>
        <div class="sig-right">
            <div>Ciamis, {{ \App\Services\AttendanceExporter::formatDateSimpleId(\Carbon\Carbon::now()) }}</div>
            <div>Kepala Sekolah,</div>
            <div class="sig-space"></div>
            <div class="sig-name">{{ \App\Services\AttendanceExporter::KEPSEK_NAME }}</div>
            <div>{{ \App\Services\AttendanceExporter::KEPSEK_GOLOK }}</div>
            <div class="sig-nip">NIP. {{ \App\Services\AttendanceExporter::KEPSEK_NIP }}</div>
        </div>
    </div>

</div>

</body>
</html>
