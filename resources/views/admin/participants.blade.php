@extends('layouts.app')

@section('title', 'Kelola Peserta - E-Apel SMKN 1 Ciamis')

@section('body-class', 'admin-layout')

@section('content')
<style>
    /* Modal Backdrop */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(8px);
        z-index: 999;
        overflow-y: auto;
        padding: 2rem 0;
        align-items: flex-start;
        justify-content: center;
        animation: fadeIn 0.3s ease-out;
    }
    
    .modal-content {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        width: 90%;
        max-width: 500px;
        padding: 2.25rem 2rem;
        box-shadow: var(--card-shadow);
        margin: auto;
        animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

</style>

<!-- Admin Navbar -->
<header class="admin-navbar">
    <div class="admin-nav-brand">
        <div class="admin-nav-logo">
            <i class="fa-solid fa-gauge"></i>
        </div>
        <div>
            <div>E-Apel Admin</div>
            <div style="font-size: 0.7rem; font-weight: 500; color: var(--text-muted);">SMKN 1 Ciamis</div>
        </div>
    </div>
    
    <nav class="admin-nav-links">
        <a href="{{ route('admin.dashboard') }}" class="admin-nav-link">
            <i class="fa-solid fa-calendar-days"></i> Sesi Apel
        </a>
        <a href="{{ route('admin.participants') }}" class="admin-nav-link active">
            <i class="fa-solid fa-users"></i> Data Guru & Peserta
        </a>
    </nav>

    <div class="admin-user-menu">
        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);" class="hide-mobile">
            {{ Auth::user()->name }}
        </span>
        <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--accent-rose); border-color: rgba(244, 63, 94, 0.2);">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </button>
        </form>
    </div>
</header>

<!-- Main Admin Area -->
<main class="glass-container-wide">
    
    <!-- Success Alert -->
    @if (session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Error Alert -->
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

    <!-- Content Split Panel -->
    <div class="panel-split">
        
        <!-- Left Side: Add Participant Form -->
        <div class="form-modal-inline">
            <h3 style="margin-bottom: 1.25rem; font-size: 1.2rem; color: var(--text-main); border-bottom: 1px solid var(--input-border); padding-bottom: 0.5rem;">
                <i class="fa-solid fa-user-plus" style="color: var(--accent-indigo)"></i> Tambah Peserta Baru
            </h3>
            
            <form action="{{ route('admin.participants.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label class="form-label" for="nik">NIK / NIP / ID (Primary Key)</label>
                    <input type="text" name="nik" id="nik" class="form-control" placeholder="Masukkan NIK unik" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: Budi Gunawan, S.Pd." required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="jabatan">Jabatan</label>
                    <input type="text" name="jabatan" id="jabatan" class="form-control" placeholder="Contoh: Guru Mapel, Kepala TU">
                </div>

                <div class="form-group">
                    <label class="form-label" for="jenis_kepegawaian">Jenis Kepegawaian</label>
                    <select name="jenis_kepegawaian" id="jenis_kepegawaian" class="form-control form-select">
                        <option value="">— Pilih —</option>
                        <option value="asn">ASN</option>
                        <option value="pns">PNS</option>
                        <option value="p3k">P3K</option>
                        <option value="honorer">Honorer</option>
                        <option value="mahasiswa">Mahasiswa (PPL/PPG)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="role">Peran / Kategori</label>
                    <select name="role" id="role" class="form-control form-select" required>
                        <option value="Guru">Guru</option>
                        <option value="TU">Staf TU</option>
                        <option value="PPL">Peserta PPL</option>
                        <option value="PPG">Peserta PPG</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select name="status" id="status" class="form-control form-select" required>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 0.5rem;">
                    <i class="fa-solid fa-save"></i> Simpan Peserta
                </button>
            </form>
        </div>

        <!-- Right Side: Participants List with Filter -->
        <div>
            <!-- Filters Form -->
            <div style="background: rgba(255, 255, 255, 0.4); padding: 1.25rem; border-radius: var(--radius-md); border: 1.5px solid var(--card-border); margin-bottom: 1.5rem;">
                <form action="{{ route('admin.participants') }}" method="GET" class="filter-form-grid">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="search" style="font-size: 0.75rem;">Cari Nama / NIK</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Kata kunci..." value="{{ request('search') }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="filter_role" style="font-size: 0.75rem;">Peran</label>
                        <select name="role" id="filter_role" class="form-control form-select" style="padding: 0.75rem 1rem;">
                            <option value="">Semua</option>
                            <option value="Guru" {{ request('role') === 'Guru' ? 'selected' : '' }}>Guru</option>
                            <option value="TU" {{ request('role') === 'TU' ? 'selected' : '' }}>Staf TU</option>
                            <option value="PPL" {{ request('role') === 'PPL' ? 'selected' : '' }}>PPL</option>
                            <option value="PPG" {{ request('role') === 'PPG' ? 'selected' : '' }}>PPG</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="filter_status" style="font-size: 0.75rem;">Status</label>
                        <select name="status" id="filter_status" class="form-control form-select" style="padding: 0.75rem 1rem;">
                            <option value="">Semua</option>
                            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.25rem;">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                        <a href="{{ route('admin.participants') }}" class="btn btn-secondary" style="padding: 0.75rem 1.25rem;">
                            <i class="fa-solid fa-undo"></i>
                        </a>
                    </div>
                </form>
            </div>

            <h3 style="margin-bottom: 1rem; font-size: 1.2rem; color: var(--text-main);">
                <i class="fa-solid fa-users" style="color: var(--accent-teal)"></i> Daftar Guru & Peserta
            </h3>

            @if ($participants->isEmpty())
                <div style="text-align: center; padding: 3rem; background: rgba(255,255,255,0.2); border-radius: var(--radius-md); border: 1.5px dashed var(--input-border);">
                    <i class="fa-regular fa-user-circle" style="font-size: 2.5rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                    <p style="color: var(--text-muted); font-weight: 500;">Tidak ada data peserta ditemukan.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>NIK / ID</th>
                                <th>Nama Lengkap</th>
                                <th>Jabatan</th>
                                <th>Jenis Kepegawaian</th>
                                <th>Kategori</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($participants as $p)
                                <tr>
                                    <td style="font-family: monospace; font-weight: 700; color: var(--text-main);">
                                        {{ $p->nik }}
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main);">{{ $p->name }}</div>
                                    </td>
                                    <td>
                                        {{ $p->jabatan ?: '—' }}
                                    </td>
                                    <td>
                                        @if($p->jenis_kepegawaian)
                                            <span class="badge badge-info" style="text-transform: uppercase;">{{ $p->jenis_kepegawaian }}</span>
                                        @else
                                            <span style="color: var(--text-light);">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $p->role }}</span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge {{ $p->status === 'aktif' ? 'badge-success' : 'badge-danger' }}">
                                            {{ ucfirst($p->status) }}
                                        </span>
                                    </td>
                                    <td style="text-align: right; overflow: visible;">
                                        <div class="dropdown-kebab">
                                            <button class="kebab-btn" onclick="toggleDropdown(event, 'drop-{{ $p->nik }}')" title="Aksi">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <div id="drop-{{ $p->nik }}" class="dropdown-menu-content">
                                                <button type="button" onclick="openEditModal('{{ $p->nik }}', '{{ addslashes($p->name) }}', '{{ addslashes($p->jabatan ?? '') }}', '{{ $p->jenis_kepegawaian ?? '' }}', '{{ $p->role }}', '{{ $p->status }}')">
                                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                                </button>
                                                <form action="{{ route('admin.participants.delete', $p->nik) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data peserta ini? Semua riwayat kehadirannya juga akan dihapus.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="delete-btn">
                                                        <i class="fa-solid fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrapper">
                    {{ $participants->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</main>

<!-- Edit Participant Modal Overlay -->
<div id="editModal" class="modal-backdrop">
    <div class="modal-content">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid var(--input-border); padding-bottom: 0.5rem;">
            <h3 style="font-size: 1.25rem;"><i class="fa-solid fa-user-pen" style="color: var(--accent-indigo)"></i> Edit Data Peserta</h3>
            <button type="button" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-light);" onclick="closeEditModal()">&times;</button>
        </div>

        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label" for="edit_nik">NIK / NIP / ID</label>
                <input type="text" name="nik" id="edit_nik" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="edit_name">Nama Lengkap</label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="edit_jabatan">Jabatan</label>
                <input type="text" name="jabatan" id="edit_jabatan" class="form-control" placeholder="Contoh: Guru Mapel, Kepala TU">
            </div>

            <div class="form-group">
                <label class="form-label" for="edit_jenis_kepegawaian">Jenis Kepegawaian</label>
                <select name="jenis_kepegawaian" id="edit_jenis_kepegawaian" class="form-control form-select">
                    <option value="">— Pilih —</option>
                    <option value="asn">ASN</option>
                    <option value="pns">PNS</option>
                    <option value="p3k">P3K</option>
                    <option value="honorer">Honorer</option>
                    <option value="mahasiswa">Mahasiswa (PPL/PPG)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="edit_role">Peran / Kategori</label>
                <select name="role" id="edit_role" class="form-control form-select" required>
                    <option value="Guru">Guru</option>
                    <option value="TU">Staf TU</option>
                    <option value="PPL">Peserta PPL</option>
                    <option value="PPG">Peserta PPG</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="edit_status">Status</label>
                <select name="status" id="edit_status" class="form-control form-select" required>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>

            <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary btn-block" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');
    
    function openEditModal(nik, name, jabatan, jenis_kepegawaian, role, status) {
        document.getElementById('edit_nik').value = nik;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_jabatan').value = jabatan;
        document.getElementById('edit_jenis_kepegawaian').value = jenis_kepegawaian;
        document.getElementById('edit_role').value = role;
        document.getElementById('edit_status').value = status;
        
        // Dynamically update form action url
        editForm.action = "{{ url('/admin/participants') }}/" + encodeURIComponent(nik);
        
        editModal.style.display = 'flex';
    }

    function closeEditModal() {
        editModal.style.display = 'none';
    }

    // Close modal when clicking outside content area
    window.addEventListener('click', (e) => {
        if (e.target === editModal) {
            closeEditModal();
        }
    });

    // Toggle dropdown function
    function toggleDropdown(event, id) {
        event.stopPropagation();
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu-content').forEach(el => {
            if (el.id !== id) {
                el.style.display = 'none';
            }
        });
        
        const dropdown = document.getElementById(id);
        if (dropdown) {
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }
    }

    // Close all dropdowns when clicking outside
    window.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu-content').forEach(el => {
            el.style.display = 'none';
        });
    });
</script>
@endsection
