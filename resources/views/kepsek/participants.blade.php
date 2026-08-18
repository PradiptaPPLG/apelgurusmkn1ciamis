@extends('layouts.app')

@section('title', 'Data Guru & Peserta - Asign SMKN 1 Ciamis')
@section('body-class', 'admin-layout admin-sidebar-layout')

@section('content')
<div class="admin-wrapper">

    @include('kepsek.partials.sidebar', ['activePage' => 'participants'])

    <div class="admin-main">

        <header class="admin-mobile-topbar">
            <button class="sidebar-toggle-btn" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="mobile-topbar-title"><i class="fa-solid fa-users"></i> Data Guru &amp; Peserta</span>
        </header>

        <div class="admin-content-area">

            <div class="page-header">
                <div>
                    <h1 class="page-title">
                        <i class="fa-solid fa-users" style="color: var(--accent-indigo);"></i>
                        Data Guru &amp; Peserta
                    </h1>
                    <p class="page-subtitle">Daftar seluruh peserta aktif yang terdaftar di sistem.</p>
                </div>
            </div>

            {{-- Filter Bar --}}
            <form method="GET" action="{{ route('kepsek.participants') }}" id="filterForm"
                  style="background:var(--card-bg);border:1.5px solid var(--card-border);border-radius:var(--radius-md);padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;flex-wrap:wrap;gap:0.75rem;align-items:flex-end;">
                <div style="flex:1;min-width:180px;">
                    <label style="font-size:0.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:0.3rem;display:flex;align-items:center;gap:0.25rem;"><i class="fa-solid fa-magnifying-glass"></i> Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Nama / NIK / NIP lalu Enter..."
                           style="width:100%;padding:0.5rem 0.75rem;border:1.5px solid var(--input-border);border-radius:var(--radius-sm);background:var(--input-bg);color:var(--text-main);font-size:0.88rem;">
                </div>
                <div style="min-width:150px;">
                    <label style="font-size:0.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:0.3rem;display:flex;align-items:center;gap:0.25rem;"><i class="fa-solid fa-briefcase"></i> Jabatan</label>
                    <select name="role" onchange="this.form.submit()"
                            style="width:100%;padding:0.5rem 0.75rem;border:1.5px solid var(--input-border);border-radius:var(--radius-sm);background:var(--input-bg);color:var(--text-main);font-size:0.88rem;">
                        <option value="">Semua Jabatan</option>
                        @foreach(['Guru','TU','PPL','PPG','Wali Kelas'] as $r)
                            <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:140px;">
                    <label style="font-size:0.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:0.3rem;display:flex;align-items:center;gap:0.25rem;"><i class="fa-solid fa-circle-question"></i> Status</label>
                    <select name="status" onchange="this.form.submit()"
                            style="width:100%;padding:0.5rem 0.75rem;border:1.5px solid var(--input-border);border-radius:var(--radius-sm);background:var(--input-bg);color:var(--text-main);font-size:0.88rem;">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>
                <div style="display:flex;gap:0.5rem;align-items:flex-end;">
                    <button type="submit" class="btn btn-primary btn-sm" style="height:38px;padding:0 1rem;">
                        <i class="fa-solid fa-filter"></i> Saring
                    </button>
                    @if(request()->hasAny(['search','role','status']))
                    <a href="{{ route('kepsek.participants') }}" class="btn btn-secondary btn-sm" style="height:38px;padding:0 1rem;display:inline-flex;align-items:center;justify-content:center;">
                        <i class="fa-solid fa-rotate-left"></i> Atur Ulang
                    </a>
                    @endif
                </div>
            </form>

            {{-- Participants Table --}}
            @if ($participants->isEmpty())
                <div style="text-align:center;padding:4rem;background:rgba(255,255,255,0.2);border-radius:var(--radius-md);border:1.5px dashed var(--input-border);">
                    <i class="fa-solid fa-users-slash" style="font-size:3rem;color:var(--text-light);margin-bottom:1.25rem;"></i>
                    <p style="color:var(--text-muted);font-weight:500;font-size:1.1rem;">Tidak ada peserta ditemukan.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>NIK / NIP</th>
                                <th>Nama Lengkap</th>
                                <th>Jabatan</th>
                                <th>Jenis Kepegawaian</th>
                                <th style="text-align:center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($participants as $idx => $p)
                            <tr>
                                <td>{{ $participants->firstItem() + $idx }}</td>
                                <td style="font-family:monospace;font-size:0.85rem;">
                                    <div style="font-weight:700;">{{ $p->nik }}</div>
                                    @if($p->nip) <div style="font-size:0.72rem;color:var(--text-muted);">NIP: {{ $p->nip }}</div> @endif
                                    @if($p->other_id) <div style="font-size:0.72rem;color:var(--text-muted);">ID: {{ $p->other_id }}</div> @endif
                                </td>
                                <td style="font-weight:600;">{{ $p->name }}</td>
                                <td>
                                    <div><span class="badge badge-info">{{ $p->role }}</span></div>
                                    @if($p->jabatan) <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.2rem;">{{ $p->jabatan }}</div> @endif
                                </td>
                                <td>
                                    @if($p->jenis_kepegawaian)
                                        <span style="font-size:0.78rem;font-weight:700;text-transform:uppercase;background:rgba(99,102,241,0.08);color:var(--accent-indigo);padding:0.15rem 0.4rem;border-radius:4px;">
                                            {{ $p->jenis_kepegawaian }}
                                        </span>
                                    @else
                                        <span style="color:var(--text-light);font-size:0.82rem;">—</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    @if($p->status === 'aktif')
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge" style="background:rgba(239,68,68,0.1);color:#ef4444;">Non-Aktif</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrapper">
                    {{ $participants->links() }}
                </div>
            @endif

        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const isOpen  = sidebar.classList.contains('open');
    sidebar.classList.toggle('open', !isOpen);
    overlay.classList.toggle('active', !isOpen);
}
</script>
@endsection
