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

    {{-- ── Geofencing Status Banner ── --}}
    {{-- Shown/hidden by JS --}}
    <div id="gpsLoadingBanner" style="display:flex; align-items:center; gap:0.6rem; background: rgba(99,102,241,0.07); border: 1px solid rgba(99,102,241,0.2); border-radius:8px; padding:0.65rem 0.85rem; margin-bottom:1rem; font-size:0.83rem; color: var(--accent-indigo);">
        <i class="fa-solid fa-satellite-dish fa-pulse"></i>
        <span>Mendeteksi lokasi GPS Anda...</span>
    </div>

    <div id="gpsBlockBanner" style="display:none; align-items:center; gap:0.7rem; background: rgba(244,63,94,0.08); border: 1.5px solid rgba(244,63,94,0.3); border-radius:10px; padding:0.9rem 1rem; margin-bottom:1rem;">
        <i class="fa-solid fa-location-xmark" style="font-size:1.4rem; color: var(--accent-rose); flex-shrink:0;"></i>
        <div>
            <div style="font-size:0.9rem; font-weight:700; color: var(--accent-rose);" id="gpsBlockTitle">GPS Tidak Aktif</div>
            <div style="font-size:0.8rem; color: var(--text-muted);" id="gpsBlockDesc">Aktifkan GPS dan izinkan akses lokasi untuk melanjutkan absensi.</div>
        </div>
    </div>

    <div id="gpsOutsideBanner" style="display:none; align-items:center; gap:0.7rem; background: rgba(234,179,8,0.08); border: 1.5px solid rgba(234,179,8,0.35); border-radius:10px; padding:0.9rem 1rem; margin-bottom:1rem;">
        <i class="fa-solid fa-circle-exclamation" style="font-size:1.4rem; color:#d97706; flex-shrink:0;"></i>
        <div>
            <div style="font-size:0.9rem; font-weight:700; color:#b45309;">Anda Di Luar Area Apel</div>
            <div style="font-size:0.8rem; color: var(--text-muted);" id="outsideDesc">Silakan menuju titik apel (Auditorium) untuk dapat melakukan absensi.</div>
        </div>
    </div>

    <div id="gpsOkBanner" style="display:none; align-items:center; gap:0.7rem; background: rgba(16,185,129,0.07); border: 1.5px solid rgba(16,185,129,0.25); border-radius:10px; padding:0.65rem 1rem; margin-bottom:1rem;">
        <i class="fa-solid fa-circle-check" style="font-size:1.2rem; color:#10b981; flex-shrink:0;"></i>
        <div style="font-size:0.82rem; color:#065f46; font-weight:600;" id="gpsOkDesc">Lokasi terverifikasi. Anda berada dalam area apel.</div>
    </div>

    {{-- Distance Bar (muncul saat inside/outside) --}}
    <div id="distanceBar" style="display:none; margin-bottom:1rem;">
        <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:var(--text-muted); margin-bottom:0.3rem;">
            <span>Jarak ke titik apel</span>
            <span id="distanceLabel">— m</span>
        </div>
        <div style="background:rgba(0,0,0,0.07); border-radius:99px; height:8px; overflow:hidden;">
            <div id="distanceBarFill"
                 style="height:100%; border-radius:99px; width:0%; transition:width 0.6s ease, background 0.4s ease; background:#10b981;"></div>
        </div>
        <div style="display:flex; justify-content:space-between; font-size:0.7rem; color:var(--text-muted); margin-top:0.2rem;">
            <span>Titik Apel</span>
            <span id="distanceBarMax">— m</span>
        </div>
    </div>

    <form action="{{ route('apel.submit') }}" method="POST" id="apelForm" onsubmit="return validateForm()">
        @csrf

        {{-- Silent hidden inputs --}}
        <input type="hidden" name="latitude"       id="latitude">
        <input type="hidden" name="longitude"      id="longitude">
        <input type="hidden" name="location_name"  id="locationNameInput">
        <input type="hidden" name="photo"          id="photoInput">
        <input type="hidden" name="signature"      id="signatureInput">

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
        <button type="submit" class="btn btn-primary btn-block" id="submitBtn" style="margin-top: 1.5rem;" disabled>
            <i class="fa-solid fa-paper-plane"></i> Kirim Kehadiran (Apel)
        </button>
        <div id="submitHint" style="text-align:center; font-size:0.77rem; color:var(--text-muted); margin-top:0.4rem;">
            Menunggu verifikasi lokasi GPS...
        </div>
    </form>

    <div class="app-footer">
        &copy; {{ date('Y') }} SMKN 1 Ciamis. All rights reserved.
    </div>
</div>

<script>
// ══════════════════════════════════════════════════════
//  GEOFENCING + GPS — Hard Block Logic
// ══════════════════════════════════════════════════════

const GEOFENCE_API = '/api/apel-location';

let gpsState = 'loading';   // 'loading' | 'no-gps' | 'outside' | 'ok' | 'no-fence'
let geofenceData = null;    // { configured, latitude, longitude, radius_meter }

const latInput       = document.getElementById('latitude');
const lonInput       = document.getElementById('longitude');
const locInput       = document.getElementById('locationNameInput');
const submitBtn      = document.getElementById('submitBtn');
const submitHint     = document.getElementById('submitHint');

const loadingBanner  = document.getElementById('gpsLoadingBanner');
const blockBanner    = document.getElementById('gpsBlockBanner');
const outsideBanner  = document.getElementById('gpsOutsideBanner');
const okBanner       = document.getElementById('gpsOkBanner');

function showBanner(type) {
    loadingBanner.style.display  = 'none';
    blockBanner.style.display    = 'none';
    outsideBanner.style.display  = 'none';
    okBanner.style.display       = 'none';

    if (type === 'loading') loadingBanner.style.display  = 'flex';
    if (type === 'block')   blockBanner.style.display    = 'flex';
    if (type === 'outside') outsideBanner.style.display  = 'flex';
    if (type === 'ok')      okBanner.style.display       = 'flex';
}

function enableSubmit() {
    submitBtn.disabled   = false;
    submitHint.textContent = '';
}

function disableSubmit(hint) {
    submitBtn.disabled   = true;
    submitHint.textContent = hint;
}

// ── Haversine distance formula (returns meters) ──────
function haversineDistance(lat1, lon1, lat2, lon2) {
    const R   = 6371000; // Earth radius in meters
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a   = Math.sin(dLat / 2) * Math.sin(dLat / 2)
              + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180)
              * Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c   = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

// ── Update distance bar ──────────────────────────────
function updateDistanceBar(dist, radius) {
    const bar      = document.getElementById('distanceBar');
    const fill     = document.getElementById('distanceBarFill');
    const label    = document.getElementById('distanceLabel');
    const barMax   = document.getElementById('distanceBarMax');

    // Tampilkan bar hingga 2x radius agar masih terlihat jika di luar
    const maxDisplay = radius * 2;
    const pct = Math.min((dist / maxDisplay) * 100, 100);

    bar.style.display = 'block';
    label.textContent = Math.round(dist) + ' m';
    barMax.textContent = radius + ' m (batas)';
    fill.style.width = pct + '%';

    if (dist <= radius) {
        // Hijau: dalam area
        fill.style.background = '#10b981';
    } else if (dist <= radius * 1.5) {
        // Kuning: dekat tapi di luar
        fill.style.background = '#f59e0b';
    } else {
        // Merah: jauh di luar
        fill.style.background = '#ef4444';
    }
}

// ── Check user position against geofence ────────────
function checkGeofence(userLat, userLon) {
    if (!geofenceData || !geofenceData.configured) {
        // No geofence configured — allow freely, show nothing
        showBanner('ok');
        document.getElementById('gpsOkDesc').textContent = 'Lokasi terdeteksi. Silakan lanjutkan absensi.';
        enableSubmit();
        return;
    }

    const dist = haversineDistance(
        userLat, userLon,
        geofenceData.latitude, geofenceData.longitude
    );

    const radius = geofenceData.radius_meter || 10;

    // Selalu tampilkan bar jarak
    updateDistanceBar(dist, radius);

    if (dist <= radius) {
        // ✅ Inside — allow
        showBanner('ok');
        document.getElementById('gpsOkDesc').textContent =
            `Lokasi terverifikasi. Anda berada dalam area apel (${Math.round(dist)}m dari titik apel).`;
        enableSubmit();
    } else {
        // ❌ Outside — hard block
        showBanner('outside');
        document.getElementById('outsideDesc').textContent =
            `Anda berada sejauh ±${Math.round(dist)} meter dari titik apel. Maksimal ${radius} meter. Silakan menuju titik apel untuk melakukan absensi.`;
        disableSubmit('Anda di luar area apel. Dekati titik apel.');
    }
}

// ── Main GPS + Geofence Flow ─────────────────────────
(async function initGeofence() {
    showBanner('loading');
    disableSubmit('Menunggu verifikasi lokasi GPS...');

    // Step 1: Check if browser supports geolocation
    if (!navigator.geolocation) {
        showBanner('block');
        document.getElementById('gpsBlockTitle').textContent = 'GPS Tidak Didukung';
        document.getElementById('gpsBlockDesc').textContent  = 'Browser Anda tidak mendukung GPS. Gunakan browser modern (Chrome/Firefox).';
        disableSubmit('GPS tidak didukung oleh browser ini.');
        return;
    }

    // Step 2: Fetch geofence settings from server
    try {
        const res  = await fetch(GEOFENCE_API);
        geofenceData = await res.json();
    } catch (e) {
        console.warn('Gagal memuat data geofence:', e);
        geofenceData = { configured: false };
    }

    // Step 3: Kumpulkan bacaan GPS selama GPS_COLLECT_MS, lalu rata-ratakan koordinat
    // dengan bobot berdasarkan akurasi (weighted average). Ini jauh lebih stabil dari
    // sekadar mengambil satu bacaan terbaik, karena noise acak saling menghilangkan.
    const GPS_COLLECT_MS    = 12000; // kumpulkan selama 12 detik
    const MAX_ACC_THRESHOLD = 50;    // abaikan bacaan akurasi > 50m (terlalu noisy)

    let samples           = [];   // { lat, lon, acc }
    let watchId           = null;
    let gpsSettled        = false;
    let gpsTimer          = null;
    let countdown         = GPS_COLLECT_MS / 1000;
    let countdownInterval = null;

    // Tampilkan countdown di banner loading
    countdownInterval = setInterval(() => {
        countdown = Math.max(0, countdown - 1);
        const el = document.querySelector('#gpsLoadingBanner span');
        if (el && !gpsSettled) {
            el.textContent = `Mengumpulkan data GPS (${samples.length} sampel)... (${countdown}s)`;
        }
        if (countdown <= 0) clearInterval(countdownInterval);
    }, 1000);

    function applyAveragedPosition() {
        if (gpsSettled) return;
        gpsSettled = true;

        clearInterval(countdownInterval);
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
        if (gpsTimer)         clearTimeout(gpsTimer);

        if (samples.length === 0) { onGpsError({ code: 3, message: 'Timeout' }); return; }

        // Weighted average — bobot = 1/akurasi (akurasi lebih baik = bobot lebih tinggi)
        let totalW = 0, sumLat = 0, sumLon = 0;
        for (const s of samples) {
            const w = 1 / s.acc;
            sumLat += s.lat * w;
            sumLon += s.lon * w;
            totalW += w;
        }
        const avgLat = sumLat / totalW;
        const avgLon = sumLon / totalW;
        const avgAcc = Math.round(samples.reduce((a, s) => a + s.acc, 0) / samples.length);

        console.log(`[GPS] ${samples.length} sampel dirata-rata → lat=${avgLat.toFixed(6)}, lon=${avgLon.toFixed(6)}, ~${avgAcc}m`);

        latInput.value = avgLat.toFixed(8);
        lonInput.value = avgLon.toFixed(8);

        // Reverse geocode (non-blocking)
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${avgLat.toFixed(8)}&lon=${avgLon.toFixed(8)}&zoom=18&addressdetails=1`, {
            headers: { 'Accept-Language': 'id-ID,id;q=0.9,en;q=0.8' }
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.address) {
                const addr  = data.address;
                const parts = [];
                const placeKeys = ['amenity', 'school', 'college', 'university', 'building', 'office', 'shop', 'tourism', 'leisure', 'historic'];
                let placeName = '';
                for (const key of placeKeys) {
                    if (addr[key]) { placeName = addr[key]; break; }
                }
                if (placeName) parts.push(placeName);
                if (addr.road) parts.push(addr.road);
                if (addr.village) parts.push(addr.village);
                else if (addr.suburb) parts.push(addr.suburb);
                if (addr.town) parts.push(addr.town);
                else if (addr.city) parts.push(addr.city);
                else if (addr.municipality) parts.push(addr.municipality);
                else if (addr.county) parts.push(addr.county);
                let addressText = parts.join(', ');
                if (!addressText && data.display_name) addressText = data.display_name;
                locInput.value = addressText;
            }
        })
        .catch(err => console.error('Geocoding error:', err));

        // Check geofence dengan posisi rata-rata
        checkGeofence(avgLat, avgLon);
    }

    function onGpsError(err) {
        // Kalau sudah ada sampel, tetap rata-ratakan daripada error
        if (samples.length >= 1) { applyAveragedPosition(); return; }
        if (gpsSettled) return;
        gpsSettled = true;

        clearInterval(countdownInterval);
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
        if (gpsTimer)         clearTimeout(gpsTimer);

        showBanner('block');
        if (err.code === 1) {
            document.getElementById('gpsBlockTitle').textContent = 'Izin Lokasi Ditolak';
            document.getElementById('gpsBlockDesc').textContent  =
                'Anda menolak akses GPS. Buka pengaturan browser dan izinkan akses lokasi, lalu muat ulang halaman.';
        } else if (err.code === 2) {
            document.getElementById('gpsBlockTitle').textContent = 'GPS Tidak Tersedia';
            document.getElementById('gpsBlockDesc').textContent  =
                'Sinyal GPS tidak terdeteksi. Pastikan GPS aktif dan Anda berada di area dengan sinyal yang baik.';
        } else {
            document.getElementById('gpsBlockTitle').textContent = 'GPS Timeout';
            document.getElementById('gpsBlockDesc').textContent  =
                'Waktu pencarian lokasi habis. Pastikan GPS aktif, lalu muat ulang halaman.';
        }
        disableSubmit('GPS wajib aktif untuk melakukan absensi.');
    }

    // Kumpulkan bacaan GPS via watchPosition selama GPS_COLLECT_MS
    watchId = navigator.geolocation.watchPosition(
        (pos) => {
            const acc = pos.coords.accuracy;
            console.log(`[GPS] Bacaan #${samples.length + 1}: akurasi=${Math.round(acc)}m`);

            // Hanya simpan bacaan dengan akurasi layak
            if (acc <= MAX_ACC_THRESHOLD) {
                samples.push({ lat: pos.coords.latitude, lon: pos.coords.longitude, acc });
            }

            // Early exit: sudah ≥ 8 sampel dan rata-rata akurasi ≤ 10m — sudah sangat baik
            if (samples.length >= 8) {
                const avgAcc = samples.reduce((a, s) => a + s.acc, 0) / samples.length;
                if (avgAcc <= 10) applyAveragedPosition();
            }
        },
        onGpsError,
        { enableHighAccuracy: true, timeout: 25000, maximumAge: 0 }
    );

    // Setelah GPS_COLLECT_MS, rata-ratakan semua sampel yang sudah terkumpul
    gpsTimer = setTimeout(() => {
        if (samples.length >= 1) {
            applyAveragedPosition();
        } else if (!gpsSettled) {
            onGpsError({ code: 3, message: 'Timeout' });
        }
    }, GPS_COLLECT_MS);
})();


// ══════════════════════════════════════════════════════
//  SIGNATURE PAD
// ══════════════════════════════════════════════════════

const canvas  = document.getElementById('signaturePad');
const ctx     = canvas.getContext('2d');
let isDrawing = false;
let hasDrawn  = false;

function resizeCanvas() {
    const data = canvas.toDataURL();
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

// ══════════════════════════════════════════════════════
//  FORM VALIDATION
// ══════════════════════════════════════════════════════

function validateForm() {
    // Double-check: button should already be disabled, but extra safety
    if (submitBtn.disabled) {
        alert('Absensi tidak dapat dilakukan. Pastikan GPS aktif dan Anda berada dalam area apel.');
        return false;
    }
    if (!hasDrawn) {
        alert('Tanda tangan wajib diisi sebelum mengirimkan kehadiran.');
        return false;
    }
    document.getElementById('signatureInput').value = canvas.toDataURL('image/png');
    return true;
}
</script>
@endsection
