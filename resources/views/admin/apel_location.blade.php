@extends('layouts.app')

@section('title', 'Titik Apel - Admin E-Apel SMKN 1 Ciamis')
@section('body-class', 'admin-layout admin-sidebar-layout')

{{-- Leaflet CSS → masuk ke <head> via @stack --}}
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    #map {
        height: 450px !important;
        width: 100% !important;
        z-index: 1;
        background: #f0f4f8;
    }
</style>
@endpush

@section('content')
<div class="admin-wrapper">

    {{-- Sidebar --}}
    @include('admin.partials.sidebar', ['activePage' => 'location'])

    {{-- Main Content --}}
    <div class="admin-main">

        {{-- Mobile Topbar --}}
        <header class="admin-mobile-topbar">
            <button class="sidebar-toggle-btn" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="mobile-topbar-title"><i class="fa-solid fa-location-dot"></i> Titik Apel</span>
        </header>

        <div class="admin-content-area">

            {{-- Page Header --}}
            <div class="page-header">
                <div>
                    <h1 class="page-title">
                        <i class="fa-solid fa-location-dot" style="color: var(--accent-rose);"></i>
                        Pengaturan Titik Apel
                    </h1>
                    <p class="page-subtitle">
                        Tentukan koordinat pusat area apel. Peserta wajib berada dalam radius
                        <strong>{{ $apelLocation->radius_meter }} meter</strong> untuk dapat melakukan absensi.
                    </p>
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

            {{-- Status Card --}}
            <div class="location-status-card {{ $apelLocation->isConfigured() ? 'status-configured' : 'status-not-configured' }}">
                @if($apelLocation->isConfigured())
                    <div class="status-icon status-icon-green">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <div class="status-title">Titik Apel Aktif</div>
                        <div class="status-desc">
                            <strong>{{ $apelLocation->label ?? 'Titik Apel' }}</strong> &mdash;
                            {{ number_format($apelLocation->latitude, 6) }}, {{ number_format($apelLocation->longitude, 6) }}
                            &mdash; Radius <strong>{{ $apelLocation->radius_meter }} meter</strong>
                        </div>
                        @if($apelLocation->updated_by)
                            <div class="status-meta">Terakhir diset oleh: {{ $apelLocation->updated_by }}</div>
                        @endif
                    </div>
                @else
                    <div class="status-icon status-icon-warn">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <div class="status-title">Titik Apel Belum Dikonfigurasi</div>
                        <div class="status-desc">Geofencing dinonaktifkan. Klik pada peta di bawah untuk menetapkan titik apel.</div>
                    </div>
                @endif
            </div>

            {{-- Map + Form --}}
            <div class="location-panel-split">

                {{-- Peta --}}
                <div class="location-map-wrapper">
                    <div class="map-header">
                        <i class="fa-solid fa-map" style="color: var(--accent-indigo);"></i>
                        <span>Klik pada peta untuk memilih titik apel</span>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="locateMe()" id="locateMeBtn">
                            <i class="fa-solid fa-crosshairs"></i> Lokasi Saya
                        </button>
                    </div>

                    {{-- div peta dengan id unik --}}
                    <div id="map"></div>

                    <div class="map-hint">
                        <i class="fa-solid fa-info-circle"></i>
                        Lingkaran biru menunjukkan area radius <span id="hintRadius">{{ $apelLocation->radius_meter }}</span> meter dari titik apel.
                    </div>
                </div>

                {{-- Form --}}
                <div class="location-form-wrapper">
                    <h3 class="form-section-title">
                        <i class="fa-solid fa-sliders" style="color: var(--accent-indigo);"></i>
                        Detail Titik Apel
                    </h3>

                    <form action="{{ route('admin.apel.location.save') }}" method="POST" id="locationForm">
                        @csrf

                        <div class="form-group">
                            <label class="form-label" for="loc_label">Nama Lokasi</label>
                            <input type="text" name="label" id="loc_label" class="form-control"
                                   placeholder="Contoh: Lapangan Apel SMKN 1 Ciamis"
                                   value="{{ old('label', $apelLocation->label) }}">
                        </div>

                        <div class="coords-grid">
                            <div class="form-group">
                                <label class="form-label" for="lat_input">Latitude</label>
                                <input type="number" name="latitude" id="lat_input" class="form-control"
                                       placeholder="-7.123456" step="0.00000001"
                                       value="{{ old('latitude', $apelLocation->latitude) }}"
                                       required readonly>
                                <small style="color:var(--text-muted);font-size:0.75rem;">Klik peta atau pakai "Lokasi Saya"</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="lon_input">Longitude</label>
                                <input type="number" name="longitude" id="lon_input" class="form-control"
                                       placeholder="108.123456" step="0.00000001"
                                       value="{{ old('longitude', $apelLocation->longitude) }}"
                                       required readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Radius Area Apel</label>
                            <input type="hidden" name="radius_meter" value="10">
                            <div style="display:flex;align-items:center;gap:0.65rem;background:#f0fdf4;border:1.5px solid #86efac;border-radius:8px;padding:0.65rem 1rem;">
                                <i class="fa-solid fa-circle-check" style="color:#16a34a;"></i>
                                <span style="font-weight:700;color:#15803d;">10 meter (tetap)</span>
                                <span style="font-size:0.75rem;color:#6b7280;margin-left:auto;">Radius maksimal dari titik apel</span>
                            </div>
                        </div>

                        <div class="coord-preview" id="coordPreview">
                            @if($apelLocation->isConfigured())
                                <div class="coord-preview-item">
                                    <i class="fa-solid fa-map-pin" style="color:var(--accent-rose);"></i>
                                    <span>{{ number_format($apelLocation->latitude, 8) }}, {{ number_format($apelLocation->longitude, 8) }}</span>
                                </div>
                            @else
                                <div style="color:var(--text-muted);font-size:0.85rem;text-align:center;">
                                    <i class="fa-regular fa-map"></i> Belum ada titik dipilih
                                </div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" id="saveBtn" style="margin-top:1rem;"
                                {{ !$apelLocation->isConfigured() && !old('latitude') ? 'disabled' : '' }}>
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Titik Apel
                        </button>

                        @if($apelLocation->isConfigured())
                            <div style="margin-top:0.5rem;font-size:0.78rem;color:var(--text-muted);text-align:center;">
                                Terakhir diperbarui: {{ $apelLocation->updated_at->diffForHumans() }}
                            </div>
                        @endif
                    </form>
                </div>

            </div>{{-- end location-panel-split --}}

        </div>{{-- end admin-content-area --}}
    </div>{{-- end admin-main --}}
</div>{{-- end admin-wrapper --}}

{{-- ═══════════════════════════════════════════════════════════
     Leaflet JS — tanpa integrity agar tidak diblokir browser
     ═══════════════════════════════════════════════════════════ --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
(function () {
    'use strict';

    // ── Data dari server ──────────────────────────────────────
    var LAT    = {{ $apelLocation->latitude  ?? 'null' }};
    var LNG    = {{ $apelLocation->longitude ?? 'null' }};
    var RADIUS = {{ $apelLocation->radius_meter ?? 10 }};

    var CENTER = (LAT !== null && LNG !== null) ? [LAT, LNG] : [-7.3319, 108.3516];
    var ZOOM   = (LAT !== null && LNG !== null) ? 19 : 15;

    // ── Init Leaflet ──────────────────────────────────────────
    var map = L.map('map', {
        center:      CENTER,
        zoom:        ZOOM,
        zoomControl: true,
    });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 22,
    }).addTo(map);

    // ── State ─────────────────────────────────────────────────
    var marker       = null;
    var circle       = null;
    var FIXED_RADIUS = 10;

    // ── Custom marker icon ────────────────────────────────────
    var pin = L.divIcon({
        html: '<div style="width:16px;height:16px;border-radius:50%;background:#dc2626;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.4);"></div>',
        className: '',
        iconSize:    [16, 16],
        iconAnchor:  [8, 8],
    });

    // ── Place marker & radius circle ──────────────────────────
    function placeMarker(lat, lng) {
        document.getElementById('lat_input').value = lat.toFixed(8);
        document.getElementById('lon_input').value = lng.toFixed(8);
        document.getElementById('saveBtn').disabled = false;
        document.getElementById('coordPreview').innerHTML =
            '<div class="coord-preview-item">' +
            '<i class="fa-solid fa-map-pin" style="color:var(--accent-rose);"></i>' +
            '<span>' + lat.toFixed(8) + ', ' + lng.toFixed(8) + '</span>' +
            '</div>';

        if (marker) map.removeLayer(marker);
        if (circle) map.removeLayer(circle);

        marker = L.marker([lat, lng], { icon: pin, draggable: true })
            .addTo(map)
            .bindPopup('<b>Titik Apel</b><br>' + lat.toFixed(6) + ', ' + lng.toFixed(6))
            .openPopup();

        circle = L.circle([lat, lng], {
            radius:      FIXED_RADIUS,
            color:       '#2563eb',
            fillColor:   '#2563eb',
            fillOpacity: 0.12,
            weight:      2,
        }).addTo(map);

        marker.on('dragend', function () {
            var p = marker.getLatLng();
            placeMarker(p.lat, p.lng);
        });
    }

    // Klik peta
    map.on('click', function (e) {
        placeMarker(e.latlng.lat, e.latlng.lng);
        if (map.getZoom() < 18) map.setView(e.latlng, 18);
    });

    // Tampilkan marker jika sudah tersimpan
    if (LAT !== null && LNG !== null) {
        placeMarker(LAT, LNG);
    }

    // Radius dikunci 10m — tidak ada slider

    // ── Tombol Lokasi Saya ────────────────────────────────────
    window.locateMe = function () {
        var btn = document.getElementById('locateMeBtn');
        btn.disabled  = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mencari...';

        if (!navigator.geolocation) {
            alert('Browser tidak mendukung GPS.');
            btn.disabled  = false;
            btn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> Lokasi Saya';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                var lat = pos.coords.latitude;
                var lng = pos.coords.longitude;
                map.setView([lat, lng], 20);
                placeMarker(lat, lng);
                btn.disabled  = false;
                btn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> Lokasi Saya';
            },
            function (err) {
                alert('GPS error: ' + err.message);
                btn.disabled  = false;
                btn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> Lokasi Saya';
            },
            { enableHighAccuracy: true, timeout: 15000 }
        );
    };

    // ── Sidebar (mobile) ──────────────────────────────────────
    window.toggleSidebar = function () {
        var s = document.getElementById('adminSidebar');
        var o = document.getElementById('sidebarOverlay');
        var open = s.classList.contains('open');
        s.classList.toggle('open', !open);
        o.classList.toggle('active', !open);
    };

    // ── Fix ukuran peta setelah layout render ─────────────────
    setTimeout(function () { map.invalidateSize(); }, 200);
    setTimeout(function () { map.invalidateSize(); }, 800);

})();
</script>
@endsection
