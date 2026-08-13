@extends('layouts.app')

@section('title', 'Absensi Berhasil - SMKN 1 Ciamis')

@section('body-class', 'client-layout')

@section('content')
<div class="glass-container">
    <div class="success-checkmark">
        <i class="fa-solid fa-check"></i>
    </div>
    
    <div class="brand-header" style="margin-bottom: 1.5rem;">
        <h1 class="brand-title" style="color: var(--accent-teal);">Absensi Berhasil!</h1>
        <p class="brand-subtitle">Data kehadiran Anda telah tersimpan dengan aman.</p>
    </div>

    <div style="background: rgba(255, 255, 255, 0.4); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem; border: 1px solid var(--card-border);">
        <div style="display: grid; grid-template-columns: 100px 1fr; gap: 0.5rem 1rem; font-size: 0.9rem;">
            <div style="color: var(--text-muted); font-weight: 600;">Nama:</div>
            <div style="font-weight: 700; color: var(--text-main);">{{ session('participant_name') }}</div>
            
            <div style="color: var(--text-muted); font-weight: 600;">Sesi:</div>
            <div style="font-weight: 600; color: var(--text-main);">{{ session('session_title') }}</div>
            
            <div style="color: var(--text-muted); font-weight: 600;">Waktu Ttd:</div>
            <div style="font-weight: 600; color: var(--text-main);">{{ session('time') }} WIB</div>
        </div>
    </div>

    <a href="{{ route('apel.index') }}" class="btn btn-primary btn-block">
        <i class="fa-solid fa-rotate-left"></i> Kembali ke Form Presensi
    </a>

    <div class="app-footer">
        &copy; {{ date('Y') }} SMKN 1 Ciamis. All rights reserved.
    </div>
</div>
@endsection
