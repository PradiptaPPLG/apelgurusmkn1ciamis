@extends('layouts.app')

@section('title', 'Detail Presensi - Asign SMKN 1 Ciamis')

@section('body-class', 'admin-layout admin-sidebar-layout')

{{-- Push Leaflet CSS ke <head> --}}
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endpush

@section('content')
{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
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
        overflow-y: auto;
        padding: 2rem 0;
        align-items: flex-start;
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
        margin: auto;
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
            <span class="mobile-topbar-title"><i class="fa-solid fa-clipboard-list"></i> Detail Presensi</span>
        </header>

        <div class="admin-content-area">

        {{-- Page Header --}}
        <div class="page-header">
            <div>
                <h1 class="page-title"><i class="fa-solid fa-clipboard-list" style="color: var(--accent-teal);"></i> Detail Sesi Presensi</h1>
                <p class="page-subtitle">Rincian kehadiran peserta pada sesi: <strong>{{ $session->title }}</strong></p>
            </div>
        </div>

    <!-- Top Row Navigation & Actions -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>

        {{-- Download Dropdown --}}
        <div style="position: relative; display: inline-block;" id="exportDropdownWrapper">
            <button type="button" class="btn btn-primary" id="exportDropdownBtn"
                    onclick="toggleExportDropdown()"
                    style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-download"></i>
                Unduh Laporan
                <i class="fa-solid fa-chevron-down" style="font-size:0.75rem;"></i>
            </button>
            <div id="exportDropdownMenu" style="display:none; position:absolute; right:0; top:calc(100% + 6px); background:var(--card-bg); border:1.5px solid var(--card-border); border-radius:var(--radius-md); box-shadow:var(--card-shadow); min-width:200px; z-index:100; overflow:hidden;">
                @php
                    $exportParams = array_filter(request()->only(['search','jabatan','date_from','date_to']));
                @endphp
                <a href="{{ route('admin.sessions.export.pdf', array_merge(['id' => $session->id], $exportParams)) }}"
                   class="export-option" style="display:flex; align-items:center; gap:0.65rem; padding:0.7rem 1rem; color:var(--text-main); text-decoration:none; font-size:0.9rem; transition:background 0.15s;"
                   onmouseover="this.style.background='rgba(99,102,241,0.07)'" onmouseout="this.style.background='transparent'">
                    <i class="fa-solid fa-file-pdf" style="color:#e53e3e; width:16px;"></i>
                    <div>
                        <div style="font-weight:600;">Unduh PDF</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">Format kop sekolah resmi</div>
                    </div>
                </a>
                <div style="height:1px; background:var(--card-border); margin:0 0.5rem;"></div>
                <a href="{{ route('admin.sessions.export.excel', array_merge(['id' => $session->id], $exportParams)) }}"
                   class="export-option" style="display:flex; align-items:center; gap:0.65rem; padding:0.7rem 1rem; color:var(--text-main); text-decoration:none; font-size:0.9rem; transition:background 0.15s;"
                   onmouseover="this.style.background='rgba(99,102,241,0.07)'" onmouseout="this.style.background='transparent'">
                    <i class="fa-solid fa-file-excel" style="color:#1d6f42; width:16px;"></i>
                    <div>
                        <div style="font-weight:600;">Unduh Excel</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">Format spreadsheet .xlsx</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.sessions.detail', $session->id) }}"
          style="background:var(--card-bg); border:1.5px solid var(--card-border); border-radius:var(--radius-md); padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; flex-wrap:wrap; gap:0.75rem; align-items:flex-end;">

        <div style="flex:1; min-width:160px;">
            <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.3rem; display:block;">🔍 Cari Nama</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Ketik nama..."
                   style="width:100%; padding:0.5rem 0.75rem; border:1.5px solid var(--input-border); border-radius:var(--radius-sm); background:var(--input-bg); color:var(--text-main); font-size:0.88rem;">
        </div>

        <div style="min-width:150px;">
            <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.3rem; display:block;">📂 Jabatan</label>
            <select name="jabatan"
                    style="width:100%; padding:0.5rem 0.75rem; border:1.5px solid var(--input-border); border-radius:var(--radius-sm); background:var(--input-bg); color:var(--text-main); font-size:0.88rem;">
                <option value="">Semua Jabatan</option>
                @foreach(['Guru','TU','PPL','PPG','Wali Kelas'] as $j)
                    <option value="{{ $j }}" {{ request('jabatan') === $j ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>
        </div>

        <div style="min-width:140px;">
            <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.3rem; display:block;">📅 Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   style="width:100%; padding:0.5rem 0.75rem; border:1.5px solid var(--input-border); border-radius:var(--radius-sm); background:var(--input-bg); color:var(--text-main); font-size:0.88rem;">
        </div>

        <div style="min-width:140px;">
            <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.3rem; display:block;">📅 Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   style="width:100%; padding:0.5rem 0.75rem; border:1.5px solid var(--input-border); border-radius:var(--radius-sm); background:var(--input-bg); color:var(--text-main); font-size:0.88rem;">
        </div>

        <div style="display:flex; gap:0.5rem; align-items:flex-end;">
            <button type="submit" class="btn btn-primary btn-sm" style="height:38px; padding:0 1rem;">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            @if(request()->hasAny(['search','jabatan','date_from','date_to']))
            <a href="{{ route('admin.sessions.detail', $session->id) }}" class="btn btn-secondary btn-sm" style="height:38px; padding:0 1rem;">
                <i class="fa-solid fa-xmark"></i> Reset
            </a>
            @endif
        </div>
    </form>

    @if(request()->hasAny(['search','jabatan','date_from','date_to']))
    <div style="margin-bottom:1rem; font-size:0.82rem; color:var(--text-muted); display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
        <i class="fa-solid fa-circle-info" style="color:var(--accent-indigo);"></i>
        <span>Filter aktif:</span>
        @if(request('search')) <span style="background:rgba(99,102,241,0.1);color:var(--accent-indigo);padding:0.15rem 0.5rem;border-radius:20px;font-weight:600;">Nama: "{{ request('search') }}"</span> @endif
        @if(request('jabatan')) <span style="background:rgba(99,102,241,0.1);color:var(--accent-indigo);padding:0.15rem 0.5rem;border-radius:20px;font-weight:600;">Jabatan: {{ request('jabatan') }}</span> @endif
        @if(request('date_from')) <span style="background:rgba(99,102,241,0.1);color:var(--accent-indigo);padding:0.15rem 0.5rem;border-radius:20px;font-weight:600;">Dari: {{ request('date_from') }}</span> @endif
        @if(request('date_to')) <span style="background:rgba(99,102,241,0.1);color:var(--accent-indigo);padding:0.15rem 0.5rem;border-radius:20px;font-weight:600;">Sampai: {{ request('date_to') }}</span> @endif
        <span style="margin-left:0.25rem;">— menampilkan <strong>{{ $attendances->count() }}</strong> peserta</span>
    </div>
    @endif


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
                            <td style="font-family: monospace; color: var(--text-main);">
                                <div style="font-weight: 700;">{{ $attendance->participant_nik }}</div>
                                @if($attendance->participant && $attendance->participant->nip)
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">NIP: {{ $attendance->participant->nip }}</div>
                                @endif
                                @if($attendance->participant && $attendance->participant->other_id)
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">ID: {{ $attendance->participant->other_id }}</div>
                                @endif
                            </td>
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
                                @if ($attendance->location_name)
                                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.85rem; line-height: 1.2;">
                                        {{ $attendance->location_name }}
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.15rem;">
                                        {{ $attendance->latitude }}, {{ $attendance->longitude }}
                                    </div>
                                @elseif ($attendance->latitude && $attendance->longitude)
                                    <div style="font-size: 0.8rem; font-weight: 600;">
                                        {{ $attendance->latitude }}, {{ $attendance->longitude }}
                                    </div>
                                @else
                                    <span style="color: var(--text-light); font-size: 0.85rem;">Tidak terekam</span>
                                @endif
                                
                                @if ($attendance->latitude && $attendance->longitude)
                                    <div style="margin-top: 0.35rem;">
                                        <button type="button"
                                                class="btn btn-secondary btn-sm" 
                                                style="padding: 0.2rem 0.5rem; font-size: 0.75rem; border-color: rgba(99,102,241,0.2);"
                                                onclick="showMapModal({{ $attendance->latitude }}, {{ $attendance->longitude }}, '{{ addslashes($attendance->participant->name ?? $attendance->participant_nik) }}')">
                                            <i class="fa-solid fa-map-location-dot" style="color: var(--accent-indigo)"></i> Lihat Peta
                                        </button>
                                    </div>
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

    <!-- Absent Participants Section -->
    <div style="background: rgba(239, 68, 68, 0.04); border: 1.5px solid rgba(239, 68, 68, 0.15); border-radius: var(--radius-md); padding: 1.5rem; margin-top: 2.5rem; margin-bottom: 2rem;">
        <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.15rem; color: #ef4444; display: flex; align-items: center; gap: 0.55rem;">
            <i class="fa-solid fa-circle-xmark"></i>
            <span>Daftar Peserta Belum Presensi ({{ $absentParticipants->count() }} orang)</span>
        </h3>
        
        @if($absentParticipants->isEmpty())
            <div style="color: #10b981; font-weight: 600; font-size: 0.88rem; display: flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid fa-circle-check" style="font-size: 1.1rem;"></i> Semua peserta aktif sudah melakukan presensi pada sesi ini.
            </div>
        @else
            <div class="table-responsive" style="max-height: 280px; overflow-y: auto; border: 1px solid rgba(239, 68, 68, 0.1); border-radius: var(--radius-sm);">
                <table class="table-custom" style="margin-bottom: 0;">
                    <thead style="position: sticky; top: 0; z-index: 2;">
                        <tr>
                            <th style="width: 50px; background: #fee2e2; color: #991b1b; border-bottom: 1.5px solid rgba(239,68,68,0.2);">No</th>
                            <th style="background: #fee2e2; color: #991b1b; border-bottom: 1.5px solid rgba(239,68,68,0.2);">NIK / NIP</th>
                            <th style="background: #fee2e2; color: #991b1b; border-bottom: 1.5px solid rgba(239,68,68,0.2);">Nama Lengkap</th>
                            <th style="background: #fee2e2; color: #991b1b; border-bottom: 1.5px solid rgba(239,68,68,0.2);">Kategori</th>
                            <th style="background: #fee2e2; color: #991b1b; border-bottom: 1.5px solid rgba(239,68,68,0.2);">Jabatan / Kepegawaian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($absentParticipants as $idx => $absent)
                            <tr style="background: rgba(254, 242, 242, 0.25);">
                                <td style="color: #991b1b;">{{ $idx + 1 }}</td>
                                <td style="font-family: monospace; color: #991b1b;">
                                    <strong>{{ $absent->nik }}</strong>
                                    @if($absent->nip)
                                        <div style="font-size: 0.72rem; color: #b91c1c; opacity: 0.85;">NIP: {{ $absent->nip }}</div>
                                    @endif
                                </td>
                                <td style="font-weight: 600; color: #991b1b;">{{ $absent->name }}</td>
                                <td>
                                    <span class="badge" style="background: #fecaca; color: #991b1b; font-weight: 600; font-size: 0.75rem; border: 1px solid rgba(239,68,68,0.2);">{{ $absent->role }}</span>
                                </td>
                                <td style="color: #991b1b; font-size: 0.82rem; font-weight: 500;">
                                    {{ $absent->jabatan ?? '—' }}
                                    @if($absent->jenis_kepegawaian)
                                        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #b91c1c; background: rgba(239, 68, 68, 0.08); padding: 0.1rem 0.35rem; border-radius: 4px; margin-left: 0.3rem;">{{ $absent->jenis_kepegawaian }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

        </div>{{-- end content-area --}}
    </div>{{-- end admin-main --}}
</div>{{-- end admin-wrapper --}}

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

<!-- Leaflet Map Modal Overlay -->
<div id="mapModal" class="modal-backdrop">
    <div class="modal-content" style="max-width: 600px; width: 90%;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; border-bottom: 1px solid var(--input-border); padding-bottom: 0.5rem;">
            <h3 id="mapModalTitle" style="font-size: 1.15rem; color: var(--text-main);">Lokasi Presensi</h3>
            <button type="button" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-light);" onclick="closeMapModal()">&times;</button>
        </div>
        <div id="mapContainer" style="height: 350px; width: 100%; border: 1.5px solid var(--input-border); border-radius: var(--radius-md); background: #eee; z-index: 1;"></div>
        <button type="button" class="btn btn-secondary btn-block" style="margin-top: 1rem;" onclick="closeMapModal()">Tutup</button>
    </div>
</div>

<script>
    const imageModal = document.getElementById('imageModal');
    const imageModalTarget = document.getElementById('imageModalTarget');
    const imageModalTitle = document.getElementById('imageModalTitle');
    const mapModal = document.getElementById('mapModal');

    function showImageZoom(src, title) {
        imageModalTarget.src = src;
        imageModalTitle.innerText = title;
        imageModal.style.display = 'flex';
    }

    function closeImageModal() {
        imageModal.style.display = 'none';
        imageModalTarget.src = '';
    }

    // Leaflet map initialization
    let leafletMap = null;
    let mapMarker = null;

    function showMapModal(lat, lon, name) {
        mapModal.style.display = 'flex';
        
        // Timeout to ensure the container DOM is rendered before Leaflet runs
        setTimeout(() => {
            if (leafletMap) {
                leafletMap.remove();
            }
            
            leafletMap = L.map('mapContainer').setView([lat, lon], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(leafletMap);
            
            mapMarker = L.marker([lat, lon]).addTo(leafletMap)
                .bindPopup(`<b>${name}</b><br>Lokasi Presensi`)
                .openPopup();
                
            leafletMap.invalidateSize();
        }, 150);
    }

    function closeMapModal() {
        mapModal.style.display = 'none';
        if (leafletMap) {
            leafletMap.remove();
            leafletMap = null;
        }
    }

    // Close modals when clicking outside content area
    window.addEventListener('click', (e) => {
        if (e.target === imageModal) {
            closeImageModal();
        }
        if (e.target === mapModal) {
            closeMapModal();
        }
    });

    function toggleSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const isOpen  = sidebar.classList.contains('open');
        sidebar.classList.toggle('open', !isOpen);
        overlay.classList.toggle('active', !isOpen);
    }

    // Export dropdown toggle
    function toggleExportDropdown() {
        const menu = document.getElementById('exportDropdownMenu');
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }
    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('exportDropdownWrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            const menu = document.getElementById('exportDropdownMenu');
            if (menu) menu.style.display = 'none';
        }
    });

</script>
@endsection
