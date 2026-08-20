{{-- Global Topbar Partial with Interactive Profile & Settings Dropdown --}}
<header class="admin-topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle-btn" onclick="toggleSidebar()" aria-label="Buka Menu Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="topbar-brand-indicator">
            <i class="fa-solid fa-graduation-cap" style="color: var(--accent-indigo); font-size: 1.05rem;"></i>
            <span>SMKN 1 Ciamis</span>
            <span style="color: var(--text-light); margin: 0 0.35rem;">&bull;</span>
            <span style="font-weight: 500; color: var(--text-muted);">Sistem Presensi Apel</span>
        </div>
    </div>
    
    <div class="topbar-right">
        {{-- Interactive Profile Button with Dropdown --}}
        <div class="topbar-user-dropdown-wrapper" style="position: relative;">
            <button type="button" class="topbar-user" onclick="toggleProfileDropdown(event)" aria-label="Menu Pengguna">
                <div class="topbar-user-avatar" style="background: {{ Auth::user()->isKepsek() ? 'rgba(124, 58, 237, 0.1)' : 'rgba(99, 102, 241, 0.1)' }}; color: {{ Auth::user()->isKepsek() ? '#7c3aed' : '#4f46e5' }};">
                    <i class="fa-solid {{ Auth::user()->isKepsek() ? 'fa-user-tie' : 'fa-user-shield' }}"></i>
                </div>
                <div class="topbar-user-info">
                    <div class="topbar-user-name">{{ Auth::user()->name }}</div>
                    <div class="topbar-user-role">{{ Auth::user()->isKepsek() ? 'Kepala Sekolah' : 'Administrator' }}</div>
                </div>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.72rem; color: var(--text-muted); margin-left: 0.25rem;"></i>
            </button>

            {{-- Dropdown Menu --}}
            <div id="profileDropdownMenu" class="topbar-dropdown-menu">
                <div class="topbar-dropdown-header">
                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.88rem;">{{ Auth::user()->name }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ Auth::user()->email }}</div>
                    <div style="margin-top: 0.3rem;">
                        <span class="badge {{ Auth::user()->isKepsek() ? 'badge-warning' : 'badge-primary' }}" style="font-size: 0.68rem; padding: 0.15rem 0.5rem;">
                            {{ Auth::user()->isKepsek() ? 'Kepala Sekolah' : 'Administrator' }}
                        </span>
                    </div>
                </div>
                
                <div class="topbar-dropdown-divider"></div>

                <a href="{{ Auth::user()->isKepsek() ? route('kepsek.profile') : route('admin.profile') }}" class="topbar-dropdown-item">
                    <i class="fa-solid fa-user-gear" style="color: var(--accent-indigo);"></i>
                    <div>
                        <div style="font-weight: 600; color: var(--text-main);">Profil &amp; Ganti Password</div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);">Ubah nama display &amp; kata sandi</div>
                    </div>
                </a>

                @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.settings') }}" class="topbar-dropdown-item">
                    <i class="fa-solid fa-sliders" style="color: var(--accent-teal);"></i>
                    <div>
                        <div style="font-weight: 600; color: var(--text-main);">Pengaturan Aplikasi &amp; Sesi</div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);">Jam apel, toleransi, data sekolah</div>
                    </div>
                </a>
                @endif

                <div class="topbar-dropdown-divider"></div>

                <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="topbar-dropdown-item text-danger" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer;">
                        <i class="fa-solid fa-right-from-bracket" style="color: #ef4444;"></i>
                        <span style="font-weight: 600;">Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<script>
function toggleProfileDropdown(event) {
    event.stopPropagation();
    const menu = document.getElementById('profileDropdownMenu');
    if (menu) {
        menu.classList.toggle('show');
    }
}

window.addEventListener('click', (e) => {
    const menu = document.getElementById('profileDropdownMenu');
    if (menu && menu.classList.contains('show')) {
        if (!menu.contains(e.target)) {
            menu.classList.remove('show');
        }
    }
});
</script>
