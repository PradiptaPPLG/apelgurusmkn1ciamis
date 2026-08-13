@extends('layouts.app')

@section('title', 'Detail Presensi - E-Apel SMKN 1 Ciamis')

@section('body-class', 'admin-layout')

@section('content')
<style>
    /* Modal Backdrop for Image Zoom */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(8px);
        z-index: 999;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease-out;
    }
    
    .modal-content {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        width: 90%;
        max-width: 450px;
        padding: 2rem;
        box-shadow: var(--card-shadow);
        text-align: center;
        animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .thumbnail-zoom {
        cursor: pointer;
        transition: var(--transition-smooth);
        border: 1px solid var(--input-border);
        border-radius: var(--radius-sm);
        background: white;
    }

    .thumbnail-zoom:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
</style>

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
    
    <!-- Top Row Navigation & Actions -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>
        
        <a href="{{ route('admin.sessions.export', $session->id) }}" class="btn btn-primary">
            <i class="fa-solid fa-file-csv"></i> Unduh Laporan (CSV)
        </a>
    </div>

    <!-- Session Info Header Card -->
    <div style="background: rgba(99, 102, 241, 0.05); border: 1.5px solid rgba(99, 102, 241, 0.15); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 2rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Nama Kegiatan</div>
            <div style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin-top: 0.25rem;">{{ $session->title }}</div>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Tanggal & Waktu</div>
            <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-top: 0.25rem;">
                {{ $session->date->format('d M Y') }} 
                <span style="font-size: 0.85rem; font-weight: 500; color: var(--text-muted);">
                    ({{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }})
                </span>
            </div>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Kode Registrasi</div>
            <div style="margin-top: 0.25rem;">
                <code style="font-family: monospace; font-size: 1.25rem; font-weight: 800; background: rgba(99, 102, 241, 0.15); color: var(--accent-indigo); padding: 0.2rem 0.6rem; border-radius: var(--radius-sm);">{{ $session->code }}</code>
            </div>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Kehadiran</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--accent-teal); margin-top: 0.1rem;">
                {{ $attendances->count() }} <span style="font-size: 0.85rem; font-weight: 500; color: var(--text-muted);">Peserta</span>
            </div>
        </div>
    </div>

    <!-- Attendance Log Table -->
    <h3 style="margin-bottom: 1.25rem; font-size: 1.2rem; color: var(--text-main);">
        <i class="fa-solid fa-clipboard-list" style="color: var(--accent-teal)"></i> Riwayat Kehadiran Peserta
    </h3>

    @if ($attendances->isEmpty())
        <div style="text-align: center; padding: 4rem; background: rgba(255,255,255,0.2); border-radius: var(--radius-md); border: 1.5px dashed var(--input-border);">
            <i class="fa-solid fa-users-slash" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1.25rem;"></i>
            <p style="color: var(--text-muted); font-weight: 500; font-size: 1.1rem;">Belum ada peserta yang melakukan presensi.</p>
            <p style="font-size: 0.85rem; color: var(--text-light); margin-top: 0.25rem;">Bagikan kode registrasi <strong>{{ $session->code }}</strong> ke grup agar peserta dapat mengisi form.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>NIK / NIP</th>
                        <th>Nama Lengkap</th>
                        <th>Kategori</th>
                        <th>Waktu Presensi</th>
                        <th>Lokasi (GPS)</th>
                        <th style="text-align: center;">Ttd</th>
                        <th style="text-align: center;">Foto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $idx => $attendance)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td style="font-family: monospace; font-weight: 700;">{{ $attendance->participant_nik }}</td>
                            <td style="font-weight: 600; color: var(--text-main);">{{ $attendance->participant->name ?? 'Tidak Terdaftar' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $attendance->participant->role ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem; font-weight: 600;">
                                    {{ $attendance->signed_in_at->format('H:i:s') }}
                                </div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">
                                    {{ $attendance->signed_in_at->format('d M Y') }}
                                </div>
                            </td>
                            <td>
                                @if ($attendance->latitude && $attendance->longitude)
                                    <div style="font-size: 0.8rem; font-weight: 600;">
                                        {{ $attendance->latitude }}, {{ $attendance->longitude }}
                                    </div>
                                    <div style="margin-top: 0.25rem;">
                                        <a href="https://www.google.com/maps/place/{{ $attendance->latitude }},{{ $attendance->longitude }}" 
                                           target="_blank" 
                                           class="btn btn-secondary btn-sm" 
                                           style="padding: 0.2rem 0.5rem; font-size: 0.75rem; border-color: rgba(99,102,241,0.2);">
                                            <i class="fa-solid fa-map-location-dot" style="color: var(--accent-indigo)"></i> Buka Peta
                                        </a>
                                    </div>
                                @else
                                    <span style="color: var(--text-light); font-size: 0.85rem;">Tidak terekam</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <img src="{{ $attendance->signature }}" 
                                     alt="Ttd" 
                                     class="thumbnail-zoom" 
                                     style="height: 35px; width: 70px; object-fit: contain;" 
                                     onclick="showImageZoom('{{ $attendance->signature }}', 'Tanda Tangan - {{ addslashes($attendance->participant->name ?? $attendance->participant_nik) }}')">
                            </td>
                            <td style="text-align: center;">
                                @if ($attendance->photo)
                                    <img src="{{ $attendance->photo }}" 
                                         alt="Selfie" 
                                         class="thumbnail-zoom" 
                                         style="height: 35px; width: 35px; border-radius: 50%; object-fit: cover;" 
                                         onclick="showImageZoom('{{ $attendance->photo }}', 'Foto Selfie - {{ addslashes($attendance->participant->name ?? $attendance->participant_nik) }}')">
                                @else
                                    <span style="color: var(--text-light); font-size: 0.8rem;">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</main>

<!-- Image Zoom Modal Overlay -->
<div id="imageModal" class="modal-backdrop">
    <div class="modal-content">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid var(--input-border); padding-bottom: 0.5rem;">
            <h3 id="imageModalTitle" style="font-size: 1.15rem; color: var(--text-main);">Detail Lampiran</h3>
            <button type="button" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-light);" onclick="closeImageModal()">&times;</button>
        </div>
        <img id="imageModalTarget" style="width: 100%; border: 1.5px solid var(--input-border); border-radius: var(--radius-md); background: white; max-height: 300px; object-fit: contain;">
        <button type="button" class="btn btn-secondary btn-block" style="margin-top: 1.5rem;" onclick="closeImageModal()">Tutup</button>
    </div>
</div>

<script>
    const imageModal = document.getElementById('imageModal');
    const imageModalTarget = document.getElementById('imageModalTarget');
    const imageModalTitle = document.getElementById('imageModalTitle');

    function showImageZoom(src, title) {
        imageModalTarget.src = src;
        imageModalTitle.innerText = title;
        imageModal.style.display = 'flex';
    }

    function closeImageModal() {
        imageModal.style.display = 'none';
        imageModalTarget.src = '';
    }

    // Close modal when clicking outside content area
    window.addEventListener('click', (e) => {
        if (e.target === imageModal) {
            closeImageModal();
        }
    });
</script>
@endsection
