@extends('layouts.app')

@section('title', 'Dashboard Admin - Asign SMKN 1 Ciamis')
@section('body-class', 'admin-layout admin-sidebar-layout')

@section('content')
<div class="admin-wrapper">

    {{-- Sidebar --}}
    @include('admin.partials.sidebar', ['activePage' => 'dashboard'])

    {{-- Main Content --}}
    <div class="admin-main">

        {{-- Mobile Topbar --}}
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
                    <h1 class="page-title"><i class="fa-solid fa-gauge-high" style="color: var(--accent-indigo);"></i> Dashboard Sesi Apel</h1>
                    <p class="page-subtitle">Kelola sesi apel dan pantau kehadiran guru & peserta.</p>
                </div>
            </div>

            {{-- Alerts --}}
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif
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

            {{-- Geofence Warning --}}
            @if(!$apelLocation->isConfigured())
                <div class="alert alert-warning" style="display:flex; align-items:center; gap:0.75rem;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size:1.2rem; flex-shrink:0;"></i>
                    <div>
                        <strong>Titik apel belum dikonfigurasi!</strong>
                        Geofencing dinonaktifkan — semua peserta bisa absen dari mana saja.
                        <a href="{{ route('admin.apel.location') }}" style="color: var(--accent-indigo); font-weight: 600; text-decoration: underline;">Set titik apel sekarang →</a>
                    </div>
                </div>
            @endif

            {{-- Statistics Panel --}}
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

                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <div class="stat-title">Geofencing</div>
                        <div class="stat-value" style="font-size: 1.1rem;">
                            @if($apelLocation->isConfigured())
                                <span style="color: #10b981;">Aktif</span>
                            @else
                                <span style="color: var(--accent-rose);">Off</span>
                            @endif
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                            @if($apelLocation->isConfigured())
                                Radius {{ $apelLocation->radius_meter }}m
                            @else
                                Belum dikonfigurasi
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Analytics Charts Section (Fitur #4) --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
                
                {{-- Chart 1: Trend Kehadiran --}}
                <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div>
                            <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.15rem;">
                                <i class="fa-solid fa-chart-line" style="color: var(--accent-indigo);"></i> Tren Kehadiran Sesi
                            </h3>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">Jumlah peserta hadir 7 sesi apel terakhir</span>
                        </div>
                        <span class="badge" style="background: rgba(99,102,241,0.1); color: var(--accent-indigo); font-size: 0.75rem; font-weight: 700;">
                            Rata-rata: {{ $avgAttendanceRate }}%
                        </span>
                    </div>
                    <div style="position: relative; flex: 1; min-height: 220px;">
                        <canvas id="trendAttendanceChart"></canvas>
                    </div>
                </div>

                {{-- Chart 2: Distribusi Kategori --}}
                <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div>
                            <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.15rem;">
                                <i class="fa-solid fa-chart-pie" style="color: var(--accent-teal);"></i> Partisipasi per Kategori
                            </h3>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">Komposisi absensi bulan ini ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})</span>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 120px; align-items: center; gap: 0.5rem; position: relative; flex: 1; min-height: 220px;">
                        <div style="position: relative; height: 100%; max-height: 210px;">
                            <canvas id="categoryAttendanceChart"></canvas>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.78rem;">
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #6366f1; display: inline-block;"></span>
                                <span style="color: var(--text-muted);">Guru:</span> <strong>{{ $categoryCounts['Guru'] }}</strong>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #06b6d4; display: inline-block;"></span>
                                <span style="color: var(--text-muted);">TU:</span> <strong>{{ $categoryCounts['TU'] }}</strong>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                                <span style="color: var(--text-muted);">Wali:</span> <strong>{{ $categoryCounts['Wali Kelas'] }}</strong>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                                <span style="color: var(--text-muted);">PLP:</span> <strong>{{ $categoryCounts['PLP'] }}</strong>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #ec4899; display: inline-block;"></span>
                                <span style="color: var(--text-muted);">PPG:</span> <strong>{{ $categoryCounts['PPG'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Top 5 Disciplined Leaderboard --}}
            @if(count($topParticipants) > 0)
            <div class="card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; background: linear-gradient(135deg, rgba(99,102,241,0.03) 0%, rgba(14,165,233,0.03) 100%);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-medal" style="color: #f59e0b; font-size: 1.1rem;"></i>
                        <h4 style="font-size: 0.92rem; font-weight: 700; color: var(--text-main); margin: 0;">
                            Peserta Paling Disiplin & Rajin Hadir (30 Hari Terakhir)
                        </h4>
                    </div>
                    <a href="{{ route('admin.rekap.index') }}" style="font-size: 0.8rem; color: var(--accent-indigo); font-weight: 600; text-decoration: none;">
                        Lihat Rekap Lengkap →
                    </a>
                </div>
                <div style="display: flex; gap: 0.75rem; overflow-x: auto; padding-bottom: 0.25rem;">
                    @foreach($topParticipants as $rank => $tp)
                        <div style="background: var(--card-bg); border: 1.5px solid var(--card-border); border-radius: var(--radius-md); padding: 0.6rem 0.85rem; min-width: 190px; flex: 1; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: {{ $rank === 0 ? '#fef3c7' : ($rank === 1 ? '#f1f5f9' : ($rank === 2 ? '#ffedd5' : 'rgba(99,102,241,0.1)')) }}; color: {{ $rank === 0 ? '#b45309' : ($rank === 1 ? '#475569' : ($rank === 2 ? '#c2410c' : 'var(--accent-indigo)')) }}; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.78rem; flex-shrink: 0;">
                                #{{ $rank + 1 }}
                            </div>
                            <div style="overflow: hidden;">
                                <div style="font-weight: 700; font-size: 0.82rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $tp->participant->name ?? $tp->participant_nik }}">
                                    {{ $tp->participant->name ?? $tp->participant_nik }}
                                </div>
                                <div style="font-size: 0.72rem; color: var(--text-muted); display: flex; justify-content: space-between; gap: 0.3rem;">
                                    <span>{{ $tp->participant->role ?? 'Peserta' }}</span>
                                    <span style="color: #059669; font-weight: 700;">{{ $tp->total_attendance }}x Hadir</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Content Split Panel --}}
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

                        <div class="form-group">
                            <label class="form-label" for="valid_days">Berlaku Selama</label>
                            <select name="valid_days" id="valid_days" class="form-control form-select" required>
                                <option value="1">1 Hari (Hanya Hari Ini)</option>
                                <option value="2">2 Hari</option>
                                <option value="3">3 Hari</option>
                                <option value="5">5 Hari</option>
                                <option value="7">7 Hari (1 Minggu)</option>
                            </select>
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

                {{-- Right Side: Sessions List --}}
                <div>
                    <h3 style="margin-bottom: 1.25rem; font-size: 1.2rem; color: var(--text-main); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                        <span><i class="fa-solid fa-list" style="color: var(--accent-teal)"></i> Riwayat Sesi Apel</span>
                        <span style="font-size: 0.72rem; display: flex; align-items: center; gap: 0.8rem; font-weight: 600; background: rgba(255,255,255,0.4); padding: 0.25rem 0.6rem; border-radius: var(--radius-sm); border: 1px solid var(--input-border);">
                            <span style="display: flex; align-items: center; gap: 0.3rem;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span> Hijau = Aktif</span>
                            <span style="display: flex; align-items: center; gap: 0.3rem;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444; display: inline-block;"></span> Merah = Kadaluarsa</span>
                        </span>
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
                                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                    {{ $session->dateRangeLabel() }}
                                                    @if($session->valid_days > 1)
                                                        <span style="color: var(--accent-indigo); font-weight: 600;">({{ $session->valid_days }} hari)</span>
                                                    @endif
                                                </div>
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
                                                    @if($session->isExpired())
                                                        <code style="font-family: monospace; font-size: 1rem; font-weight: 800; background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 0.15rem 0.4rem; border-radius: 4px;" title="Sesi Kadaluarsa (Masa Berlaku Habis)">{{ $session->code }}</code>
                                                    @else
                                                        <code style="font-family: monospace; font-size: 1rem; font-weight: 800; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 0.15rem 0.4rem; border-radius: 4px;" title="Sesi Aktif (Masih Berlaku)">{{ $session->code }}</code>
                                                    @endif
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

        </div>{{-- end content-area --}}
    </div>{{-- end admin-main --}}
</div>{{-- end admin-wrapper --}}

<script>
const defaultTimes = {
    pagi: { start: '06:20', end: '06:40' },
    sore: { start: '14:50', end: '15:20' },
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
    document.querySelectorAll('.dropdown-menu-content').forEach(el => {
        if (el.id !== id) el.style.display = 'none';
    });
    const dropdown = document.getElementById(id);
    if (dropdown) {
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }
}

window.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-menu-content').forEach(el => {
        el.style.display = 'none';
    });
});

function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const isOpen  = sidebar.classList.contains('open');
    sidebar.classList.toggle('open', !isOpen);
    overlay.classList.toggle('active', !isOpen);
}
</script>

{{-- Chart.js CDN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Trend Line & Bar Chart
    const trendCtx = document.getElementById('trendAttendanceChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        type: 'line',
                        label: 'Tingkat Kehadiran (%)',
                        data: {!! json_encode($chartRates) !!},
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#4f46e5',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        yAxisID: 'y1',
                        tension: 0.35,
                        fill: false
                    },
                    {
                        type: 'bar',
                        label: 'Jumlah Hadir (Orang)',
                        data: {!! json_encode($chartData) !!},
                        backgroundColor: 'rgba(6, 182, 212, 0.65)',
                        borderColor: '#0891b2',
                        borderWidth: 1,
                        borderRadius: 6,
                        yAxisID: 'y',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { size: 11, family: 'Inter, sans-serif' }
                        }
                    },
                    tooltip: {
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: { display: true, text: 'Jumlah Orang', font: { size: 10 } },
                        grid: { color: 'rgba(0,0,0,0.04)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        min: 0,
                        max: 100,
                        grid: { drawOnChartArea: false },
                        ticks: {
                            callback: function(value) { return value + '%'; }
                        },
                        title: { display: true, text: 'Persentase %', font: { size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10.5 } }
                    }
                }
            }
        });
    }

    // 2. Category Doughnut Chart
    const catCtx = document.getElementById('categoryAttendanceChart');
    if (catCtx) {
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: ['Guru', 'TU', 'Wali Kelas', 'PLP', 'PPG'],
                datasets: [{
                    data: [
                        {{ $categoryCounts['Guru'] }},
                        {{ $categoryCounts['TU'] }},
                        {{ $categoryCounts['Wali Kelas'] }},
                        {{ $categoryCounts['PLP'] }},
                        {{ $categoryCounts['PPG'] }}
                    ],
                    backgroundColor: [
                        '#6366f1',
                        '#06b6d4',
                        '#10b981',
                        '#f59e0b',
                        '#ec4899'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.parsed + ' absensi';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection
