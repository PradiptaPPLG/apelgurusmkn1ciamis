@extends('layouts.app')

@section('title', 'Kelola Sesi Apel - Asign SMKN 1 Ciamis')
@section('body-class', 'admin-layout admin-sidebar-layout')

@section('content')
<div class="admin-wrapper">

    {{-- Sidebar --}}
    @include('admin.partials.sidebar', ['activePage' => 'sessions'])

    {{-- Main Content Area --}}
    <div class="admin-main">

        {{-- Global Topbar --}}
        @include('admin.partials.topbar')

        <div class="admin-content-area">

            {{-- Page Header --}}
            <div class="page-header">
                <div>
                    <h1 class="page-title"><i class="fa-solid fa-calendar-check" style="color: var(--accent-indigo)"></i> Manajemen Sesi Apel</h1>
                    <p class="page-subtitle">Buat sesi absensi apel baru dan kelola seluruh riwayat pelaksanaan apel.</p>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            {{-- Content Split Panel (Form Kiri & Tabel Kanan) --}}
            <div class="panel-split">

                {{-- Left Side: Create Session Form --}}
                <div class="form-modal-inline">
                    <h3 style="margin-bottom: 1.25rem; font-size: 1.2rem; color: var(--text-main); border-bottom: 1px solid var(--input-border); padding-bottom: 0.5rem;">
                        <i class="fa-solid fa-plus-circle" style="color: var(--accent-indigo)"></i> Buat Sesi Apel Baru
                    </h3>

                    <form action="{{ route('admin.sessions.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label class="form-label" for="title">Judul Kegiatan</label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="Contoh: Apel Pagi Guru" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="date">Tanggal</label>
                            <input type="date" name="date" id="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        {{-- Multi-day validity --}}
                        <div class="form-group">
                            <label class="form-label" for="valid_days">Berlaku Selama</label>
                            <select name="valid_days" id="valid_days" class="form-control">
                                <option value="1" selected>1 Hari (Hanya Hari Ini)</option>
                                <option value="2">2 Hari</option>
                                <option value="3">3 Hari</option>
                                <option value="4">4 Hari</option>
                                <option value="5">5 Hari</option>
                                <option value="6">6 Hari</option>
                                <option value="7">7 Hari (1 Minggu)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="type">Tipe Waktu</label>
                            <select name="type" id="type" class="form-control" onchange="autoFillTime(this.value)">
                                <option value="pagi">Pagi (Sign-in)</option>
                                <option value="sore">Sore (Sign-out)</option>
                            </select>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label" for="start_time">Jam Mulai</label>
                                <input type="time" name="start_time" id="start_time" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="end_time">Jam Selesai</label>
                                <input type="time" name="end_time" id="end_time" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan & Generate Kode
                        </button>
                    </form>
                </div>

                {{-- Right Side: Active/Past Sessions List Table --}}
                <div>
                    <h3 style="margin-bottom: 1.25rem; font-size: 1.2rem; color: var(--text-main); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                        <span><i class="fa-solid fa-list" style="color: var(--accent-teal)"></i> Riwayat Sesi Apel</span>
                        <span style="font-size:0.72rem; display:flex; align-items:center; gap:0.8rem; font-weight:600; background:rgba(255,255,255,0.4); padding:0.25rem 0.6rem; border-radius:var(--radius-sm); border:1px solid var(--input-border);">
                            <span style="display:flex;align-items:center;gap:0.3rem;"><span style="width:8px;height:8px;border-radius:50%;background:#10b981;display:inline-block;"></span>Hijau = Aktif</span>
                            <span style="display:flex;align-items:center;gap:0.3rem;"><span style="width:8px;height:8px;border-radius:50%;background:#ef4444;display:inline-block;"></span>Merah = Kadaluarsa</span>
                        </span>
                    </h3>

                    @if ($sessions->isEmpty())
                        <div style="text-align: center; padding: 3rem; background: rgba(255, 255, 255, 0.2); border-radius: var(--radius-md); border: 1.5px dashed var(--input-border);">
                            <i class="fa-regular fa-folder-open" style="font-size: 2.5rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                            <p style="color: var(--text-muted); font-weight: 500;">Belum ada sesi apel. Silakan buat di sebelah kiri.</p>
                        </div>
                    @else
                        <div class="table-responsive" style="margin-bottom: 1rem;">
                            <table class="table-custom" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="min-width: 140px;">Kegiatan / Tanggal</th>
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
                                            <div style="font-weight: 700; color: var(--text-main);">
                                                <a href="{{ route('admin.sessions.detail', $session->id) }}" style="color: inherit; text-decoration: none;">
                                                    {{ $session->title }}
                                                </a>
                                            </div>
                                            <div style="font-size: 0.78rem; color: var(--text-muted);">
                                                {{ $session->date->format('d M Y') }}
                                                @if($session->valid_days > 1 && $session->end_date)
                                                    <span style="background: rgba(99,102,241,0.08); color: var(--accent-indigo); padding: 1px 5px; border-radius: 3px; font-size: 0.72rem; font-weight: 600;">
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
                                            <span style="font-size: 0.85rem; font-weight: 600;">
                                                {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            @if($session->isOpen())
                                                <div style="display:inline-flex; flex-direction:column; align-items:center; gap:2px;">
                                                    <code style="font-family: monospace; font-size: 1rem; font-weight: 800; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 0.15rem 0.4rem; border-radius: 4px;">{{ $session->code }}</code>
                                                    <button type="button"
                                                            onclick="copyShareLink('{{ route('apel.index', $session->code) }}')"
                                                            title="Salin Link Presensi"
                                                            style="background:none; border:none; color:var(--accent-indigo); font-size:0.68rem; font-weight:600; cursor:pointer; padding:0; display:flex; align-items:center; gap:2px;">
                                                        <i class="fa-solid fa-copy"></i> Salin Link
                                                    </button>
                                                </div>
                                            @else
                                                <div style="display:inline-flex; flex-direction:column; align-items:center; gap:2px;">
                                                    <code style="font-family: monospace; font-size: 0.95rem; font-weight: 600; background: rgba(239, 68, 68, 0.08); color: #ef4444; padding: 0.15rem 0.4rem; border-radius: 4px;">{{ $session->code }}</code>
                                                    <span style="font-size:0.65rem; color:var(--text-light);">Ditutup</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td style="text-align: center; font-weight: 700; font-size: 1.1rem; color: var(--accent-teal);">
                                            {{ $session->attendances_count }}
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="dropdown-menu-wrapper" style="position: relative; display: inline-block;">
                                                <button class="btn btn-secondary btn-icon"
                                                        aria-label="Menu Aksi Sesi {{ $session->title }}"
                                                        onclick="toggleDropdown(event, 'dd-{{ $session->id }}')">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <div id="dd-{{ $session->id }}" class="dropdown-menu-content">
                                                    <a href="{{ route('admin.sessions.detail', $session->id) }}" class="dropdown-item">
                                                        <i class="fa-solid fa-eye"></i> Detail
                                                    </a>
                                                    <a href="{{ route('admin.sessions.export.pdf', $session->id) }}" class="dropdown-item">
                                                        <i class="fa-solid fa-file-pdf"></i> Unduh PDF
                                                    </a>
                                                    <a href="{{ route('admin.sessions.export.excel', $session->id) }}" class="dropdown-item">
                                                        <i class="fa-solid fa-file-excel"></i> Unduh Excel
                                                    </a>
                                                    <a href="{{ route('admin.sessions.preview', $session->id) }}" class="dropdown-item" target="_blank">
                                                        <i class="fa-solid fa-print"></i> Pratinjau
                                                    </a>
                                                    <form action="{{ route('admin.sessions.delete', $session->id) }}" method="POST" onsubmit="return confirm('Hapus sesi ini dan semua data kehadirannya?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" style="width: 100%; text-align: left; background: none; border: none;">
                                                            <i class="fa-solid fa-trash"></i> Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
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
</div>

<script>
const defaultTimes = {
    pagi: { start: '{{ $appSetting->default_pagi_start ?? "06:20" }}', end: '{{ $appSetting->default_pagi_end ?? "06:40" }}' },
    sore: { start: '{{ $appSetting->default_sore_start ?? "14:50" }}', end: '{{ $appSetting->default_sore_end ?? "15:20" }}' },
};

function autoFillTime(type) {
    const times = defaultTimes[type];
    if (times) {
        document.getElementById('start_time').value = times.start;
        document.getElementById('end_time').value   = times.end;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById('type');
    if (typeSelect) autoFillTime(typeSelect.value);
});

function copyShareLink(link) {
    navigator.clipboard.writeText(link).then(() => {
        alert('Link absensi berhasil disalin ke clipboard!\n' + link);
    }).catch(err => {
        alert('Gagal menyalin link: ' + err);
    });
}

function toggleDropdown(event, id) {
    event.stopPropagation();
    const dropdown = document.getElementById(id);
    if (!dropdown) return;
    
    const isCurrentlyOpen = dropdown.classList.contains('dropdown-open');
    
    // Close all open dropdowns
    document.querySelectorAll('.dropdown-menu-content').forEach(el => {
        el.classList.remove('dropdown-open');
        el.style.display = 'none';
    });
    
    if (!isCurrentlyOpen) {
        const btn = event.currentTarget;
        const rect = btn.getBoundingClientRect();
        
        // Move dropdown to body so it completely breaks out of table overflow
        if (dropdown.parentElement !== document.body) {
            document.body.appendChild(dropdown);
        }
        
        dropdown.style.display = 'block';
        dropdown.style.position = 'fixed';
        dropdown.style.zIndex = '999999';
        dropdown.style.right = (window.innerWidth - rect.right) + 'px';
        dropdown.style.left = 'auto';
        
        const spaceBelow = window.innerHeight - rect.bottom;
        if (spaceBelow < 200 && rect.top > 200) {
            dropdown.style.top = 'auto';
            dropdown.style.bottom = (window.innerHeight - rect.top + 6) + 'px';
        } else {
            dropdown.style.top = (rect.bottom + 6) + 'px';
            dropdown.style.bottom = 'auto';
        }
        
        dropdown.classList.add('dropdown-open');
    }
}

// Close dropdown on click outside or scroll
window.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-menu-content').forEach(el => {
        el.classList.remove('dropdown-open');
        el.style.display = 'none';
    });
});

window.addEventListener('scroll', () => {
    document.querySelectorAll('.dropdown-menu-content').forEach(el => {
        el.classList.remove('dropdown-open');
        el.style.display = 'none';
    });
}, { passive: true });

function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const isOpen  = sidebar.classList.contains('open');
    sidebar.classList.toggle('open', !isOpen);
    overlay.classList.toggle('active', !isOpen);
}
</script>
@endsection
