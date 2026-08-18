@extends('layouts.app')

@section('title', 'Dashboard Kepala Sekolah - Asign SMKN 1 Ciamis')
@section('body-class', 'admin-layout admin-sidebar-layout')

@section('content')
<div class="admin-wrapper">

    @include('kepsek.partials.sidebar', ['activePage' => 'dashboard'])

    <div class="admin-main">

        <header class="admin-mobile-topbar">
            <button class="sidebar-toggle-btn" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="mobile-topbar-title"><i class="fa-solid fa-gauge-high"></i> Dashboard</span>
        </header>

        <div class="admin-content-area">

            {{-- Page Header --}}
            <div class="page-header">
                <div>
                    <h1 class="page-title">
                        <i class="fa-solid fa-eye" style="color: var(--accent-indigo);"></i>
                        Pantau Kehadiran Guru
                    </h1>
                    <p class="page-subtitle">Tampilan read-only — riwayat sesi apel dan data peserta.</p>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            {{-- Statistics Panel --}}
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <div class="stat-title">Total Peserta Aktif</div>
                        <div class="stat-value">{{ $totalParticipants }}</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon stat-icon-teal">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="stat-title">Total Sesi Apel</div>
                        <div class="stat-value">{{ $totalSessions }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Keseluruhan</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon stat-icon-rose">
                        <i class="fa-solid fa-signature"></i>
                    </div>
                    <div>
                        <div class="stat-title">Hadir Hari Ini</div>
                        <div class="stat-value">{{ $todayAttendances }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Semua Sesi</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(124,58,237,0.1);color:#7c3aed;">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <div>
                        <div class="stat-title">Akun</div>
                        <div class="stat-value" style="font-size:1.1rem;">Kepala Sekolah</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Read-only access</div>
                    </div>
                </div>
            </div>

            {{-- Sessions List (read-only) --}}
            <div style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1.25rem; font-size: 1.2rem; color: var(--text-main); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                    <span><i class="fa-solid fa-list" style="color: var(--accent-teal)"></i> Riwayat Sesi Apel</span>
                    <span style="font-size:0.72rem; display:flex; align-items:center; gap:0.8rem; font-weight:600; background:rgba(255,255,255,0.4); padding:0.25rem 0.6rem; border-radius:var(--radius-sm); border:1px solid var(--input-border);">
                        <span style="display:flex;align-items:center;gap:0.3rem;"><span style="width:8px;height:8px;border-radius:50%;background:#10b981;display:inline-block;"></span>Hijau = Aktif</span>
                        <span style="display:flex;align-items:center;gap:0.3rem;"><span style="width:8px;height:8px;border-radius:50%;background:#ef4444;display:inline-block;"></span>Merah = Kadaluarsa</span>
                    </span>
                </h3>

                @if ($sessions->isEmpty())
                    <div style="text-align:center;padding:3rem;background:rgba(255,255,255,0.2);border-radius:var(--radius-md);border:1.5px dashed var(--input-border);">
                        <i class="fa-regular fa-folder-open" style="font-size:2.5rem;color:var(--text-light);margin-bottom:1rem;"></i>
                        <p style="color:var(--text-muted);font-weight:500;">Belum ada sesi apel.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Kegiatan / Tanggal</th>
                                    <th>Tipe</th>
                                    <th>Jam Buka</th>
                                    <th style="text-align:center;">Kode</th>
                                    <th style="text-align:center;">Kehadiran</th>
                                    <th style="text-align:right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sessions as $session)
                                <tr>
                                    <td>
                                        <div style="font-weight:700;color:var(--text-main);">{{ $session->title }}</div>
                                        <div style="font-size:0.75rem;color:var(--text-muted);">
                                            {{ $session->dateRangeLabel() }}
                                            @if($session->valid_days > 1)
                                                <span style="color:var(--accent-indigo);font-weight:600;">({{ $session->valid_days }} hari)</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $session->type === 'pagi' ? 'badge-info' : 'badge-warning' }}">
                                            {{ ucfirst($session->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size:0.85rem;font-weight:600;">
                                            {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        @if($session->isExpired())
                                            <code style="font-family:monospace;font-size:1rem;font-weight:800;background:rgba(239,68,68,0.1);color:#ef4444;padding:0.15rem 0.4rem;border-radius:4px;">{{ $session->code }}</code>
                                        @else
                                            <code style="font-family:monospace;font-size:1rem;font-weight:800;background:rgba(16,185,129,0.1);color:#10b981;padding:0.15rem 0.4rem;border-radius:4px;">{{ $session->code }}</code>
                                        @endif
                                    </td>
                                    <td style="text-align:center;font-weight:700;font-size:1.1rem;color:var(--accent-teal);">
                                        {{ $session->attendances_count }}
                                    </td>
                                    <td style="text-align:right;">
                                        <a href="{{ route('kepsek.sessions.detail', $session->id) }}"
                                           class="btn btn-secondary btn-sm"
                                           style="font-size:0.78rem; padding:0.3rem 0.75rem;">
                                            <i class="fa-solid fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-wrapper">
                        {{ $sessions->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const isOpen  = sidebar.classList.contains('open');
    sidebar.classList.toggle('open', !isOpen);
    overlay.classList.toggle('active', !isOpen);
}
</script>
@endsection
