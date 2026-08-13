@extends('layouts.app')

@section('title', 'Dashboard Admin - E-Apel SMKN 1 Ciamis')

@section('body-class', 'admin-layout')

@section('content')
<!-- Admin Navbar -->
<header class="admin-navbar">
    <div class="admin-nav-brand">
        <div class="admin-nav-logo">
            <i class="fa-solid fa-gauge"></i>
        </div>
        <div>
            <div>E-Apel Admin</div>
            <div style="font-size: 0.7rem; font-weight: 500; color: var(--text-muted);">SMKN 1 Ciamis</div>
        </div>
    </div>
    
    <nav class="admin-nav-links">
        <a href="{{ route('admin.dashboard') }}" class="admin-nav-link active">
            <i class="fa-solid fa-calendar-days"></i> Sesi Apel
        </a>
        <a href="{{ route('admin.participants') }}" class="admin-nav-link">
            <i class="fa-solid fa-users"></i> Data Guru & Peserta
        </a>
    </nav>

    <div class="admin-user-menu">
        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);" class="hide-mobile">
            {{ Auth::user()->name }}
        </span>
        <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--accent-rose); border-color: rgba(244, 63, 94, 0.2);">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </button>
        </form>
    </div>
</header>

<!-- Main Admin Area -->
<main class="glass-container-wide">
    
    <!-- Success Alert -->
    @if (session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Error Alert -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Statistics Panel -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="stat-title">Total Guru & Peserta</div>
                <div class="stat-value">{{ $totalParticipants }}</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $activeParticipants }} Aktif</div>
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
    </div>

    <!-- Content Split Panel -->
    <div class="panel-split">
        
        <!-- Left Side: Create Session Form -->
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

                <div class="form-group">
                    <label class="form-label" for="type">Tipe Waktu</label>
                    <select name="type" id="type" class="form-control form-select" required onchange="autoFillTime(this.value)">
                        <option value="pagi">Pagi (Sign-in)</option>
                        <option value="sore">Sore (Sign-out)</option>
                    </select>
                </div>

                <div class="time-grid-cols">
                    <div class="form-group">
                        <label class="form-label" for="start_time">Jam Mulai</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" value="06:20" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="end_time">Jam Selesai</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" value="06:40" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 0.5rem;">
                    <i class="fa-solid fa-save"></i> Simpan & Generate Kode
                </button>
            </form>
        </div>

        <!-- Right Side: Sessions List -->
        <div>
            <h3 style="margin-bottom: 1.25rem; font-size: 1.2rem; color: var(--text-main); display: flex; align-items: center; justify-content: space-between;">
                <span><i class="fa-solid fa-list" style="color: var(--accent-teal)"></i> Riwayat Sesi Apel</span>
            </h3>

            @if ($sessions->isEmpty())
                <div style="text-align: center; padding: 3rem; background: rgba(255,255,255,0.2); border-radius: var(--radius-md); border: 1.5px dashed var(--input-border);">
                    <i class="fa-regular fa-folder-open" style="font-size: 2.5rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                    <p style="color: var(--text-muted); font-weight: 500;">Belum ada sesi apel yang dibuat.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Kegiatan / Tanggal</th>
                                <th>Tipe</th>
                                <th>Jam Buka</th>
                                <th style="text-align: center;">Kode</th>
                                <th style="text-align: center;">Kehadiran</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sessions as $session)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-main);">{{ $session->title }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $session->date->format('d M Y') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $session->type === 'pagi' ? 'badge-info' : 'badge-warning' }}">
                                            {{ ucfirst($session->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem; font-weight: 600;">
                                            {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem;">
                                            <code style="font-family: monospace; font-size: 1rem; font-weight: 800; background: rgba(99, 102, 241, 0.1); color: var(--accent-indigo); padding: 0.15rem 0.4rem; border-radius: 4px;">{{ $session->code }}</code>
                                            <button type="button" class="btn btn-secondary btn-sm" style="font-size: 0.65rem; padding: 0.1rem 0.3rem;" onclick="copyShareLink('{{ route('apel.index', $session->code) }}')">
                                                <i class="fa-solid fa-copy"></i> Salin Link
                                            </button>
                                        </div>
                                    </td>
                                    <td style="text-align: center; font-weight: 700; font-size: 1.1rem; color: var(--accent-teal);">
                                        {{ $session->attendances_count }}
                                    </td>
                                    <td style="text-align: right; overflow: visible;">
                                        <div class="dropdown-kebab">
                                            <button class="kebab-btn" onclick="toggleDropdown(event, 'drop-{{ $session->id }}')" title="Aksi">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <div id="drop-{{ $session->id }}" class="dropdown-menu-content">
                                                <a href="{{ route('admin.sessions.detail', $session->id) }}">
                                                    <i class="fa-solid fa-eye"></i> Detail
                                                </a>
                                                <form action="{{ route('admin.sessions.delete', $session->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sesi apel ini dan semua data kehadiran di dalamnya?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="delete-btn">
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
</main>

<script>
    // Auto-fill default times based on session type
    const defaultTimes = {
        pagi: { start: '06:20', end: '06:40' },
        sore: { start: '14:50', end: '15:20' },
    };

    function autoFillTime(type) {
        const times = defaultTimes[type];
        if (times) {
            document.getElementById('start_time').value = times.start;
            document.getElementById('end_time').value = times.end;
        }
    }

    // Set default times on page load
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

    // Toggle dropdown function
    function toggleDropdown(event, id) {
        event.stopPropagation();
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu-content').forEach(el => {
            if (el.id !== id) {
                el.style.display = 'none';
            }
        });
        
        const dropdown = document.getElementById(id);
        if (dropdown) {
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }
    }

    // Close all dropdowns when clicking outside
    window.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu-content').forEach(el => {
            el.style.display = 'none';
        });
    });
</script>
@endsection
