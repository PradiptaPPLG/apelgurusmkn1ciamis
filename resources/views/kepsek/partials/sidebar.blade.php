{{-- Kepala Sekolah Sidebar Partial --}}
<aside class="admin-sidebar" id="adminSidebar">
    {{-- Sidebar Header / Brand --}}
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon" style="background: none;">
            <img src="/icons/logoadmin.png" alt="Logo" style="width: 32px; height: 32px; object-fit: contain;">
        </div>
        <div class="sidebar-brand-text">
            <div class="sidebar-brand-name">Asign</div>
            <div class="sidebar-brand-sub">SMKN 1 Ciamis</div>
        </div>
        <button class="sidebar-close-btn" id="sidebarCloseBtn" onclick="toggleSidebar()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    {{-- User info --}}
    <div class="sidebar-user">
        <div class="sidebar-user-avatar" style="background: rgba(124, 58, 237, 0.1); color: #7c3aed;">
            <i class="fa-solid fa-user-tie"></i>
        </div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
            <div class="sidebar-user-role">Kepala Sekolah</div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">
        <div class="sidebar-nav-label">Menu</div>

        <a href="{{ route('kepsek.dashboard') }}"
           class="sidebar-nav-item {{ ($activePage ?? '') === 'dashboard' ? 'active' : '' }}">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('kepsek.participants') }}"
           class="sidebar-nav-item {{ ($activePage ?? '') === 'participants' ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i>
            <span>Data Guru &amp; Peserta</span>
        </a>
    </nav>

    {{-- Logout at bottom --}}
    <div class="sidebar-footer">
        <a href="{{ route('apel.index') }}" class="sidebar-nav-item" target="_blank" style="font-size:0.8rem;">
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
