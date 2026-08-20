{{-- Kepala Sekolah Sidebar Partial --}}
<aside class="admin-sidebar" id="adminSidebar">
    {{-- Sidebar Header / Brand --}}
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon" style="background: none;">
            <img src="/icons/logoadmin.png" alt="Logo Asign SMKN 1 Ciamis" style="width: 42px; height: 42px; object-fit: contain;">
        </div>
        <div class="sidebar-brand-text">
            <div class="sidebar-brand-name">Asign</div>
            <div class="sidebar-brand-sub">SMKN 1 Ciamis</div>
        </div>
        <button class="sidebar-close-btn" id="sidebarCloseBtn" onclick="toggleSidebar()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">
        <div class="sidebar-nav-label">Menu</div>

        <a href="{{ route('kepsek.dashboard') }}"
           class="sidebar-nav-item {{ ($activePage ?? '') === 'dashboard' ? 'active' : '' }}">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('kepsek.sessions.index') }}"
           class="sidebar-nav-item {{ ($activePage ?? '') === 'sessions' ? 'active' : '' }}">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Sesi Apel</span>
        </a>

        <a href="{{ route('kepsek.participants') }}"
           class="sidebar-nav-item {{ ($activePage ?? '') === 'participants' ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i>
            <span>Data Guru &amp; Peserta</span>
        </a>

        <a href="{{ route('kepsek.rekap.index') }}"
           class="sidebar-nav-item {{ ($activePage ?? '') === 'rekap' ? 'active' : '' }}">
            <i class="fa-solid fa-table-list"></i>
            <span>Rekap Bulanan</span>
        </a>
    </nav>

    {{-- Footer link at bottom --}}
    <div class="sidebar-footer">
        <a href="{{ route('apel.index') }}" class="sidebar-nav-item" target="_blank" style="font-size:0.8rem;">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            <span>Lihat Halaman Absen</span>
        </a>
    </div>
</aside>

{{-- Overlay for mobile --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
