@extends('layouts.app')

@section('title', 'Riwayat Sesi Apel - Asign SMKN 1 Ciamis')
@section('body-class', 'admin-layout admin-sidebar-layout')

@section('content')
<div class="admin-wrapper">

    {{-- Sidebar --}}
    @include('kepsek.partials.sidebar', ['activePage' => 'sessions'])

    <div class="admin-main">

        {{-- Mobile Topbar --}}
        <header class="admin-mobile-topbar">
            <button class="sidebar-toggle-btn" onclick="toggleSidebar()" aria-label="Buka Menu Sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="mobile-topbar-title"><i class="fa-solid fa-calendar-check"></i> Sesi Apel</span>
        </header>

        <div class="admin-content-area">

            {{-- Page Header --}}
            <div class="page-header">
                <div>
                    <h1 class="page-title">
                        <i class="fa-solid fa-calendar-check" style="color: var(--accent-indigo);"></i>
                        Riwayat Pelaksanaan Sesi Apel
                    </h1>
                    <p class="page-subtitle">Pantau seluruh catatan sesi apel pagi dan sore yang telah terlaksana di SMKN 1 Ciamis.</p>
                </div>
            </div>

            {{-- Sessions Table Card --}}
            <div class="card" style="padding: 1.5rem; border: 1.5px solid var(--card-border);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 1.25rem; flex-wrap:wrap; gap:0.5rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin: 0;">
                        <i class="fa-solid fa-list" style="color: var(--accent-teal)"></i> Daftar Seluruh Sesi Apel
                    </h3>
                    <span style="font-size:0.72rem; display:flex; align-items:center; gap:0.8rem; font-weight:600; background:rgba(255,255,255,0.4); padding:0.25rem 0.6rem; border-radius:var(--radius-sm); border:1px solid var(--input-border);">
                        <span style="display:flex;align-items:center;gap:0.3rem;"><span style="width:8px;height:8px;border-radius:50%;background:#10b981;display:inline-block;"></span>Hijau = Aktif</span>
                        <span style="display:flex;align-items:center;gap:0.3rem;"><span style="width:8px;height:8px;border-radius:50%;background:#ef4444;display:inline-block;"></span>Merah = Kadaluarsa</span>
                    </span>
                </div>

                @if ($sessions->isEmpty())
                    <div style="text-align:center;padding:3rem;background:rgba(255,255,255,0.2);border-radius:var(--radius-md);border:1.5px dashed var(--input-border);">
                        <i class="fa-regular fa-folder-open" style="font-size:2.5rem;color:var(--text-light);margin-bottom:1rem;"></i>
                        <p style="color:var(--text-muted);font-weight:500;">Belum ada sesi apel yang dibuat.</p>
                    </div>
                @else
                    <div class="table-responsive" style="margin-bottom: 1rem;">
                        <table class="table-custom" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="min-width: 150px;">Kegiatan / Tanggal</th>
                                    <th style="width: 10%; text-align: center;">Tipe</th>
                                    <th style="min-width: 120px; text-align: center;">Jam Buka</th>
                                    <th style="width: 14%; text-align: center;">Kode</th>
                                    <th style="width: 12%; text-align: center;">Kehadiran</th>
                                    <th style="width: 10%; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sessions as $session)
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:var(--text-main);">
                                            <a href="{{ route('kepsek.sessions.detail', $session->id) }}" style="color: inherit; text-decoration: none;">
                                                {{ $session->title }}
                                            </a>
                                        </div>
                                        <div style="font-size:0.78rem; color:var(--text-muted);">
                                            {{ $session->date->format('d M Y') }}
                                            @if($session->valid_days > 1 && $session->end_date)
                                                <span style="background:rgba(99,102,241,0.08); color:var(--accent-indigo); padding:1px 5px; border-radius:3px; font-size:0.72rem; font-weight:600;">
                                                    s/d {{ \Carbon\Carbon::parse($session->end_date)->format('d M') }} ({{ $session->valid_days }} hari)
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge {{ $session->type === 'pagi' ? 'badge-primary' : 'badge-warning' }}">
                                            {{ ucfirst($session->type) }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="font-size:0.85rem; font-weight:600;">
                                            {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        @if($session->isOpen())
                                            <code style="font-family:monospace;font-size:1rem;font-weight:800;background:rgba(16,185,129,0.1);color:#10b981;padding:0.15rem 0.4rem;border-radius:4px;">{{ $session->code }}</code>
                                        @else
                                            <code style="font-family:monospace;font-size:0.95rem;font-weight:600;background:rgba(239,68,68,0.08);color:#ef4444;padding:0.15rem 0.4rem;border-radius:4px;">{{ $session->code }}</code>
                                        @endif
                                    </td>
                                    <td style="text-align:center;font-weight:700;font-size:1.1rem;color:var(--accent-teal);">
                                        {{ $session->attendances_count }}
                                    </td>
                                    <td style="text-align:center;">
                                        <a href="{{ route('kepsek.sessions.detail', $session->id) }}"
                                           class="btn btn-secondary btn-sm"
                                           style="font-size:0.78rem; padding:0.35rem 0.85rem; display:inline-flex; align-items:center; gap:0.3rem;">
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
