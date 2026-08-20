<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Times New Roman', 'DejaVu Serif', serif; font-size: 10px; color: #000; }

    /* ── KOP ─────────────────────────────────────── */
    .kop {
        border-bottom: 2.5px solid #000;
        padding-bottom: 6px;
        margin-bottom: 10px;
        display: table;
        width: 100%;
    }
    .kop-logo { display: table-cell; width: 76px; vertical-align: middle; text-align: center; }
    .kop-logo img { width: 68px; height: auto; }
    .kop-text { display: table-cell; vertical-align: middle; text-align: center; padding: 0 8px; }
    .kop-prov  { font-size: 9.5px; font-weight: normal; text-transform: uppercase; letter-spacing: 0.3px; }
    .kop-dinas { font-size: 9.5px; font-weight: bold; text-transform: uppercase; }
    .kop-cabang{ font-size: 9.5px; font-weight: bold; text-transform: uppercase; }
    .kop-school{ font-size: 15px;  font-weight: bold; text-transform: uppercase; margin: 3px 0 2px; }
    .kop-alamat{ font-size: 8px; }

    /* ── JUDUL ───────────────────────────────────── */
    .doc-title {
        text-align: center;
        font-size: 13px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 10px 0 3px;
        text-decoration: underline;
    }
    .doc-sub {
        text-align: center;
        font-size: 10px;
        margin-bottom: 10px;
    }

    /* ── INFO GRID ───────────────────────────────── */
    .info-grid { margin-bottom: 10px; }
    .info-grid table { width: 100%; }
    .info-grid td { padding: 1.5px 4px; font-size: 9.5px; }
    .info-grid .lbl { width: 110px; font-weight: bold; }
    .info-grid .sep { width: 10px; }

    /* ── TABEL ABSENSI ───────────────────────────── */
    table.absen { width: 100%; border-collapse: collapse; margin-top: 6px; }
    table.absen thead tr { background-color: #000; color: #fff; }
    table.absen thead th {
        padding: 5px 6px;
        text-align: center;
        font-size: 9.5px;
        border: 1px solid #000;
    }
    table.absen tbody tr:nth-child(even) { background-color: #f5f5f5; }
    table.absen tbody td {
        padding: 4.5px 6px;
        border: 1px solid #555;
        font-size: 9px;
        vertical-align: middle;
    }
    table.absen tbody td.center { text-align: center; }
    table.absen tbody td.num    { text-align: center; width: 28px; }
    .td-ttd { height: 28px; }
    .empty-row td { text-align: center; color: #888; padding: 18px; font-style: italic; }

    /* ── SIGNATURE ───────────────────────────────── */
    .footer-sign {
        margin-top: 20px;
        display: table;
        width: 100%;
    }
    .sign-spacer { display: table-cell; width: 55%; }
    .sign-block  { display: table-cell; width: 45%; text-align: center; }
    .sign-block .sign-title  { font-size: 9.5px; }
    .sign-block .sign-space  { height: 50px; }
    .sign-block .sign-name   { font-weight: bold; font-size: 9.5px; text-decoration: underline; }
    .sign-block .sign-golok  { font-size: 9px; }
    .sign-block .sign-nip    { font-size: 9px; }

    /* ── FILTER BADGE ────────────────────────────── */
    .badge-filter {
        display: inline-block;
        background: #e8f0fe;
        color: #1a1a2e;
        border-radius: 4px;
        padding: 1px 6px;
        font-size: 8px;
        margin: 0 2px;
    }
    .filter-info { text-align: center; font-size: 8px; color: #555; margin-bottom: 6px; }

    /* ── PREVIEW ─────────────────────────────────── */
    @page {
        size: A4 portrait;
        margin: 15mm 15mm 20mm 15mm;
    }
    @media print {
        .no-print { display: none !important; }
        body { background: #fff !important; padding: 0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .preview-container { border: none !important; box-shadow: none !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; padding: 0 !important; }
        table.absen tbody tr:nth-child(even) { background-color: #f5f5f5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        table.absen thead tr { background-color: #000 !important; color: #fff !important; }
    }
    @media screen {
        body.preview-mode { background: #f1f5f9; padding: 2rem 1rem; }
        .preview-container {
            background: #fff;
            width: 100%; max-width: 820px;
            margin: 0 auto 2rem;
            padding: 3rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgb(0 0 0/0.1), 0 2px 4px -2px rgb(0 0 0/0.1);
            border-radius: 8px;
        }
        .preview-toolbar {
            max-width: 820px; margin: 0 auto 1rem;
            display: flex; justify-content: space-between; align-items: center;
            background: #1e293b; color: #fff;
            padding: 0.75rem 1.5rem; border-radius: 8px;
            font-family: system-ui, -apple-system, sans-serif; font-size: 14px;
        }
        .preview-btn {
            background: #3b82f6; color: #fff; border: none;
            padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer;
            font-weight: 600; text-decoration: none;
            display: inline-flex; align-items: center; gap: 0.5rem; font-size: 13px;
        }
        .preview-btn:hover { background: #2563eb; }
        .preview-btn-sec { background: #475569; }
        .preview-btn-sec:hover { background: #334155; }
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="{{ isset($isPreview) && $isPreview ? 'preview-mode' : '' }}">

@if(isset($isPreview) && $isPreview)
    <div class="preview-toolbar no-print">
        <div style="font-weight: 600;">
            <i class="fa-solid fa-eye"></i> Pratinjau Laporan Presensi
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="window.print()" class="preview-btn">
                <i class="fa-solid fa-print"></i> Cetak Laporan
            </button>
            <button onclick="window.close()" class="preview-btn preview-btn-sec">
                <i class="fa-solid fa-xmark"></i> Tutup
            </button>
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

{{-- ===== JUDUL ===== --}}
@php
    $jabatanFilter = request('jabatan', '');
    $jabatanLower  = strtolower(trim($jabatanFilter));
    $isPPG         = in_array($jabatanLower, ['ppl', 'ppg', 'plp', 'gema upi']);
    $isTU          = in_array($jabatanLower, ['tu', 'tutt', 'tut', 'tu tt']);
    
    if ($jabatanLower === 'wali kelas') {
        $docTitle = 'DAFTAR HADIR APEL WALI KELAS';
    } elseif ($isPPG) {
        $docTitle = 'DAFTAR HADIR APEL ' . strtoupper($jabatanFilter ?: 'PLP/PPG');
    } elseif ($isTU) {
        $docTitle = 'DAFTAR HADIR APEL TATA USAHA (TU)';
    } elseif ($jabatanFilter) {
        $docTitle = 'DAFTAR HADIR APEL ' . strtoupper($jabatanFilter);
    } else {
        $docTitle = 'DAFTAR HADIR APEL';
    }
@endphp
<div class="doc-title">{{ $docTitle }}</div>
<div class="doc-sub">{{ $session->title }}</div>

{{-- ===== INFO SESI ===== --}}
<div class="info-grid">
    <table>
        <tr>
            <td class="lbl">Kode Sesi</td>
            <td class="sep">:</td>
            <td>{{ $session->code }}</td>
            <td class="lbl">Waktu</td>
            <td class="sep">:</td>
            <td>{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="lbl">Hari/Tanggal</td>
            <td class="sep">:</td>
            <td>
                @php
                    $days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                    $months = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                    $d = $session->date;
                    echo $days[$d->dayOfWeek] . ', ' . $d->format('d') . ' ' . $months[(int)$d->format('n')] . ' ' . $d->format('Y');
                    if ($session->valid_days > 1) {
                        echo ' s/d ' . $session->end_date->format('d') . ' ' . $months[(int)$session->end_date->format('n')] . ' ' . $session->end_date->format('Y');
                    }
                @endphp
            </td>
            <td class="lbl">Total Hadir</td>
            <td class="sep">:</td>
            <td>{{ $attendances->count() }} orang</td>
        </tr>
    </table>
</div>

{{-- Active filter badge --}}
@if(request()->filled('jabatan') || request()->filled('search') || request()->filled('date_from'))
<div class="filter-info">
    <i class="fa-solid fa-filter" style="margin-right:3px;"></i>Filter aktif:
    @if(request()->filled('jabatan'))
        <span class="badge-filter">Kategori / Jabatan: {{ request('jabatan') }}</span>
    @endif
    @if(request()->filled('search'))
        <span class="badge-filter">Nama: {{ request('search') }}</span>
    @endif
    @if(request()->filled('date_from') || request()->filled('date_to'))
        <span class="badge-filter">Tanggal: {{ request('date_from','—') }} s/d {{ request('date_to','—') }}</span>
    @endif
</div>
@endif

{{-- ===== TABEL ABSENSI ===== --}}
<table class="absen">
    <thead>
        <tr>
            <th style="width:26px">No</th>
            <th style="width:160px">Nama</th>
            <th style="width:110px">{{ $isPPG ? 'NIM' : 'NIP / NIK' }}</th>
            <th style="width:100px">{{ $isPPG ? 'Kategori' : 'Jabatan' }}</th>
            <th style="width:60px">Waktu Hadir</th>
            <th style="width:80px">Tanda Tangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($attendances as $i => $a)
        @php
            $p = $a->participant;
            $isRowPPG = $isPPG || in_array(strtoupper($p->role ?? ''), ['PLP', 'PPG', 'PPL']);
            $idNumber = $isRowPPG ? ($p->other_id ?? ($p->nip ?? $a->participant_nik)) : ($p->nip ?? $a->participant_nik);
            if ($isRowPPG) {
                $jabatanVal = in_array(strtoupper($p->role ?? ''), ['PLP', 'PPG', 'PPL']) ? strtoupper($p->role) : ($p->jabatan ?? ($p->role ?? '-'));
            } else {
                $jabatanVal = $p->jabatan ?? ($p->role ?? '-');
            }
        @endphp
        <tr>
            <td class="num">{{ $i + 1 }}</td>
            <td>{{ $p->name ?? $a->participant_nik }}</td>
            <td class="center">{{ $idNumber }}</td>
            <td class="center">{{ $jabatanVal }}</td>
            <td class="center">{{ $a->signed_in_at->format('H:i') }}</td>
            <td class="td-ttd center"></td>{{-- Tanda Tangan dikosongkan --}}
        </tr>
        @empty
        <tr class="empty-row">
            <td colspan="6">Tidak ada data kehadiran</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- ===== TANDA TANGAN — hanya Kepala Sekolah di kanan ===== --}}
<div class="footer-sign">
    <div class="sign-spacer"></div>
    <div class="sign-block">
        @php
            $now = \Carbon\Carbon::now();
            $months = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            $tglCetak = $now->format('d') . ' ' . $months[(int)$now->format('n')] . ' ' . $now->format('Y');
        @endphp
        <div class="sign-title">Ciamis, {{ $tglCetak }}</div>
        <div class="sign-title">Kepala Sekolah,</div>
        <div class="sign-space"></div>
        <div class="sign-name">H. Cepy Wahyudin, A.Md., S.Kom., M.Kom.</div>
        <div class="sign-golok">Penata Tk. I/III/d</div>
        <div class="sign-nip">NIP. 198408252010011010</div>
    </div>
</div>

</div>
</body>
</html>
