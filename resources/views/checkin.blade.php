@extends('layouts.app')

@section('title', 'Presensi Apel - SMKN 1 Ciamis')
@section('body-class', 'client-layout')

@section('content')
<div class="glass-container">

    {{-- Header: text only, no logo icon --}}
    <div class="brand-header" style="margin-bottom: 1.5rem;">
        <h1 class="brand-title">E-Apel Guru</h1>
    </div>

    {{-- Error Alerts --}}
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

    @if (session('error'))
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <form action="{{ route('apel.submit') }}" method="POST" id="apelForm" onsubmit="return validateForm()">
        @csrf

        {{-- Silent hidden inputs --}}
        <input type="hidden" name="latitude"  id="latitude">
        <input type="hidden" name="longitude" id="longitude">
        <input type="hidden" name="location_name" id="locationNameInput">
        <input type="hidden" name="photo"     id="photoInput">
        <input type="hidden" name="signature" id="signatureInput">

        {{-- GPS error notice (non-blocking, only shown if permission denied) --}}
        <div id="geoErrorNotice" style="display:none; font-size:0.78rem; color:#b45309; background:#fffbeb; border:1px solid #fef3c7; border-radius:6px; padding:0.5rem 0.75rem; margin-bottom:1rem;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Izin lokasi ditolak. Absensi tetap bisa dilanjutkan tanpa koordinat.
        </div>

        {{-- ① Kode Registrasi Apel --}}
        <div class="form-group">
            <label class="form-label" for="code">Kode Registrasi Apel</label>
            <input type="text"
                   name="code"
                   id="code"
                   class="form-control"
                   placeholder="Contoh: ABCDE"
                   maxlength="5"
                   value="{{ old('code', $openSession->code ?? $selectedCode) }}"
                   required
                   style="text-transform: uppercase; letter-spacing: 0.15em; font-weight: 700;">
            @if ($urlSession && !$openSession)
                <small style="color: var(--accent-rose); font-weight: 500;">Sesi untuk kode ini belum dimulai atau sudah berakhir.</small>
            @endif
        </div>

        {{-- ② NIK / NIP / ID --}}
        <div class="form-group">
            <label class="form-label" for="nik">NIK / NIP / ID</label>
            <input type="text"
                   name="nik"
                   id="nik"
                   class="form-control"
                   placeholder="Masukkan NIK / NIP Anda"
                   value="{{ old('nik') }}"
                   required>
        </div>

        {{-- ③ Tanda Tangan --}}
        <div class="form-group">
            <label class="form-label">Tanda Tangan</label>
            <div class="canvas-wrapper">
                <canvas id="signaturePad" class="signature-canvas"></canvas>
            </div>
            <div class="canvas-actions">
                <button type="button" class="btn btn-secondary btn-sm" onclick="clearSignature()">
                    <i class="fa-solid fa-eraser"></i> Bersihkan
                </button>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">
            <i class="fa-solid fa-paper-plane"></i> Kirim Kehadiran (Apel)
        </button>
    </form>

    <div class="app-footer">
        &copy; {{ date('Y') }} SMKN 1 Ciamis. All rights reserved.
    </div>
</div>

<script>
    // --- Silent GPS capture ---
    (function () {
        const latInput = document.getElementById('latitude');
        const lonInput = document.getElementById('longitude');
        const locInput = document.getElementById('locationNameInput');
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                pos => {
                    const lat = pos.coords.latitude.toFixed(8);
                    const lon = pos.coords.longitude.toFixed(8);
                    latInput.value = lat;
                    lonInput.value = lon;

                    // Fetch precise address from OpenStreetMap Nominatim (runs client-side)
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`, {
                        headers: {
                            'Accept-Language': 'id-ID,id;q=0.9,en;q=0.8'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.address) {
                            const addr = data.address;
                            const parts = [];
                            
                            // Extract place/building name if available
                            const placeKeys = ['amenity', 'school', 'college', 'university', 'building', 'office', 'shop', 'tourism', 'leisure', 'historic'];
                            let placeName = '';
                            for (const key of placeKeys) {
                                if (addr[key]) {
                                    placeName = addr[key];
                                    break;
                                }
                            }

                            const parts = [];
                            if (placeName) {
                                parts.push(placeName);
                            }
                            if (addr.road) {
                                parts.push(addr.road);
                            }
                            
                            if (addr.village) parts.push(addr.village);
                            else if (addr.suburb) parts.push(addr.suburb);
                            
                            if (addr.town) parts.push(addr.town);
                            else if (addr.city) parts.push(addr.city);
                            else if (addr.municipality) parts.push(addr.municipality);
                            else if (addr.county) parts.push(addr.county);

                            let addressText = parts.join(', ');
                            if (!addressText && data.display_name) {
                                addressText = data.display_name;
                            }
                            locInput.value = addressText;
                        }
                    })
                    .catch(err => console.error('Geocoding error:', err));
                },
                () => {
                    const n = document.getElementById('geoErrorNotice');
                    if (n) n.style.display = 'flex';
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
    })();

    // --- Signature Pad ---
    const canvas  = document.getElementById('signaturePad');
    const ctx     = canvas.getContext('2d');
    let isDrawing = false;
    let hasDrawn  = false;

    function resizeCanvas() {
        const data = canvas.toDataURL(); // preserve drawing on resize
        canvas.width  = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
        ctx.strokeStyle = '#1e293b';
        ctx.lineWidth   = 2.5;
        ctx.lineCap     = 'round';
        ctx.lineJoin    = 'round';
        const img = new Image();
        img.onload = () => ctx.drawImage(img, 0, 0);
        img.src = data;
    }

    window.addEventListener('load',   resizeCanvas);
    window.addEventListener('resize', resizeCanvas);

    // Mouse
    canvas.addEventListener('mousedown', e => { isDrawing = true; ctx.beginPath(); ctx.moveTo(e.offsetX, e.offsetY); });
    canvas.addEventListener('mousemove', e => { if (!isDrawing) return; ctx.lineTo(e.offsetX, e.offsetY); ctx.stroke(); hasDrawn = true; });
    canvas.addEventListener('mouseup',   () => isDrawing = false);
    canvas.addEventListener('mouseout',  () => isDrawing = false);

    // Touch
    canvas.addEventListener('touchstart', e => {
        e.preventDefault();
        const t = e.touches[0], r = canvas.getBoundingClientRect();
        isDrawing = true; ctx.beginPath(); ctx.moveTo(t.clientX - r.left, t.clientY - r.top);
    }, { passive: false });
    canvas.addEventListener('touchmove', e => {
        e.preventDefault();
        if (!isDrawing) return;
        const t = e.touches[0], r = canvas.getBoundingClientRect();
        ctx.lineTo(t.clientX - r.left, t.clientY - r.top); ctx.stroke(); hasDrawn = true;
    }, { passive: false });
    canvas.addEventListener('touchend', () => isDrawing = false);

    function clearSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasDrawn = false;
    }

    // --- Form validation ---
    function validateForm() {
        if (!hasDrawn) {
            alert('Tanda tangan wajib diisi sebelum mengirimkan kehadiran.');
            return false;
        }
        document.getElementById('signatureInput').value = canvas.toDataURL('image/png');
        return true;
    }
</script>
@endsection


