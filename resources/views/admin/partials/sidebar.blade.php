{{-- Admin Sidebar Partial --}}
{{-- Usage: @include('admin.partials.sidebar', ['activePage' => 'dashboard|participants|location']) --}}

<aside class="admin-sidebar" id="adminSidebar">
    {{-- Sidebar Header / Brand --}}
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon" style="background: none;">
            <img src="/icons/logoadmin.png" alt="Logo" style="width: 42px; height: 42px; object-fit: contain;">
        </div>
        <div class="sidebar-brand-text">
            <div class="sidebar-brand-name">Asign</div>
            <div class="sidebar-brand-sub">SMKN 1 Ciamis</div>
        </div>
        <button class="sidebar-close-btn" id="sidebarCloseBtn" onclick="toggleSidebar()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    {{-- Admin info --}}
    <div class="sidebar-user">
        <div class="sidebar-user-avatar" style="background: rgba(99, 102, 241, 0.1); color: #4f46e5;">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
            <div class="sidebar-user-role">
                {{ Auth::user()->isKepsek() ? 'Kepala Sekolah' : 'Administrator' }}
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">
        <div class="sidebar-nav-label">Menu Utama</div>

        <a href="{{ route('admin.dashboard') }}"
           class="sidebar-nav-item {{ ($activePage ?? '') === 'dashboard' ? 'active' : '' }}">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Dashboard & Sesi</span>
        </a>

        <a href="{{ route('admin.participants') }}"
           class="sidebar-nav-item {{ ($activePage ?? '') === 'participants' ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i>
            <span>Data Guru & Peserta</span>
        </a>

        <div class="sidebar-nav-label" style="margin-top: 1rem;">Konfigurasi</div>

        <a href="{{ route('admin.apel.location') }}"
           class="sidebar-nav-item {{ ($activePage ?? '') === 'location' ? 'active' : '' }}">
            <i class="fa-solid fa-location-dot"></i>
            <span>Titik Apel</span>
            @php $loc = \App\Models\ApelLocation::getInstance(); @endphp
            @if(!$loc->isConfigured())
                <span class="sidebar-badge-warn" title="Belum dikonfigurasi">!</span>
            @else
                <span class="sidebar-badge-ok" title="Sudah dikonfigurasi"><i class="fa-solid fa-check"></i></span>
            @endif
        </a>
    </nav>

    {{-- Logout at bottom --}}
    <div class="sidebar-footer">
        <a href="{{ route('apel.index') }}" class="sidebar-nav-item" target="_blank" style="font-size: 0.8rem;">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            <span>Lihat Halaman Absen</span>
        </a>
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button type="submit" class="sidebar-logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>

{{-- Overlay for mobile --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
