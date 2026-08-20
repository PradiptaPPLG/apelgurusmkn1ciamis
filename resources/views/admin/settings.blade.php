<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Konfigurasi sistem presensi apel SMKN 1 Ciamis: pengaturan jam operasional default, identitas sekolah, penandatangan laporan, dan radius geofencing.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <title>Pengaturan Aplikasi &amp; Sesi - Asign SMKN 1 Ciamis</title>

    {{-- Fonts & Icons --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- CSS Assets --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="admin-body">

<div class="admin-wrapper">

    {{-- Sidebar --}}
    @include('admin.partials.sidebar', ['activePage' => 'settings'])

    {{-- Main Content Area --}}
    <div class="admin-main">

        {{-- Global Topbar --}}
        @include('admin.partials.topbar')

        <div class="admin-content-area">

            {{-- Page Header --}}
            <div class="page-header" style="margin-bottom: 1.5rem;">
                <h1 class="page-title">
                    <i class="fa-solid fa-sliders" style="color: var(--accent-indigo);"></i>
                    <span>Pengaturan Aplikasi &amp; Sesi Apel</span>
                </h1>
                <div class="page-subtitle">Konfigurasi jam operasional default, identitas instansi sekolah, dan data penandatangan laporan.</div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 0.85rem 1.25rem; border-radius: var(--radius-md);">
                    <i class="fa-solid fa-circle-check" style="font-size: 1.2rem; color: #10b981;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 1.5rem; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.85rem 1.25rem; border-radius: var(--radius-md);">
                    <div style="font-weight: 700; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Terjadi kesalahan pada pengaturan formulir:</span>
                    </div>
                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.84rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">

                    {{-- Card 1: Default Jam Operasional Apel --}}
                    <div class="card" style="background: #ffffff; border: 1px solid var(--card-border); border-radius: var(--radius-lg); padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--card-border); padding-bottom: 1rem;">
                            <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(245, 158, 11, 0.1); color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin: 0;">Default Jam Sesi Apel</h2>
                                <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0.15rem 0 0;">Otomatis mengisi jam saat membuat sesi baru</p>
                            </div>
                        </div>

                        {{-- Apel Pagi --}}
                        <div style="background: #f8fafc; border: 1px solid var(--card-border); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.25rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 700; color: #b45309; margin-bottom: 0.75rem;">
                                <i class="fa-solid fa-sun"></i>
                                <span>Default Apel Pagi</span>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div>
                                    <label for="default_pagi_start" style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Mulai</label>
                                    <input type="time" id="default_pagi_start" name="default_pagi_start" aria-label="Waktu Mulai Apel Pagi" class="form-control" value="{{ old('default_pagi_start', $appSetting->default_pagi_start) }}" required style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.85rem;">
                                </div>
                                <div>
                                    <label for="default_pagi_end" style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Selesai</label>
                                    <input type="time" id="default_pagi_end" name="default_pagi_end" aria-label="Waktu Selesai Apel Pagi" class="form-control" value="{{ old('default_pagi_end', $appSetting->default_pagi_end) }}" required style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.85rem;">
                                </div>
                            </div>
                        </div>

                        {{-- Apel Sore --}}
                        <div style="background: #f8fafc; border: 1px solid var(--card-border); border-radius: var(--radius-md); padding: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 700; color: #4338ca; margin-bottom: 0.75rem;">
                                <i class="fa-solid fa-cloud-sun"></i>
                                <span>Default Apel Sore</span>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div>
                                    <label for="default_sore_start" style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Mulai</label>
                                    <input type="time" id="default_sore_start" name="default_sore_start" aria-label="Waktu Mulai Apel Sore" class="form-control" value="{{ old('default_sore_start', $appSetting->default_sore_start) }}" required style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.85rem;">
                                </div>
                                <div>
                                    <label for="default_sore_end" style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Selesai</label>
                                    <input type="time" id="default_sore_end" name="default_sore_end" aria-label="Waktu Selesai Apel Sore" class="form-control" value="{{ old('default_sore_end', $appSetting->default_sore_end) }}" required style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.85rem;">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card 2: Penandatangan Dokumen Laporan (Kepala Sekolah) --}}
                    <div class="card" style="background: #ffffff; border: 1px solid var(--card-border); border-radius: var(--radius-lg); padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--card-border); padding-bottom: 1rem;">
                            <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                <i class="fa-solid fa-file-signature"></i>
                            </div>
                            <div>
                                <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin: 0;">Penandatangan Laporan</h2>
                                <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0.15rem 0 0;">Dicetak pada export PDF &amp; Excel rekap bulanan</p>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label for="kepsek_name" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                                Nama Kepala Sekolah <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" id="kepsek_name" name="kepsek_name" aria-label="Nama Kepala Sekolah" class="form-control" value="{{ old('kepsek_name', $appSetting->kepsek_name) }}" required style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.88rem;">
                        </div>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label for="kepsek_nip" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                                NIP Kepala Sekolah <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" id="kepsek_nip" name="kepsek_nip" aria-label="NIP Kepala Sekolah" class="form-control" value="{{ old('kepsek_nip', $appSetting->kepsek_nip) }}" required style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.88rem;">
                        </div>

                        <div class="form-group">
                            <label for="kepsek_pangkat" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                                Pangkat / Golongan
                            </label>
                            <input type="text" id="kepsek_pangkat" name="kepsek_pangkat" aria-label="Pangkat atau Golongan Kepala Sekolah" class="form-control" value="{{ old('kepsek_pangkat', $appSetting->kepsek_pangkat) }}" placeholder="Contoh: Pembina Utama Muda / IV c" style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.88rem;">
                        </div>
                    </div>

                    {{-- Card 3: Identitas Sekolah & Aplikasi --}}
                    <div class="card" style="background: #ffffff; border: 1px solid var(--card-border); border-radius: var(--radius-lg); padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--card-border); padding-bottom: 1rem;">
                            <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(99, 102, 241, 0.1); color: var(--accent-indigo); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                <i class="fa-solid fa-school"></i>
                            </div>
                            <div>
                                <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin: 0;">Identitas Sekolah &amp; Aplikasi</h2>
                                <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0.15rem 0 0;">Nama instansi dan branding aplikasi</p>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label for="school_name" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                                Nama Sekolah / Instansi <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" id="school_name" name="school_name" aria-label="Nama Sekolah atau Instansi" class="form-control" value="{{ old('school_name', $appSetting->school_name) }}" required style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.88rem;">
                        </div>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label for="app_name" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                                Nama Aplikasi <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" id="app_name" name="app_name" aria-label="Nama Aplikasi" class="form-control" value="{{ old('app_name', $appSetting->app_name) }}" required style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.88rem;">
                        </div>

                        <div class="form-group">
                            <label for="school_address" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                                Alamat Sekolah
                            </label>
                            <input type="text" id="school_address" name="school_address" aria-label="Alamat Sekolah" class="form-control" value="{{ old('school_address', $appSetting->school_address) }}" style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.88rem;">
                        </div>
                    </div>

                    {{-- Card 4: Geofencing & Lokasi --}}
                    <div class="card" style="background: #ffffff; border: 1px solid var(--card-border); border-radius: var(--radius-lg); padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--card-border); padding-bottom: 1rem;">
                            <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(14, 165, 233, 0.1); color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                <i class="fa-solid fa-location-crosshairs"></i>
                            </div>
                            <div>
                                <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin: 0;">Radius &amp; Geofencing</h2>
                                <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0.15rem 0 0;">Toleransi jarak presensi lokasi GPS</p>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label for="default_radius" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                                Default Radius Toleransi (Meter) <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="number" id="default_radius" name="default_radius" aria-label="Default Radius Toleransi Geofencing Meter" class="form-control" value="{{ old('default_radius', $appSetting->default_radius) }}" min="5" max="1000" required style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.88rem;">
                            <span style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-top: 0.35rem;">
                                <i class="fa-solid fa-circle-info"></i> Rekomendasi: 20-50 meter untuk mengakomodasi akurasi GPS smartphone.
                            </span>
                        </div>

                        <div style="padding: 1rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-md);">
                            <div style="font-size: 0.84rem; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.3rem;">
                                <i class="fa-solid fa-map-pin"></i>
                                <span>Titik Koordinat Pusat</span>
                            </div>
                            <div style="font-size: 0.78rem; color: #15803d; margin-bottom: 0.75rem;">
                                Saat ini: <strong>{{ $apelLocation->label ?: 'Belum disetel' }}</strong> (Radius: {{ $apelLocation->radius_meter }}m)
                            </div>
                            <a href="{{ route('admin.apel.location') }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.78rem; padding: 0.4rem 0.85rem; border: 1px solid #16a34a; color: #16a34a; border-radius: var(--radius-md); text-decoration: none; font-weight: 600; background: #ffffff;">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                <span>Atur Titik Peta di Halaman Titik Apel</span>
                            </a>
                        </div>
                    </div>

                </div>

                {{-- Submit Button Bar --}}
                <div style="display: flex; justify-content: flex-end; padding: 1.25rem 1.75rem; background: #ffffff; border: 1px solid var(--card-border); border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 2rem; font-size: 0.9rem; font-weight: 700; border-radius: var(--radius-md); cursor: pointer; border: none; background: var(--accent-indigo); color: #ffffff;">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Seluruh Pengaturan</span>
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    if (sidebar) sidebar.classList.toggle('open');
}
</script>

</body>
</html>
