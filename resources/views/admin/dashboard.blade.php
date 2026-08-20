@extends('layouts.app')

@section('title', 'Dashboard Utama - Asign SMKN 1 Ciamis')
@section('body-class', 'admin-layout admin-sidebar-layout')

@section('content')
<div class="admin-wrapper">

    {{-- Sidebar --}}
    @include('admin.partials.sidebar', ['activePage' => 'dashboard'])

    {{-- Main Content Area --}}
    <div class="admin-main">

        {{-- Global Topbar --}}
        @include('admin.partials.topbar')

        <div class="admin-content-area">

            {{-- Page Header --}}
            <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem;">
                <div>
                    <h1 class="page-title"><i class="fa-solid fa-chart-pie" style="color: var(--accent-indigo)"></i> Dashboard Utama</h1>
                    <p class="page-subtitle">Ringkasan kehadiran hari ini, statistik kepegawaian, dan analisis visual apel.</p>
                </div>
                <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <a href="{{ route('admin.sessions.index') }}" class="btn btn-primary btn-sm" style="display:inline-flex; align-items:center; gap:0.4rem;">
                        <i class="fa-solid fa-calendar-plus"></i> Kelola &amp; Buat Sesi
                    </a>
                    <a href="{{ route('admin.rekap.index') }}" class="btn btn-secondary btn-sm" style="display:inline-flex; align-items:center; gap:0.4rem;">
                        <i class="fa-solid fa-table-list"></i> Rekap Bulanan
                    </a>
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

            {{-- LIVE ACTIVE SESSION BANNER (Jika sedang ada sesi buka hari ini) --}}
            @if($currentOpenSession)
                <div class="card" style="background: linear-gradient(135deg, rgba(16,185,129,0.08) 0%, rgba(99,102,241,0.08) 100%); border: 1.5px solid rgba(16,185,129,0.4); margin-bottom: 1.5rem; padding: 1.25rem 1.5rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
                        <div style="display:flex; align-items:center; gap:1rem;">
                            <div style="width:48px; height:48px; border-radius:12px; background:#10b981; color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.4rem; box-shadow:0 4px 12px rgba(16,185,129,0.3);">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                            <div>
                                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.2rem;">
                                    <span style="display:inline-block; width:9px; height:9px; border-radius:50%; background:#10b981; animation: pulse 2s infinite;"></span>
                                    <span style="font-size:0.75rem; font-weight:800; text-transform:uppercase; color:#047857; letter-spacing:0.5px;">Sesi Apel Sedang Berlangsung</span>
                                </div>
                                <h3 style="font-size:1.15rem; font-weight:700; color:var(--text-main); margin:0;">
                                    {{ $currentOpenSession->title }}
                                </h3>
                                <div style="font-size:0.8rem; color:var(--text-muted); margin-top:0.2rem;">
                                    Jam Operasional: <strong>{{ \Carbon\Carbon::parse($currentOpenSession->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($currentOpenSession->end_time)->format('H:i') }}</strong> &bull; Total Hadir: <strong>{{ $currentOpenSession->attendances_count }} orang</strong>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                            <div style="text-align:center; background:var(--card-bg); padding:0.4rem 0.8rem; border-radius:var(--radius-sm); border:1px solid var(--input-border);">
                                <div style="font-size:0.68rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Kode Sesi</div>
                                <code style="font-size:1.15rem; font-weight:800; color:#10b981; font-family:monospace;">{{ $currentOpenSession->code }}</code>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="copyShareLink('{{ route('apel.index', $currentOpenSession->code) }}')" style="display:inline-flex; align-items:center; gap:0.4rem;">
                                <i class="fa-solid fa-copy"></i> Salin Link
                            </button>
                            <a href="{{ route('admin.sessions.detail', $currentOpenSession->id) }}" class="btn btn-primary btn-sm" style="display:inline-flex; align-items:center; gap:0.4rem;">
                                <i class="fa-solid fa-eye"></i> Detail Sesi
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Statistics Panel --}}
            <div class="stats-grid" style="margin-bottom: 1.5rem;">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <div class="stat-title">Total Guru &amp; Peserta</div>
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
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Semua Sesi Hari Ini</div>
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
                            Peserta Paling Disiplin &amp; Rajin Hadir (30 Hari Terakhir)
                        </h4>
                    </div>
                    <a href="{{ route('admin.rekap.index') }}" style="font-size: 0.8rem; color: var(--accent-indigo); font-weight: 600; text-decoration: none;">
                        Lihat Rekap Lengkap &rarr;
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

            {{-- Recent Sessions Summary Card --}}
            <div class="card" style="padding: 1.25rem 1.5rem; border: 1.5px solid var(--card-border);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 1rem; flex-wrap:wrap; gap:0.5rem;">
                    <div>
                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin: 0;">
                            <i class="fa-solid fa-clock-rotate-left" style="color: var(--accent-indigo)"></i> Sesi Apel Terakhir
                        </h3>
                        <span style="font-size: 0.78rem; color: var(--text-muted);">Ringkasan pelaksanaan apel terbaru</span>
                    </div>
                    <a href="{{ route('admin.sessions.index') }}" class="btn btn-secondary btn-sm" style="font-size: 0.8rem; display:inline-flex; align-items:center; gap:0.3rem;">
                        <span>Buka Kelola Sesi</span> <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                @if($recentSessions->isEmpty())
                    <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        Belum ada riwayat sesi apel. Silakan buat sesi di menu <strong>Sesi Apel</strong>.
                    </div>
                @else
                    <div class="table-responsive" style="margin-bottom: 0;">
                        <table class="table-custom" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="min-width: 140px;">Kegiatan</th>
                                    <th style="width: 14%;">Tanggal</th>
                                    <th style="width: 10%; text-align: center;">Tipe</th>
                                    <th style="min-width: 120px; text-align: center;">Jam Buka</th>
                                    <th style="width: 12%; text-align: center;">Kode</th>
                                    <th style="width: 12%; text-align: center;">Kehadiran</th>
                                    <th style="width: 10%; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSessions as $rs)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-main);">
                                            <a href="{{ route('admin.sessions.detail', $rs->id) }}" style="color: inherit; text-decoration: none;">
                                                {{ $rs->title }}
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.83rem; color: var(--text-muted);">{{ $rs->date->format('d M Y') }}</span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge {{ $rs->type === 'pagi' ? 'badge-primary' : 'badge-warning' }}">
                                            {{ ucfirst($rs->type) }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="font-size: 0.83rem; font-weight: 600;">
                                            {{ \Carbon\Carbon::parse($rs->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($rs->end_time)->format('H:i') }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        @if($rs->isOpen())
                                            <code style="font-family: monospace; font-size: 0.95rem; font-weight: 800; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 0.15rem 0.4rem; border-radius: 4px;">{{ $rs->code }}</code>
                                        @else
                                            <code style="font-family: monospace; font-size: 0.95rem; font-weight: 600; background: rgba(239, 68, 68, 0.08); color: #ef4444; padding: 0.15rem 0.4rem; border-radius: 4px;">{{ $rs->code }}</code>
                                        @endif
                                    </td>
                                    <td style="text-align: center; font-weight: 700; font-size: 1rem; color: var(--accent-teal);">
                                        {{ $rs->attendances_count }}
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="{{ route('admin.sessions.detail', $rs->id) }}" class="btn btn-secondary btn-sm" style="font-size: 0.78rem; padding: 0.3rem 0.75rem; display:inline-flex; align-items:center; gap:0.25rem;">
                                            <i class="fa-solid fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<script>
function copyShareLink(link) {
    navigator.clipboard.writeText(link).then(() => {
        alert('Link absensi berhasil disalin ke clipboard!\n' + link);
    }).catch(err => {
        alert('Gagal menyalin link: ' + err);
    });
}

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
