<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kelola profil administrator dan kata sandi akun sistem presensi apel SMKN 1 Ciamis.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <title>Profil &amp; Keamanan Akun - Asign SMKN 1 Ciamis</title>

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
    @include('admin.partials.sidebar', ['activePage' => 'profile'])

    {{-- Main Content Area --}}
    <div class="admin-main">

        {{-- Global Topbar --}}
        @include('admin.partials.topbar')

        <div class="admin-content-area">

            {{-- Page Header --}}
            <div class="page-header" style="margin-bottom: 1.5rem;">
                <h1 class="page-title">
                    <i class="fa-solid fa-user-gear" style="color: var(--accent-indigo);"></i>
                    <span>Profil &amp; Keamanan Akun</span>
                </h1>
                <div class="page-subtitle">Kelola nama tampilan profil, email login, dan kata sandi akun administrator.</div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 0.85rem 1.25rem; border-radius: var(--radius-md);">
                    <i class="fa-solid fa-circle-check" style="font-size: 1.2rem; color: #10b981;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('success_password'))
                <div class="alert alert-success" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 0.85rem 1.25rem; border-radius: var(--radius-md);">
                    <i class="fa-solid fa-shield-halved" style="font-size: 1.2rem; color: #10b981;"></i>
                    <span>{{ session('success_password') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 1.5rem; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.85rem 1.25rem; border-radius: var(--radius-md);">
                    <div style="font-weight: 700; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Terjadi kesalahan pada data formulir:</span>
                    </div>
                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.84rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Profile Grid Form --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem;">

                {{-- Card 1: Informasi Profil --}}
                <div class="card" style="background: #ffffff; border: 1px solid var(--card-border); border-radius: var(--radius-lg); padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--card-border); padding-bottom: 1rem;">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(99, 102, 241, 0.1); color: var(--accent-indigo); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                            <i class="fa-solid fa-id-card"></i>
                        </div>
                        <div>
                            <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin: 0;">Informasi Profil</h2>
                            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0.15rem 0 0;">Ubah nama yang muncul pada topbar dan email akun</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label for="name" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                                Nama Lengkap (Display Name) <span style="color: #ef4444;">*</span>
                            </label>
                            <div class="input-with-icon" style="position: relative;">
                                <i class="fa-solid fa-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                                <input type="text" id="name" name="name" aria-label="Nama Lengkap Display Name" class="form-control" value="{{ old('name', $user->name) }}" required style="width: 100%; padding: 0.65rem 1rem 0.65rem 2.5rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.88rem;">
                            </div>
                            <span style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-top: 0.35rem;">
                                <i class="fa-solid fa-circle-info"></i> Nama ini langsung muncul di pojok kanan atas topbar setelah disimpan.
                            </span>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label for="email" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                                Email / Username Login <span style="color: #ef4444;">*</span>
                            </label>
                            <div class="input-with-icon" style="position: relative;">
                                <i class="fa-solid fa-envelope" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                                <input type="email" id="email" name="email" aria-label="Email atau Username Login" class="form-control" value="{{ old('email', $user->email) }}" required style="width: 100%; padding: 0.65rem 1rem 0.65rem 2.5rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.88rem;">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.75rem;">
                            <label class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                                Peran / Hak Akses
                            </label>
                            <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1rem; background: #f8fafc; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.85rem; font-weight: 600; color: var(--text-main);">
                                <i class="fa-solid fa-user-shield" style="color: var(--accent-indigo);"></i>
                                <span>Administrator (Akses Penuh Pengelolaan)</span>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.5rem; font-size: 0.85rem; font-weight: 600; border-radius: var(--radius-md); cursor: pointer; border: none; background: var(--accent-indigo); color: #ffffff;">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Simpan Perubahan Profil</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Card 2: Ganti Password --}}
                <div class="card" style="background: #ffffff; border: 1px solid var(--card-border); border-radius: var(--radius-lg); padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--card-border); padding-bottom: 1rem;">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <div>
                            <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin: 0;">Ganti Password</h2>
                            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0.15rem 0 0;">Perbarui kata sandi untuk mengamankan akun admin</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.profile.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label for="current_password" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                                Password Saat Ini <span style="color: #ef4444;">*</span>
                            </label>
                            <div class="input-with-icon" style="position: relative;">
                                <i class="fa-solid fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                                <input type="password" name="current_password" id="current_password" aria-label="Password Saat Ini" class="form-control" required style="width: 100%; padding: 0.65rem 2.5rem 0.65rem 2.5rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.88rem;">
                                <button type="button" onclick="togglePassVisibility('current_password', 'eye_current')" aria-label="Lihat atau Sembunyikan Password Saat Ini" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0.25rem;">
                                    <i id="eye_current" class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label for="password" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                                Password Baru <span style="color: #ef4444;">*</span>
                            </label>
                            <div class="input-with-icon" style="position: relative;">
                                <i class="fa-solid fa-shield" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                                <input type="password" name="password" id="password" aria-label="Password Baru" class="form-control" placeholder="Minimal 6 karakter" required style="width: 100%; padding: 0.65rem 2.5rem 0.65rem 2.5rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.88rem;">
                                <button type="button" onclick="togglePassVisibility('password', 'eye_new')" aria-label="Lihat atau Sembunyikan Password Baru" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0.25rem;">
                                    <i id="eye_new" class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.75rem;">
                            <label for="password_confirmation" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.4rem;">
                                Konfirmasi Password Baru <span style="color: #ef4444;">*</span>
                            </label>
                            <div class="input-with-icon" style="position: relative;">
                                <i class="fa-solid fa-check-double" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                                <input type="password" name="password_confirmation" id="password_confirmation" aria-label="Konfirmasi Password Baru" class="form-control" placeholder="Ulangi password baru" required style="width: 100%; padding: 0.65rem 2.5rem 0.65rem 2.5rem; border: 1px solid var(--card-border); border-radius: var(--radius-md); font-size: 0.88rem;">
                                <button type="button" onclick="togglePassVisibility('password_confirmation', 'eye_confirm')" aria-label="Lihat atau Sembunyikan Konfirmasi Password Baru" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0.25rem;">
                                    <i id="eye_confirm" class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.5rem; font-size: 0.85rem; font-weight: 600; border-radius: var(--radius-md); cursor: pointer; border: none; background: #ef4444; color: #ffffff;">
                                <i class="fa-solid fa-lock"></i>
                                <span>Perbarui Password</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>

        </div>

    </div>

</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    if (sidebar) sidebar.classList.toggle('open');
}

function togglePassVisibility(inputId, eyeId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(eyeId);
    if (!input || !eye) return;

    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.remove('fa-eye');
        eye.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
    }
}
</script>

</body>
</html>
