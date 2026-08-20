@extends('layouts.app')

@section('title', 'Rekap Presensi Bulanan - Asign SMKN 1 Ciamis')
@section('body-class', 'admin-layout admin-sidebar-layout')

@section('content')
<div class="admin-wrapper">

    {{-- Sidebar --}}
    @include('kepsek.partials.sidebar', ['activePage' => 'rekap'])

    {{-- Main Content --}}
    <div class="admin-main">

        {{-- Global Topbar --}}
        @include('admin.partials.topbar')

        <div class="admin-content-area">

            {{-- Page Header --}}
            <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem;">
                <div>
                    <h1 class="page-title"><i class="fa-solid fa-table-list" style="color: var(--accent-indigo);"></i> Rekap Presensi Bulanan</h1>
                    <p class="page-subtitle">Pantau dan cetak matriks presensi bulanan (Guru, TU, Wali Kelas, PLP, PPG).</p>
                </div>
                <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <a href="{{ route('kepsek.rekap.preview', request()->all()) }}" target="_blank" class="btn btn-secondary btn-sm" style="display:inline-flex; align-items:center; gap:0.4rem;">
                        <i class="fa-solid fa-print"></i> Pratinjau Cetak
                    </a>
                    <a href="{{ route('kepsek.rekap.export.pdf', request()->all()) }}" class="btn btn-secondary btn-sm" style="display:inline-flex; align-items:center; gap:0.4rem; color:var(--accent-rose); border-color:rgba(244,63,94,0.3);">
                        <i class="fa-solid fa-file-pdf"></i> Unduh PDF
                    </a>
                    <a href="{{ route('kepsek.rekap.export.excel', request()->all()) }}" class="btn btn-primary btn-sm" style="display:inline-flex; align-items:center; gap:0.4rem; background:#059669; border-color:#059669;">
                        <i class="fa-solid fa-file-excel"></i> Unduh Excel
                    </a>
                </div>
            </div>

            @php
                $monthNames = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                $sessionCount = count($sessions);
                $participantCount = count($participants);
                
                // Calculate average rate
                $totalPossibleSlots = $sessionCount * $participantCount;
                $totalAttendedSlots = 0;
                foreach ($participants as $p) {
                    foreach ($sessions as $s) {
                        if (isset($matrix[$p->nik][$s->id])) {
                            $totalAttendedSlots++;
                        }
                    }
                }
                $avgMonthRate = $totalPossibleSlots > 0 ? round(($totalAttendedSlots / $totalPossibleSlots) * 100, 1) : 0;
            @endphp

            {{-- Filter Bar --}}
            <form method="GET" action="{{ route('kepsek.rekap.index') }}" id="rekapFilterForm"
                  style="background:var(--card-bg); border:1.5px solid var(--card-border); border-radius:var(--radius-md); padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; flex-wrap:wrap; gap:0.75rem; align-items:flex-end;">
                
                <div style="min-width:160px; flex:1;">
                    <label for="filter_month" style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.3rem; display:flex; align-items:center; gap:0.25rem;">
                        <i class="fa-solid fa-calendar"></i> Bulan
                    </label>
                    <select name="month" id="filter_month" aria-label="Pilih Bulan" onchange="this.form.submit()"
                            style="width:100%; padding:0.5rem 0.75rem; border:1.5px solid var(--input-border); border-radius:var(--radius-sm); background:var(--input-bg); color:var(--text-main); font-size:0.88rem;">
                        @foreach($monthNames as $mNum => $mLabel)
                            <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>{{ $mLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="min-width:130px;">
                    <label for="filter_year" style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.3rem; display:flex; align-items:center; gap:0.25rem;">
                        <i class="fa-solid fa-calendar-days"></i> Tahun
                    </label>
                    <select name="year" id="filter_year" aria-label="Pilih Tahun" onchange="this.form.submit()"
                            style="width:100%; padding:0.5rem 0.75rem; border:1.5px solid var(--input-border); border-radius:var(--radius-sm); background:var(--input-bg); color:var(--text-main); font-size:0.88rem;">
                        @for($y = \Carbon\Carbon::now()->year - 2; $y <= \Carbon\Carbon::now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div style="min-width:160px; flex:1;">
                    <label for="filter_jabatan" style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.3rem; display:flex; align-items:center; gap:0.25rem;">
                        <i class="fa-solid fa-briefcase"></i> Kategori Jabatan
                    </label>
                    <select name="jabatan" id="filter_jabatan" aria-label="Pilih Kategori Jabatan" onchange="this.form.submit()"
                            style="width:100%; padding:0.5rem 0.75rem; border:1.5px solid var(--input-border); border-radius:var(--radius-sm); background:var(--input-bg); color:var(--text-main); font-size:0.88rem;">
                        <option value="">Semua Kategori</option>
                        @foreach(['Guru', 'TU', 'Wali Kelas', 'PLP', 'PPG'] as $cat)
                            <option value="{{ $cat }}" {{ $jabatan === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display:flex; gap:0.5rem; align-items:flex-end;">
                    <button type="submit" class="btn btn-primary btn-sm" style="height:38px; padding:0 1.25rem;">
                        <i class="fa-solid fa-filter"></i> Terapkan
                    </button>
                    @if(request()->hasAny(['month', 'year', 'jabatan']))
                    <a href="{{ route('kepsek.rekap.index') }}" class="btn btn-secondary btn-sm" style="height:38px; padding:0 1rem; display:inline-flex; align-items:center; justify-content:center;">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                    @endif
                </div>
            </form>

            {{-- Summary Stats Grid --}}
            <div class="stats-grid" style="margin-bottom: 1.5rem;">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(99,102,241,0.1); color: var(--accent-indigo);">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="stat-title">Sesi Apel Terlaksana</div>
                        <div class="stat-value">{{ $sessionCount }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Bulan {{ $monthNames[$month] ?? '' }} {{ $year }}</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(14,165,233,0.1); color: var(--accent-teal);">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <div class="stat-title">Total Peserta Terdaftar</div>
                        <div class="stat-value">{{ $participantCount }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $jabatan ?: 'Semua Kategori' }}</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <div>
                        <div class="stat-title">Rata-rata Kehadiran</div>
                        <div class="stat-value" style="color: {{ $avgMonthRate >= 80 ? '#10b981' : ($avgMonthRate >= 50 ? '#f59e0b' : '#f43f5e') }};">
                            {{ $avgMonthRate }}%
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Persentase total kehadiran</div>
                    </div>
                </div>
            </div>

            {{-- Matrix Table Card --}}
            <div class="card" style="padding: 0; overflow: hidden; border: 1.5px solid var(--card-border);">
                <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--card-border); display:flex; justify-content:space-between; align-items:center; background: var(--card-bg);">
                    <div>
                        <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.2rem;">
                            Matriks Presensi: {{ $monthNames[$month] ?? '' }} {{ $year }}
                        </h2>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">
                            @if($sessionCount === 0)
                                <i class="fa-solid fa-circle-info" style="color:var(--accent-rose);"></i> Belum ada sesi apel yang dibuat pada bulan ini.
                            @else
                                Menampilkan detail kehadiran per tanggal pelaksanaan apel.
                            @endif
                        </span>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 600px; overflow: auto;">
                    <table class="table" style="margin-bottom: 0; width: 100%; border-collapse: separate; border-spacing: 0;">
                        <thead>
                            <tr style="background: #1e293b; color: #fff; position: sticky; top: 0; z-index: 10;">
                                <th style="width: 45px; text-align: center; background: #1e293b; color:#fff;">No</th>
                                <th style="min-width: 220px; background: #1e293b; color:#fff;">Nama Lengkap</th>
                                <th style="min-width: 140px; text-align: center; background: #1e293b; color:#fff;">NIP / NIM</th>
                                <th style="min-width: 150px; background: #1e293b; color:#fff;">Kategori / Jabatan</th>
                                @foreach($sessions as $s)
                                    <th style="min-width: 80px; text-align: center; background: #1e293b; color:#fff; font-size: 0.78rem;">
                                        <div>{{ \Carbon\Carbon::parse($s->date)->format('d/m') }}</div>
                                        <div style="font-size: 0.68rem; font-weight: 400; opacity: 0.8;">{{ $s->code }}</div>
                                    </th>
                                @endforeach
                                <th style="min-width: 75px; text-align: center; background: #0f172a; color:#38bdf8;">Total</th>
                                <th style="min-width: 75px; text-align: center; background: #0f172a; color:#38bdf8;">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($participants as $idx => $p)
                                @php
                                    $pAttended = 0;
                                @endphp
                                <tr style="border-bottom: 1px solid var(--input-border);">
                                    <td style="text-align: center; color: var(--text-muted); font-size: 0.85rem;">{{ $idx + 1 }}</td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main); font-size: 0.88rem;">{{ $p->name }}</div>
                                    </td>
                                    <td style="text-align: center; font-size: 0.83rem; font-family: monospace; color: var(--text-muted);">
                                        {{ $p->nip ?: ($p->other_id ?: $p->nik) }}
                                    </td>
                                    <td>
                                        <span class="badge" style="background: rgba(99,102,241,0.08); color: var(--accent-indigo); font-size: 0.75rem; border: 1px solid rgba(99,102,241,0.2);">
                                            {{ $p->jabatan ?: $p->role }}
                                        </span>
                                    </td>
                                    @foreach($sessions as $s)
                                        @php
                                            $att = $matrix[$p->nik][$s->id] ?? null;
                                        @endphp
                                        <td style="text-align: center; font-size: 0.8rem;">
                                            @if($att)
                                                @php $pAttended++; @endphp
                                                <span title="Hadir: {{ \Carbon\Carbon::parse($att->signed_in_at)->format('H:i:s') }}"
                                                      style="color: #059669; font-weight: 700; display:inline-flex; align-items:center; gap:0.2rem; background:rgba(16,185,129,0.08); padding:0.2rem 0.4rem; border-radius:4px;">
                                                    <i class="fa-solid fa-check"></i> {{ \Carbon\Carbon::parse($att->signed_in_at)->format('H:i') }}
                                                </span>
                                            @else
                                                <span style="color: var(--text-light); font-weight: 400;">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    @php
                                        $pPct = $sessionCount > 0 ? round(($pAttended / $sessionCount) * 100, 1) : 0;
                                    @endphp
                                    <td style="text-align: center; font-weight: 700; color: var(--text-main); background: rgba(0,0,0,0.01);">
                                        {{ $pAttended }}
                                    </td>
                                    <td style="text-align: center; font-weight: 700; background: rgba(0,0,0,0.01); color: {{ $pPct >= 80 ? '#059669' : ($pPct >= 50 ? '#d97706' : '#e11d48') }};">
                                        {{ $pPct }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 4 + $sessionCount + 2 }}" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                        <i class="fa-solid fa-folder-open" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.4; display:block;"></i>
                                        Tidak ada data peserta yang cocok dengan filter yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
