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

            {{-- Analytics Charts Section (Fitur #4) --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-top: 1.5rem; margin-bottom: 1.5rem;">
                
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
                    <a href="{{ route('kepsek.rekap.index') }}" style="font-size: 0.8rem; color: var(--accent-indigo); font-weight: 600; text-decoration: none;">
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

            {{-- Sessions List (read-only) --}}
            <div style="margin-top: 1.5rem;">
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
