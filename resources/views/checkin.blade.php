@extends('layouts.app')

@section('title', 'Presensi Apel - SMKN 1 Ciamis')
@section('body-class', 'client-layout')

@section('content')
<div class="glass-container">

    {{-- Header: text only, no logo icon --}}
    <div class="brand-header" style="margin-bottom: 1.5rem;">
        <h1 class="brand-title">a-Sign Guru</h1>
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

    {{-- Already Attended Banner --}}
    <div id="alreadyAttendedBanner" style="display:none; text-align:center; background: rgba(16,185,129,0.07); border: 1.5px solid rgba(16,185,129,0.25); border-radius:10px; padding:1.5rem 1rem; margin-bottom:1rem;">
        <i class="fa-solid fa-circle-check" style="font-size:3rem; color:#10b981; margin-bottom:1rem; display:block;"></i>
        <div style="font-size:1.1rem; font-weight:700; color:#065f46; margin-bottom:0.5rem;">Anda Sudah Melakukan Absensi</div>
        <div style="font-size:0.85rem; color:#065f46;">Terima kasih, data kehadiran Anda untuk sesi ini dari perangkat ini sudah tercatat. Tidak dapat melakukan absensi lagi.</div>
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
        <input type="hidden" name="device_uuid"    id="deviceUuidInput">

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

{{-- ══════════════════════════════════════════════════════
     CAMERA SELFIE MODAL — Simple & Clean
     ══════════════════════════════════════════════════════ --}}
<div id="cameraModal" class="face-modal-backdrop" style="display: none;">
    <div id="cameraModalContent" class="face-modal-card">
        {{-- Close button top right --}}
        <button type="button" class="face-modal-close" onclick="closeCameraModal()" aria-label="Tutup">
            <i class="fa-solid fa-xmark"></i>
        </button>

        {{-- ── LIVE CAMERA STEP ── --}}
        <div id="cameraStep">
            {{-- Simple Header --}}
            <h2 class="face-modal-title">Verifikasi Wajah</h2>
            <p id="cameraInstruction" class="face-modal-subtitle">
                Posisikan wajah Anda tepat di dalam lingkaran
            </p>

            {{-- Clean Circular Camera Viewfinder --}}
            <div class="face-circle-wrapper">
                <video id="cameraVideo"
                       autoplay
                       playsinline
                       muted></video>
                <div id="faceGuideRing" class="face-circle-ring searching"></div>
            </div>

            {{-- Clean Status Pill --}}
            <div class="face-status-container">
                <div id="faceStatusBadge" class="face-status-badge searching">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <span>Mencari Wajah...</span>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="face-modal-actions">
                <button type="button" id="captureBtn" class="btn-face-capture" disabled>
                    <i class="fa-solid fa-camera"></i>
                    <span>Ambil Foto</span>
                </button>
                <button type="button" id="cancelCameraBtn" class="btn-face-cancel">
                    Batal
                </button>
            </div>
        </div>

        {{-- ── PREVIEW / CONFIRM STEP ── --}}
        <div id="previewStep" style="display: none;">
            <h2 class="face-modal-title">Hasil Foto Wajah</h2>
            <p class="face-modal-subtitle">
                Pastikan wajah Anda terlihat jelas & tegak
            </p>

            <div class="face-preview-wrapper">
                <img id="capturedPreview" alt="Foto Wajah">
                <div class="face-preview-badge">
                    <i class="fa-solid fa-check"></i>
                </div>
            </div>

            <div class="face-modal-actions" style="margin-top: 1.25rem;">
                <div style="display: flex; gap: 0.75rem; width: 100%;">
                    <button type="button" id="retakeBtn" class="btn-face-secondary" style="flex: 1;">
                        <i class="fa-solid fa-rotate-left"></i> Ulangi
                    </button>
                    <button type="button" id="usePhotoBtn" class="btn-face-primary" style="flex: 1.2;">
                        <i class="fa-solid fa-check"></i> Gunakan Foto
                    </button>
                </div>
            </div>
        </div>

        {{-- ── ERROR STEP ── --}}
        <div id="cameraErrorStep" style="display: none;">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem; color: #dc2626;">
                <i class="fa-solid fa-video-slash"></i>
            </div>
            <h2 class="face-modal-title">Kamera Tidak Dapat Diakses</h2>
            <p id="cameraErrorMsg" class="face-error-text" style="font-size: 0.85rem; color: #64748b; margin: 0.5rem 0 1.25rem; line-height: 1.5;">
                Izinkan akses kamera di browser Anda, lalu muat ulang halaman.
            </p>
            <button type="button" id="cancelCameraErrBtn" class="btn-face-secondary" style="width: 100%;">
                <i class="fa-solid fa-xmark"></i> Tutup
            </button>
        </div>
    </div>
</div>

<style>
/* ── Face Verification Modal — Simple & Clean ── */
.face-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
    animation: faceFadeIn 0.2s ease-out;
}

.face-modal-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    max-width: 360px;
    width: 100%;
    padding: 1.75rem 1.5rem 1.25rem;
    text-align: center;
    box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.18);
    animation: faceSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
}

.face-modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #64748b;
    border: none;
    font-size: 0.8rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}
.face-modal-close:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.face-modal-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.35rem;
}

.face-modal-subtitle {
    font-size: 0.84rem;
    color: #64748b;
    line-height: 1.4;
    margin: 0 0 1.25rem;
    min-height: 1.4em;
}

/* ── Clean Circular Viewport ── */
.face-circle-wrapper {
    position: relative;
    width: 195px;
    height: 195px;
    margin: 0 auto 0.85rem;
    border-radius: 50%;
    overflow: hidden;
    background: #0f172a;
}

#cameraVideo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    display: block;
    transform: scaleX(-1); /* mirror selfie */
}

.face-circle-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    pointer-events: none;
    transition: border-color 0.25s ease, box-shadow 0.25s ease;
    border: 3.5px solid #cbd5e1;
}
.face-circle-ring.searching {
    border-color: #cbd5e1;
}
.face-circle-ring.detected {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* ── Status Pill ── */
.face-status-container {
    min-height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.15rem;
}

.face-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    border-radius: 9999px;
    font-size: 0.78rem;
    font-weight: 600;
    transition: all 0.2s ease;
}
.face-status-badge.searching {
    background: #f1f5f9;
    color: #64748b;
}
.face-status-badge.detected {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

/* ── Action Buttons ── */
.face-modal-actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.btn-face-capture {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 0.8rem 1.25rem;
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 600;
    border: none;
    transition: all 0.2s ease;
    background: #e2e8f0;
    color: #94a3b8;
    cursor: not-allowed;
}
.btn-face-capture.active {
    background: #2563eb;
    color: #ffffff;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}
.btn-face-capture.active:hover {
    background: #1d4ed8;
}

.btn-face-cancel {
    background: transparent;
    border: none;
    color: #64748b;
    font-size: 0.85rem;
    font-weight: 500;
    padding: 0.4rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: color 0.15s ease;
}
.btn-face-cancel:hover {
    color: #0f172a;
}

.btn-face-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0.75rem 1.25rem;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    border: none;
    background: #2563eb;
    color: #ffffff;
    cursor: pointer;
    transition: background 0.15s ease;
}
.btn-face-primary:hover {
    background: #1d4ed8;
}

.btn-face-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0.75rem 1.25rem;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-face-secondary:hover {
    background: #f8fafc;
    color: #0f172a;
}

/* ── Preview Step ── */
.face-preview-wrapper {
    position: relative;
    width: 160px;
    height: 160px;
    margin: 0 auto 0.75rem;
}
#capturedPreview {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #10b981;
    display: block;
}
.face-preview-badge {
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #10b981;
    color: #ffffff;
    border: 2.5px solid #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}

/* ── Animations ── */
@keyframes faceFadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}
@keyframes faceSlideUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

{{-- Hidden canvas for photo capture --}}
<canvas id="captureCanvas" width="160" height="160" style="display:none;"></canvas>

{{-- Face Tracking Libraries (Local, Offline-ready) --}}
<script src="/js/tracking-min.js"></script>
<script src="/js/face-min.js"></script>

<script>
// ══════════════════════════════════════════════════════
//  DEVICE UUID — Identitas Unik Perangkat (Anti Titip Absen)
// ══════════════════════════════════════════════════════

/**
 * Generate a UUID v4.
 * Uses crypto.randomUUID() if available (modern browsers),
 * falls back to Math.random() for older/low-end Android devices.
 */
function generateUUID() {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }
    // Fallback for very old browsers / HP jadul
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        const r = Math.random() * 16 | 0;
        const v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

/**
 * Get or create a stable device UUID stored in localStorage.
 * This UUID persists across page reloads but resets if the user
 * manually clears browser data.
 */
function getDeviceUUID() {
    let uuid = localStorage.getItem('device_uuid');
    if (!uuid) {
        uuid = generateUUID();
        localStorage.setItem('device_uuid', uuid);
    }
    return uuid;
}

// Populate the hidden input immediately so it is included on form submit
const deviceUuidInput = document.getElementById('deviceUuidInput');
if (deviceUuidInput) {
    deviceUuidInput.value = getDeviceUUID();
}

// ══════════════════════════════════════════════════════
//  ALREADY ATTENDED LOGIC (Strictly Per-Session Code)
// ══════════════════════════════════════════════════════
function checkAlreadyAttended() {
    const codeInput = document.getElementById('code');
    const d = new Date();
    const localDate = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    
    let isAttended = false;
    const currentCode = codeInput ? codeInput.value.trim().toUpperCase() : '';

    // Clean any legacy generic daily flags so other sessions today are not blocked
    try {
        localStorage.removeItem(`device_attended_${localDate}`);
        localStorage.removeItem('last_attended_date');
        localStorage.removeItem('last_attended_session_code');
    } catch (e) {}

    // Only block if this EXACT session code was already submitted on this device today
    if (currentCode) {
        if (
            localStorage.getItem(`attended_${currentCode}_${localDate}`) === 'true' ||
            localStorage.getItem(`attended_${currentCode}`) === 'true'
        ) {
            isAttended = true;
        }
    }

    if (isAttended) {
        document.getElementById('apelForm').style.display = 'none';
        document.getElementById('alreadyAttendedBanner').style.display = 'block';
        
        // Hide distance and GPS banners just to be clean
        if (document.getElementById('gpsLoadingBanner')) document.getElementById('gpsLoadingBanner').style.display = 'none';
        if (document.getElementById('gpsBlockBanner'))   document.getElementById('gpsBlockBanner').style.display = 'none';
        if (document.getElementById('gpsOutsideBanner')) document.getElementById('gpsOutsideBanner').style.display = 'none';
        if (document.getElementById('gpsOkBanner'))      document.getElementById('gpsOkBanner').style.display = 'none';
        if (document.getElementById('distanceBar'))      document.getElementById('distanceBar').style.display = 'none';
        return true;
    } else {
        document.getElementById('apelForm').style.display = 'block';
        document.getElementById('alreadyAttendedBanner').style.display = 'none';
        return false;
    }
}

// Check on server via API asynchronously per specific session code
async function verifyServerAttendance() {
    const uuid = getDeviceUUID();
    const codeInput = document.getElementById('code');
    const code = codeInput ? codeInput.value.trim().toUpperCase() : '';
    if (!code || !uuid) return;
    
    try {
        const res = await fetch(`/api/check-attended?device_uuid=${encodeURIComponent(uuid)}&code=${encodeURIComponent(code)}`);
        if (res.ok) {
            const data = await res.json();
            if (data.attended) {
                const d = new Date();
                const localDate = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                localStorage.setItem(`attended_${code}_${localDate}`, 'true');
                checkAlreadyAttended();
            }
        }
    } catch (e) {
        console.warn('[AttendanceCheck] API warning:', e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    checkAlreadyAttended();
    verifyServerAttendance();
});

document.getElementById('code').addEventListener('input', () => {
    checkAlreadyAttended();
    verifyServerAttendance();
});

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
    if (checkAlreadyAttended()) return; // Stop geofencing if already attended

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
        const res  = await fetch(GEOFENCE_API, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        geofenceData = await res.json();
    } catch (e) {
        console.warn('Gagal memuat data geofence:', e);
        geofenceData = { configured: false };
    }

    // Step 3: Kumpulkan bacaan GPS selama GPS_COLLECT_MS, lalu rata-ratakan koordinat
    // dengan bobot berdasarkan akurasi (weighted average). Ini jauh lebih stabil dari
    // sekadar mengambil satu bacaan terbaik, karena noise acak saling menghilangkan.
    const GPS_COLLECT_MS    = 15000; // kumpulkan selama 15 detik
    const MAX_ACC_THRESHOLD = 100;   // abaikan bacaan akurasi > 100m (dulu 50m, terlalu ketat untuk device desktop/indors)
    const FALLBACK_THRESHOLD = 150;  // toleransi fallback jika sama sekali tidak dapat sampel akurat

    let samples           = [];   // { lat, lon, acc }
    let fallbackSamples   = [];   // { lat, lon, acc } untuk backup akurasi rendah
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
            el.textContent = `Mengumpulkan data GPS (${samples.length || fallbackSamples.length} sampel)... (${countdown}s)`;
        }
        if (countdown <= 0) clearInterval(countdownInterval);
    }, 1000);

    function applyAveragedPosition() {
        if (gpsSettled) return;
        gpsSettled = true;

        clearInterval(countdownInterval);
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
        if (gpsTimer)         clearTimeout(gpsTimer);

        // Jika sampel utama kosong, coba pakai fallback sampel
        let finalSamples = samples;
        if (finalSamples.length === 0) {
            finalSamples = fallbackSamples;
        }

        if (finalSamples.length === 0) { onGpsError({ code: 3, message: 'Timeout' }); return; }

        // Weighted average — bobot = 1/akurasi (akurasi lebih baik = bobot lebih tinggi)
        let totalW = 0, sumLat = 0, sumLon = 0;
        for (const s of finalSamples) {
            const w = 1 / s.acc;
            sumLat += s.lat * w;
            sumLon += s.lon * w;
            totalW += w;
        }
        const avgLat = sumLat / totalW;
        const avgLon = sumLon / totalW;
        const avgAcc = Math.round(finalSamples.reduce((a, s) => a + s.acc, 0) / finalSamples.length);

        console.log(`[GPS] ${finalSamples.length} sampel dirata-rata → lat=${avgLat.toFixed(6)}, lon=${avgLon.toFixed(6)}, ~${avgAcc}m`);

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
        if (samples.length >= 1 || fallbackSamples.length >= 1) { applyAveragedPosition(); return; }
        if (gpsSettled) return;
        gpsSettled = true;

        clearInterval(countdownInterval);
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
        if (gpsTimer)         clearTimeout(gpsTimer);

        showBanner('block');
        if (err.code === 1) {
            document.getElementById('gpsBlockTitle').textContent = 'Izin Lokasi Ditolak';
            document.getElementById('gpsBlockDesc').textContent  =
                'Anda menolak akses GPS. Buka pengaturan browser dan izinkan akses lokasi, lalu refresh halaman.';
        } else if (err.code === 2) {
            document.getElementById('gpsBlockTitle').textContent = 'GPS Tidak Tersedia';
            document.getElementById('gpsBlockDesc').textContent  =
                'Sinyal GPS tidak terdeteksi. Pastikan GPS aktif dan Anda berada di area dengan sinyal yang baik.';
        } else {
            document.getElementById('gpsBlockTitle').textContent = 'GPS Timeout';
            document.getElementById('gpsBlockDesc').textContent  =
                'Waktu pencarian lokasi habis. Pastikan GPS aktif, lalu refresh halaman.';
        }
        disableSubmit('GPS wajib aktif untuk melakukan absensi.');
    }

    // Kumpulkan bacaan GPS via watchPosition selama GPS_COLLECT_MS
    watchId = navigator.geolocation.watchPosition(
        (pos) => {
            const acc = pos.coords.accuracy;
            console.log(`[GPS] Bacaan #${samples.length + fallbackSamples.length + 1}: akurasi=${Math.round(acc)}m`);

            // Simpan ke sampel utama jika akurasinya baik
            if (acc <= MAX_ACC_THRESHOLD) {
                samples.push({ lat: pos.coords.latitude, lon: pos.coords.longitude, acc });
            } else if (acc <= FALLBACK_THRESHOLD) {
                fallbackSamples.push({ lat: pos.coords.latitude, lon: pos.coords.longitude, acc });
            }

            // Early exit jika sudah dapat sampel yang bagus
            if (samples.length >= 8) {
                const avgAcc = samples.reduce((a, s) => a + s.acc, 0) / samples.length;
                if (avgAcc <= 10) applyAveragedPosition();
            }
        },
        onGpsError,
        { enableHighAccuracy: true, timeout: 30000, maximumAge: 0 }
    );

    // Setelah GPS_COLLECT_MS, rata-ratakan semua sampel yang sudah terkumpul
    gpsTimer = setTimeout(() => {
        if (samples.length >= 1 || fallbackSamples.length >= 1) {
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
//  FORM VALIDATION + CAMERA SELFIE POPUP
// ══════════════════════════════════════════════════════

// Camera & Face Detection state
let cameraStream = null;
let capturedPhotoDataUrl = null;
let isFaceDetected = false;
let faceCheckInterval = null;
let lastFaceDetectedTime = 0;

const cameraModal     = document.getElementById('cameraModal');
const cameraVideo     = document.getElementById('cameraVideo');
const captureCanvas   = document.getElementById('captureCanvas');
const capturedPreview = document.getElementById('capturedPreview');
const cameraStep      = document.getElementById('cameraStep');
const previewStep     = document.getElementById('previewStep');
const cameraErrorStep = document.getElementById('cameraErrorStep');
const photoInput      = document.getElementById('photoInput');

// Dedicated offscreen canvas for ultra-fast (sub-5ms) face detection processing
const detectorCanvas = document.createElement('canvas');
detectorCanvas.width = 160;
detectorCanvas.height = 160;
const detectorCtx = detectorCanvas.getContext('2d', { willReadFrequently: true });

// Check for native hardware-accelerated Chrome FaceDetector API
let nativeFaceDetector = null;
if (typeof window.FaceDetector !== 'undefined') {
    try {
        nativeFaceDetector = new window.FaceDetector({ fastMode: true, maxDetectedFaces: 2 });
    } catch (e) {
        nativeFaceDetector = null;
    }
}

// Fallback JS Face Tracker (tracking.js Viola-Jones Haar Cascade)
let jsFaceTracker = null;
function initJsFaceTracker() {
    if (!jsFaceTracker && typeof tracking !== 'undefined' && tracking.ObjectTracker) {
        try {
            jsFaceTracker = new tracking.ObjectTracker('face');
            jsFaceTracker.setInitialScale(2.5);
            jsFaceTracker.setStepSize(1.8);
            jsFaceTracker.setEdgesDensity(0.1);
        } catch (e) {
            console.warn('[FaceDetection] Tracker init:', e);
        }
    }
}

/**
 * Updates UI based on face detection state.
 * @param {boolean} detected
 */
function setFaceDetectedState(detected) {
    isFaceDetected = detected;
    const ring = document.getElementById('faceGuideRing');
    const badge = document.getElementById('faceStatusBadge');
    const instruction = document.getElementById('cameraInstruction');
    const btn = document.getElementById('captureBtn');

    if (!ring || !badge || !btn) return;

    if (detected) {
        ring.className = 'face-circle-ring detected';
        badge.className = 'face-status-badge detected';
        badge.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span>Wajah Terdeteksi</span>';
        if (instruction) {
            instruction.innerHTML = '<span style="color: #059669; font-weight: 600;">Wajah terdeteksi!</span> Silakan ambil foto.';
        }
        btn.disabled = false;
        btn.classList.add('active');
    } else {
        ring.className = 'face-circle-ring searching';
        badge.className = 'face-status-badge searching';
        badge.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Mencari Wajah...</span>';
        if (instruction) {
            instruction.textContent = 'Posisikan wajah Anda tepat di dalam lingkaran';
        }
        btn.disabled = true;
        btn.classList.remove('active');
    }
}

/**
 * Checks if a face exists in the current camera frame.
 * @returns {Promise<boolean>}
 */
async function detectFaceInCurrentFrame() {
    if (!cameraVideo || cameraVideo.readyState < 2 || cameraVideo.paused || cameraVideo.ended) {
        return false;
    }

    try {
        // Draw downscaled 160x160 frame for sub-5ms processing
        detectorCtx.drawImage(cameraVideo, 0, 0, 160, 160);
    } catch (err) {
        return false;
    }

    // 1. Native Chrome/Edge FaceDetector API (fastest, high precision)
    if (nativeFaceDetector) {
        try {
            const faces = await nativeFaceDetector.detect(detectorCanvas);
            if (faces && faces.length > 0) {
                const box = faces[0].boundingBox;
                // Ensure face has reasonable dimension (at least ~25px in 160x160 canvas)
                if (box && box.width >= 24 && box.height >= 24) {
                    return true;
                }
            }
            return false;
        } catch (e) {
            // Fallback to JS tracker below if native throws
        }
    }

    // 2. tracking.js Haar Cascade Classifier (offline & universal fallback)
    initJsFaceTracker();
    if (typeof tracking !== 'undefined' && tracking.ViolaJones && tracking.ViolaJones.classifiers && tracking.ViolaJones.classifiers['face']) {
        try {
            const imgData = detectorCtx.getImageData(0, 0, 160, 160);
            const rects = tracking.ViolaJones.detect(
                imgData.data,
                160,
                160,
                jsFaceTracker ? jsFaceTracker.getInitialScale() : 2.5,
                jsFaceTracker ? jsFaceTracker.getScaleFactor() : 1.25,
                jsFaceTracker ? jsFaceTracker.getStepSize() : 1.8,
                jsFaceTracker ? jsFaceTracker.getEdgesDensity() : 0.1,
                tracking.ViolaJones.classifiers['face']
            );
            if (rects && rects.length > 0) {
                const valid = rects.some(r => r.width >= 26 && r.height >= 26);
                if (valid) return true;
            }
        } catch (e) {
            console.warn('[FaceDetection] ViolaJones detector warning:', e);
        }
    }

    return false;
}

/**
 * Starts continuous real-time face detection loop (runs every 180ms).
 */
function startFaceDetectionLoop() {
    stopFaceDetectionLoop();
    setFaceDetectedState(false);
    lastFaceDetectedTime = 0;

    let isChecking = false;
    faceCheckInterval = setInterval(async () => {
        if (isChecking || !cameraStream) return;
        isChecking = true;
        try {
            const hasFace = await detectFaceInCurrentFrame();
            const now = Date.now();
            if (hasFace) {
                lastFaceDetectedTime = now;
                setFaceDetectedState(true);
            } else {
                // Smoothing: wait 450ms before switching back to "not detected"
                // so natural blinks or minor head movements don't flicker the button
                if (now - lastFaceDetectedTime > 450) {
                    setFaceDetectedState(false);
                }
            }
        } catch (err) {
            console.warn('[FaceDetection] loop error:', err);
        } finally {
            isChecking = false;
        }
    }, 180);
}

/**
 * Stops the face detection loop.
 */
function stopFaceDetectionLoop() {
    if (faceCheckInterval) {
        clearInterval(faceCheckInterval);
        faceCheckInterval = null;
    }
}

/** Show/hide steps inside the camera modal */
function showCameraStep(step) {
    cameraStep.style.display      = step === 'camera'  ? 'block' : 'none';
    previewStep.style.display     = step === 'preview' ? 'block' : 'none';
    cameraErrorStep.style.display = step === 'error'   ? 'block' : 'none';
}

/** Stop camera stream, tracker, and close modal */
function closeCameraModal() {
    stopFaceDetectionLoop();
    if (cameraStream) {
        cameraStream.getTracks().forEach(t => t.stop());
        cameraStream = null;
    }
    cameraModal.style.display = 'none';
    capturedPhotoDataUrl = null;
    setFaceDetectedState(false);
}

/** Open camera modal and request camera access */
async function openCameraModal() {
    cameraModal.style.display = 'flex';
    showCameraStep('camera');
    setFaceDetectedState(false);

    // Try progressively simpler constraints for max device compatibility
    const constraintSets = [
        { video: { facingMode: { ideal: 'user' }, width: { ideal: 640 }, height: { ideal: 640 } }, audio: false },
        { video: { facingMode: 'user' }, audio: false },
        { video: true, audio: false },
    ];

    let lastErr = null;
    for (const constraints of constraintSets) {
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
            break; // success
        } catch (err) {
            lastErr = err;
            console.warn('[Camera] Constraint failed, trying simpler:', err.name, err.message);
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError' || err.name === 'NotFoundError') {
                break;
            }
        }
    }

    if (!cameraStream) {
        const err = lastErr;
        console.error('[Camera] getUserMedia failed:', err && err.name, err && err.message);
        let msg = 'Kamera tidak dapat diakses (' + (err && err.name || 'UnknownError') + '). Izinkan kamera di pengaturan browser, lalu refresh halaman.';
        if (err && (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError')) {
            msg = 'Izin kamera ditolak. Klik ikon kamera/gembok di address bar, pilih Izinkan, lalu refresh halaman dan coba lagi.';
        } else if (err && (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError')) {
            msg = 'Tidak ada kamera yang terdeteksi pada perangkat ini.';
        } else if (err && (err.name === 'NotReadableError' || err.name === 'TrackStartError')) {
            msg = 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi kamera lain, refresh halaman, lalu coba lagi.';
        }
        document.getElementById('cameraErrorMsg').textContent = msg;
        showCameraStep('error');
        return;
    }

    // Attach stream then play and start real-time face detection
    cameraVideo.srcObject = cameraStream;
    cameraVideo.muted = true;
    cameraVideo.onloadedmetadata = function () {
        cameraVideo.play().then(() => {
            startFaceDetectionLoop();
        }).catch(function (playErr) {
            console.warn('[Camera] video.play() failed:', playErr);
        });
    };
}

/** Capture current video frame to canvas → compress to JPEG */
async function capturePhoto() {
    // Extra validation: Ensure face is actively detected
    if (!isFaceDetected) {
        const hasFaceNow = await detectFaceInCurrentFrame();
        if (!hasFaceNow) {
            alert('Wajah tidak terdeteksi! Harap posisikan wajah Anda tepat di dalam lingkaran.');
            return;
        }
    }

    const ctx = captureCanvas.getContext('2d');
    // Draw video frame, mirrored horizontally (selfie)
    ctx.save();
    ctx.translate(160, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(cameraVideo, 0, 0, 160, 160);
    ctx.restore();

    // Compress to high quality JPEG
    capturedPhotoDataUrl = captureCanvas.toDataURL('image/jpeg', 0.82);

    capturedPreview.src = capturedPhotoDataUrl;
    stopFaceDetectionLoop();
    showCameraStep('preview');

    // Stop stream while in preview to save battery
    if (cameraStream) {
        cameraStream.getTracks().forEach(t => t.stop());
        cameraStream = null;
    }
}

/** Retake — restart camera and detection */
async function retakePhoto() {
    capturedPhotoDataUrl = null;
    showCameraStep('camera');
    setFaceDetectedState(false);
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 640 } },
            audio: false,
        });
        cameraVideo.srcObject = cameraStream;
        cameraVideo.muted = true;
        cameraVideo.onloadedmetadata = function () {
            cameraVideo.play().then(() => {
                startFaceDetectionLoop();
            }).catch(e => console.warn(e));
        };
    } catch (err) {
        document.getElementById('cameraErrorMsg').textContent = 'Gagal membuka ulang kamera. Refresh halaman dan coba lagi.';
        showCameraStep('error');
    }
}

/** Use the captured photo and actually submit the form */
function usePhotoAndSubmit() {
    if (!capturedPhotoDataUrl) return;
    photoInput.value = capturedPhotoDataUrl;
    closeCameraModal();
    // Set signature then submit programmatically
    document.getElementById('signatureInput').value = canvas.toDataURL('image/png');
    document.getElementById('apelForm').submit();
}

// ── Button wiring ────────────────────────────────────
document.getElementById('captureBtn').addEventListener('click', capturePhoto);
document.getElementById('retakeBtn').addEventListener('click', retakePhoto);
document.getElementById('usePhotoBtn').addEventListener('click', usePhotoAndSubmit);
document.getElementById('cancelCameraBtn').addEventListener('click', closeCameraModal);
document.getElementById('cancelCameraErrBtn').addEventListener('click', closeCameraModal);

// Close on backdrop click
cameraModal.addEventListener('click', function (e) {
    if (e.target === cameraModal) closeCameraModal();
});

/** validateForm — intercepts form submit → opens camera popup instead */
function validateForm() {
    if (submitBtn.disabled) {
        alert('Absensi tidak dapat dilakukan. Pastikan GPS aktif dan Anda berada dalam area apel.');
        return false;
    }
    if (!hasDrawn) {
        alert('Tanda tangan wajib diisi sebelum mengirimkan kehadiran.');
        return false;
    }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('Browser Anda tidak mendukung akses kamera. Gunakan Chrome atau browser modern untuk melakukan absensi.');
        return false;
    }

    openCameraModal();
    return false;
}
</script>
@endsection
