@extends('layouts.app')

@section('title', 'Presensi Apel - SMKN 1 Ciamis')

@section('body-class', 'client-layout')

@section('content')
<div class="glass-container">
    <div class="brand-header">
        <div class="brand-logo">
            <i class="fa-solid fa-signature"></i>
        </div>
        <h1 class="brand-title">E-Apel Guru</h1>
        <p class="brand-subtitle">SMKN 1 Ciamis</p>
    </div>

    <!-- Warning / Error Alerts -->
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

    <!-- Session Status Indicator -->
    @if ($openSession)
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <div>
                <strong>Sesi Terbuka:</strong> {{ $openSession->title }} <br>
                <small>Batas Waktu: {{ \Carbon\Carbon::parse($openSession->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($openSession->end_time)->format('H:i') }} WIB</small>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <div>
                <strong>Tidak Ada Sesi Aktif:</strong> Saat ini tidak ada sesi apel yang sedang dibuka. Pastikan Anda melakukan presensi pada jam operasional.
            </div>
        </div>
    @endif

    <form action="{{ route('apel.submit') }}" method="POST" id="apelForm" onsubmit="return validateForm()">
        @csrf
        
        <!-- Hidden Inputs for Coordinates -->
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
        
        <!-- Hidden Inputs for Canvas Drawing -->
        <input type="hidden" name="signature" id="signatureInput">
        <input type="hidden" name="photo" id="photoInput">

        <!-- GPS Status Panel -->
        <div class="geo-panel" id="geoPanel">
            <i class="fa-solid fa-location-dot"></i>
            <div id="geoStatus">Mendapatkan lokasi GPS Anda...</div>
        </div>

        <!-- Registration Code -->
        <div class="form-group">
            <label class="form-label" for="code">Kode Registrasi Apel</label>
            <input type="text" 
                   name="code" 
                   id="code" 
                   class="form-control" 
                   placeholder="Contoh: QORRG" 
                   maxlength="5" 
                   value="{{ old('code', $selectedCode) }}" 
                   required 
                   style="text-transform: uppercase;">
            @if ($urlSession && !$openSession)
                <small style="color: var(--accent-rose); font-weight: 500;">Sesi untuk kode ini belum dimulai atau sudah berakhir.</small>
            @endif
        </div>

        <!-- NIK / NIP / ID -->
        <div class="form-group">
            <label class="form-label" for="nik">NIK / NIP / ID Lainnya</label>
            <input type="text" 
                   name="nik" 
                   id="nik" 
                   class="form-control" 
                   placeholder="Masukkan NIK Anda" 
                   value="{{ old('nik') }}" 
                   required>
        </div>

        <!-- Nama Lengkap -->
        <div class="form-group">
            <label class="form-label" for="name">Nama Lengkap</label>
            <input type="text" 
                   name="name" 
                   id="name" 
                   class="form-control" 
                   placeholder="Masukkan Nama Lengkap" 
                   value="{{ old('name') }}" 
                   required>
        </div>

        <!-- Peran / Kategori -->
        <div class="form-group">
            <label class="form-label" for="role">Peran / Kategori</label>
            <select name="role" id="role" class="form-control form-select" required>
                <option value="Guru" {{ old('role') === 'Guru' ? 'selected' : '' }}>Guru</option>
                <option value="TU" {{ old('role') === 'TU' ? 'selected' : '' }}>Staf TU</option>
                <option value="PPL" {{ old('role') === 'PPL' ? 'selected' : '' }}>PPL</option>
                <option value="PPG" {{ old('role') === 'PPG' ? 'selected' : '' }}>PPG</option>
            </select>
        </div>

        <!-- Camera Selfie Capture (Optional) -->
        <div class="form-group">
            <label class="form-label">Foto Selfie (Opsional)</label>
            <div class="camera-wrapper" id="cameraWrapper">
                <div class="camera-placeholder" id="cameraPlaceholder">
                    <i class="fa-solid fa-camera"></i>
                    <p>Klik tombol di bawah untuk mengambil selfie menggunakan kamera depan</p>
                </div>
                <video id="webcam" class="camera-video" autoplay playsinline style="display: none;"></video>
                <img id="selfiePreview" class="camera-preview-img" style="display: none;">
            </div>
            
            <div class="camera-controls">
                <button type="button" class="btn btn-secondary btn-sm" id="btnStartCamera">
                    <i class="fa-solid fa-video"></i> Aktifkan Kamera
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnCapturePhoto" style="display: none;">
                    <i class="fa-solid fa-camera-retro"></i> Ambil Foto
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btnResetPhoto" style="display: none;">
                    <i class="fa-solid fa-trash"></i> Reset Foto
                </button>
            </div>
        </div>

        <!-- Tanda Tangan (Signature) Pad -->
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

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">
            <i class="fa-solid fa-paper-plane"></i> Kirim Kehadiran (Apel)
        </button>
    </form>

    <div class="app-footer">
        &copy; {{ date('Y') }} SMKN 1 Ciamis. All rights reserved.
    </div>
</div>

<!-- JavaScript for GPS, Canvas Drawing & Camera Snapshots -->
<script>
    // 1. Geolocation Fetching
    const geoPanel = document.getElementById('geoPanel');
    const geoStatus = document.getElementById('geoStatus');
    const latInput = document.getElementById('latitude');
    const lonInput = document.getElementById('longitude');
    
    // NIK lookup elements
    const nameInput = document.getElementById('name');
    const roleInput = document.getElementById('role');

    function getGPSLocation() {
        if (!navigator.geolocation) {
            geoStatus.innerHTML = '<span style="color: var(--accent-rose)">GPS tidak didukung oleh browser Anda.</span>';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude.toFixed(8);
                const lon = position.coords.longitude.toFixed(8);
                latInput.value = lat;
                lonInput.value = lon;
                geoStatus.innerHTML = `Lokasi terkunci: <span class="geo-status-active">${lat}, ${lon}</span>`;
                geoPanel.style.borderStyle = 'solid';
                geoPanel.style.borderColor = 'rgba(20, 184, 166, 0.4)';
                geoPanel.style.background = 'rgba(20, 184, 166, 0.05)';
            },
            (error) => {
                let errorMsg = 'Gagal mengakses GPS. Pastikan izin lokasi aktif.';
                if (error.code === error.PERMISSION_DENIED) {
                    errorMsg = 'Akses lokasi ditolak. Silakan izinkan lokasi di pengaturan browser.';
                }
                geoStatus.innerHTML = `<span style="color: var(--accent-rose)">${errorMsg}</span>`;
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    // Trigger GPS acquisition on load
    window.addEventListener('DOMContentLoaded', () => {
        getGPSLocation();
        setupSignaturePad();
        setupWebcam();
        
        // Auto-fetch name and role when NIK loses focus (blur)
        const nikInput = document.getElementById('nik');
        if (nikInput) {
            nikInput.addEventListener('blur', function() {
                const nik = this.value.trim();
                if (nik.length > 2) {
                    fetch(`/api/participant/${encodeURIComponent(nik)}`)
                        .then(response => {
                            if (response.ok) return response.json();
                            throw new Error('Not found');
                        })
                        .then(data => {
                            if (data && data.name) {
                                if (nameInput) nameInput.value = data.name;
                                if (roleInput) roleInput.value = data.role;
                            }
                        })
                        .catch(err => {
                            // Suppress errors, let user register on-the-fly
                        });
                }
            });
        }
    });


    // 2. HTML5 Signature Pad Canvas Logic
    const canvas = document.getElementById('signaturePad');
    const ctx = canvas.getContext('2d');
    let isDrawing = false;
    let hasDrawn = false;

    // Adjust canvas resolution for high-DPI displays
    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        ctx.strokeStyle = '#0f172a'; // matches text-main
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        clearSignature(); // clears screen and sets hasDrawn = false
    }

    window.addEventListener('resize', resizeCanvas);

    function setupSignaturePad() {
        resizeCanvas();

        // Mouse Events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Touch Events
        canvas.addEventListener('touchstart', startDrawingTouch);
        canvas.addEventListener('touchmove', drawTouch);
        canvas.addEventListener('touchend', stopDrawing);
    }

    function startDrawing(e) {
        isDrawing = true;
        ctx.beginPath();
        ctx.moveTo(e.offsetX, e.offsetY);
    }

    function draw(e) {
        if (!isDrawing) return;
        ctx.lineTo(e.offsetX, e.offsetY);
        ctx.stroke();
        hasDrawn = true;
    }

    function startDrawingTouch(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const rect = canvas.getBoundingClientRect();
        isDrawing = true;
        ctx.beginPath();
        ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
    }

    function drawTouch(e) {
        e.preventDefault();
        if (!isDrawing) return;
        const touch = e.touches[0];
        const rect = canvas.getBoundingClientRect();
        ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
        ctx.stroke();
        hasDrawn = true;
    }

    function stopDrawing() {
        isDrawing = false;
    }

    function clearSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasDrawn = false;
    }


    // 3. Webcam Selfie Logic
    let streamInstance = null;
    const video = document.getElementById('webcam');
    const selfiePreview = document.getElementById('selfiePreview');
    const cameraPlaceholder = document.getElementById('cameraPlaceholder');
    const photoInput = document.getElementById('photoInput');
    
    const btnStartCamera = document.getElementById('btnStartCamera');
    const btnCapturePhoto = document.getElementById('btnCapturePhoto');
    const btnResetPhoto = document.getElementById('btnResetPhoto');

    function setupWebcam() {
        btnStartCamera.addEventListener('click', async () => {
            try {
                streamInstance = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user' },
                    audio: false
                });
                video.srcObject = streamInstance;
                video.style.display = 'block';
                cameraPlaceholder.style.display = 'none';
                
                btnStartCamera.style.display = 'none';
                btnCapturePhoto.style.display = 'inline-flex';
            } catch (err) {
                alert('Tidak dapat mengakses kamera depan. Pastikan izin kamera telah diberikan.');
                console.error(err);
            }
        });

        btnCapturePhoto.addEventListener('click', () => {
            const photoCanvas = document.createElement('canvas');
            photoCanvas.width = video.videoWidth || 640;
            photoCanvas.height = video.videoHeight || 480;
            const photoCtx = photoCanvas.getContext('2d');
            
            // Mirror image draw
            photoCtx.translate(photoCanvas.width, 0);
            photoCtx.scale(-1, 1);
            photoCtx.drawImage(video, 0, 0, photoCanvas.width, photoCanvas.height);
            
            // Save base64
            const dataUrl = photoCanvas.toDataURL('image/jpeg', 0.85);
            photoInput.value = dataUrl;
            
            // Display preview
            selfiePreview.src = dataUrl;
            selfiePreview.style.display = 'block';
            video.style.display = 'none';
            
            btnCapturePhoto.style.display = 'none';
            btnResetPhoto.style.display = 'inline-flex';

            // Stop camera stream
            stopCameraStream();
        });

        btnResetPhoto.addEventListener('click', () => {
            photoInput.value = '';
            selfiePreview.style.display = 'none';
            selfiePreview.src = '';
            cameraPlaceholder.style.display = 'flex';
            
            btnResetPhoto.style.display = 'none';
            btnStartCamera.style.display = 'inline-flex';
        });
    }

    function stopCameraStream() {
        if (streamInstance) {
            streamInstance.getTracks().forEach(track => track.stop());
            streamInstance = null;
        }
    }


    // 4. Form Validation before submit
    function validateForm() {
        // A. Validate GPS
        if (!latInput.value || !lonInput.value) {
            alert('Harap tunggu atau aktifkan lokasi GPS Anda sebelum melakukan absensi.');
            getGPSLocation(); // Try getting location again
            return false;
        }

        // B. Validate Signature
        if (!hasDrawn) {
            alert('Tanda tangan wajib diisi sebelum mengirimkan kehadiran.');
            return false;
        }

        // Save signature base64 data to hidden input
        const sigDataUrl = canvas.toDataURL('image/png');
        document.getElementById('signatureInput').value = sigDataUrl;

        return true;
    }
</script>
@endsection
