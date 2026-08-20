@extends('layouts.app')

@section('title', 'Pratinjau Impor - Asign SMKN 1 Ciamis')

@section('body-class', 'admin-layout admin-sidebar-layout')

@section('content')
<div class="admin-wrapper">

    {{-- Sidebar --}}
    @include('admin.partials.sidebar', ['activePage' => 'participants'])

    {{-- Main Content --}}
    <div class="admin-main">

        {{-- Global Topbar --}}
        @include('admin.partials.topbar')

        <div class="admin-content-area">

            {{-- Page Header --}}
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;">
                <div>
                    <h1 class="page-title">
                        <a href="{{ route('admin.participants') }}" class="hide-mobile" style="color: var(--text-muted); margin-right: 0.5rem; text-decoration: none;" title="Kembali ke Kelola Peserta">
                            <i class="fa-solid fa-arrow-left"></i>
                        </a>
                        <i class="fa-solid fa-file-import" style="color: var(--accent-indigo);"></i> Pencocokan Kolom Impor
                    </h1>
                    <p class="page-subtitle">Cocokkan kolom tabel Excel Anda dengan kolom data sistem kami.</p>
                </div>
                <div>
                    <a href="{{ route('admin.participants') }}" class="btn btn-secondary" style="font-weight: 600;">
                        <i class="fa-solid fa-ban"></i> Batalkan
                    </a>
                </div>
            </div>

            {{-- Alerts --}}
            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.participants.import') }}" method="POST" id="importForm">
                @csrf
                <input type="hidden" name="temp_path" value="{{ $tempPath }}">

                <div class="panel-split" style="grid-template-columns: 1.8fr 1fr; gap: 1.75rem; display: grid;">
                    
                    {{-- Left Column: Mapping Section --}}
                    <div>
                        <div style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 1.75rem; border-radius: var(--radius-lg); box-shadow: var(--card-shadow); margin-bottom: 1.75rem;">
                            <h3 style="font-size: 1.15rem; color: var(--text-main); margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-arrows-split-up-and-left" style="color: var(--accent-indigo);"></i> Pemetaan Kolom Excel
                            </h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5;">
                                Sistem telah menebak kecocokan kolom secara otomatis. Silakan tinjau kembali pemetaan di bawah. Kolom yang tidak dipetakan/diabaikan tidak akan dimasukkan ke database.
                            </p>

                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                @foreach ($previewData['headers'] as $colLetter => $headerText)
                                    <div class="mapping-row" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: #f8fafc; border: 1px solid var(--input-border); border-radius: var(--radius-md); gap: 1rem; flex-wrap: wrap;">
                                        <div style="flex: 1; min-width: 200px;">
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <span class="badge badge-info" style="font-family: monospace; font-size: 0.9rem; padding: 0.25rem 0.5rem; background: var(--bg-secondary); color: var(--text-main); border: 1px solid var(--input-border);">
                                                    Kolom {{ $colLetter }}
                                                </span>
                                                <strong style="color: var(--text-main); font-size: 0.95rem;">{{ $headerText ?: '(Kolom Kosong)' }}</strong>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.35rem;">
                                                Contoh data: 
                                                @php
                                                    $samples = [];
                                                    foreach(array_slice($previewData['previewRows'], 0, 3) as $row) {
                                                        if (!empty($row[$colLetter])) {
                                                            $samples[] = '"' . e(Str::limit($row[$colLetter], 15)) . '"';
                                                        }
                                                    }
                                                    echo empty($samples) ? '<span style="color: var(--text-light)">—</span>' : implode(', ', $samples);
                                                @endphp
                                            </div>
                                        </div>

                                        <div style="min-width: 250px;">
                                            <select name="mapping[{{ $colLetter }}]" class="form-control form-select col-mapping-select" data-col="{{ $colLetter }}" onchange="updateTableHeaders()" style="padding: 0.6rem 0.85rem; font-size: 0.875rem;">
                                                <option value="ignore">— Abaikan Kolom Ini —</option>
                                                @foreach ($dbFields as $fieldName => $fieldLabel)
                                                    <option value="{{ $fieldName }}" {{ ($previewData['guesses'][$colLetter] ?? '') === $fieldName ? 'selected' : '' }}>
                                                        {{ $fieldLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Options & Controls --}}
                    <div>
                        <div style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 1.75rem; border-radius: var(--radius-lg); box-shadow: var(--card-shadow); position: sticky; top: 2rem;">
                            <h3 style="font-size: 1.15rem; color: var(--text-main); margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-sliders" style="color: var(--accent-teal);"></i> Pengaturan Impor
                            </h3>

                            <div class="form-group" style="margin-bottom: 1.25rem;">
                                <label class="form-label" for="duplicate_action" style="font-weight: 600;">Jika NIK Sudah Ada</label>
                                <select name="duplicate_action" id="duplicate_action" class="form-control form-select" required style="padding: 0.65rem 0.85rem; font-size: 0.875rem;">
                                    <option value="update">Perbarui (Update) data Guru lama dengan data baru</option>
                                    <option value="skip">Abaikan (Skip) baris Excel dan biarkan data lama</option>
                                </select>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.35rem; line-height: 1.3;">
                                    Karena NIK bersifat unik, tentukan tindakan jika sistem menemukan NIK yang sama.
                                </span>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.25rem;">
                                <label class="form-label" for="default_role" style="font-weight: 600;">Peran / Kategori Default</label>
                                <select name="default_role" id="default_role" class="form-control form-select" required style="padding: 0.65rem 0.85rem; font-size: 0.875rem;">
                                    <option value="Guru">Guru</option>
                                    <option value="TU">Staf TU</option>
                                    <option value="PPL">Peserta PPL</option>
                                    <option value="PPG">Peserta PPG</option>
                                    <option value="Wali Kelas">Wali Kelas</option>
                                </select>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.35rem; line-height: 1.3;">
                                    Digunakan jika kolom Peran/Kategori kosong atau tidak terdeteksi dalam Excel.
                                </span>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label class="form-label" for="default_status" style="font-weight: 600;">Status Default</label>
                                <select name="default_status" id="default_status" class="form-control form-select" required style="padding: 0.65rem 0.85rem; font-size: 0.875rem;">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.35rem; line-height: 1.3;">
                                    Status keaktifan jika data status di file kosong atau tidak terpetakan.
                                </span>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block" style="padding: 0.85rem; font-size: 1rem; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 0.5rem; background: linear-gradient(135deg, var(--accent-indigo), var(--accent-teal)); border: none;">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Mulai Impor Data
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Preview Table Section --}}
                <div style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 1.75rem; border-radius: var(--radius-lg); box-shadow: var(--card-shadow); margin-top: 1.75rem;">
                    <h3 style="font-size: 1.15rem; color: var(--text-main); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-table" style="color: var(--accent-indigo);"></i> Pratinjau Data Asli (Maks. 5 Baris Pertama)
                    </h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                        Periksa bagaimana isi file Excel Anda akan dibaca berdasarkan pemetaan kolom di atas.
                    </p>

                    <div class="table-responsive">
                        <table class="table-custom" style="width: 100%;">
                            <thead>
                                <tr id="preview-mapped-headers" style="background: #f1f5f9; font-weight: 800;">
                                    @foreach ($previewData['headers'] as $colLetter => $headerText)
                                        <th style="padding: 0.5rem 0.75rem; font-size: 0.75rem; color: var(--accent-indigo); text-transform: uppercase;" id="th-mapped-{{ $colLetter }}">
                                            Dipetakan Ke: N/A
                                        </th>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($previewData['headers'] as $colLetter => $headerText)
                                        <th style="font-family: var(--font-primary); font-weight: 600;">
                                            <div style="font-size: 0.7rem; color: var(--text-light);">{{ $colLetter }}</div>
                                            {{ $headerText ?: '(Kosong)' }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @if (empty($previewData['previewRows']))
                                    <tr>
                                        <td colspan="{{ count($previewData['headers']) }}" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                            Tidak ada data untuk ditampilkan.
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($previewData['previewRows'] as $row)
                                        <tr>
                                            @foreach ($previewData['headers'] as $colLetter => $headerText)
                                                <td>
                                                    {{ $row[$colLetter] ?? '' }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Kamus nama field database ke bahasa Indonesia untuk label tabel
    const fieldLabels = {
        'nik': 'NIK',
        'name': 'Nama Lengkap',
        'nip': 'NIP',
        'other_id': 'ID Lainnya',
        'jabatan': 'Jabatan',
        'jenis_kepegawaian': 'Jns. Kepegawaian',
        'role': 'Peran/Kategori',
        'status': 'Status Keaktifan',
        'ignore': 'DIABAIKAN'
    };

    function updateTableHeaders() {
        document.querySelectorAll('.col-mapping-select').forEach(select => {
            const colLetter = select.getAttribute('data-col');
            const th = document.getElementById('th-mapped-' + colLetter);
            if (th) {
                const selectedVal = select.value;
                const label = fieldLabels[selectedVal] || 'DIABAIKAN';
                
                if (selectedVal === 'ignore') {
                    th.innerText = 'DIABAIKAN';
                    th.style.color = 'var(--text-light)';
                    th.style.fontWeight = '500';
                } else {
                    th.innerText = 'Dipetakan: ' + label;
                    th.style.color = 'var(--accent-indigo)';
                    th.style.fontWeight = '800';
                }
            }
        });
    }

    // Jalankan saat halaman siap
    document.addEventListener('DOMContentLoaded', () => {
        updateTableHeaders();
    });

    // Form submit validation
    document.getElementById('importForm').addEventListener('submit', (e) => {
        const selects = document.querySelectorAll('.col-mapping-select');
        let mappedFields = [];
        
        selects.forEach(select => {
            if (select.value !== 'ignore') {
                mappedFields.push(select.value);
            }
        });

        if (!mappedFields.includes('nik')) {
            alert('Kesalahan: Anda wajib memetakan satu kolom sebagai "NIK"!');
            e.preventDefault();
            return false;
        }

        if (!mappedFields.includes('name')) {
            alert('Kesalahan: Anda wajib memetakan satu kolom sebagai "Nama Lengkap"!');
            e.preventDefault();
            return false;
        }
        
        // Cek jika ada duplikasi pemetaan ke field yang sama
        const hasDuplicates = mappedFields.some((val, i) => mappedFields.indexOf(val) !== i);
        if (hasDuplicates) {
            alert('Kesalahan: Tidak boleh memetakan lebih dari satu kolom ke data sistem yang sama (misal: memetakan dua kolom berbeda ke NIK).');
            e.preventDefault();
            return false;
        }
    });
</script>
@endsection
