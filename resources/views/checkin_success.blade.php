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

    <div style="background: rgba(255, 255, 255, 0.4); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.25rem; border: 1px solid var(--card-border);">
        <div style="display: grid; grid-template-columns: 100px 1fr; gap: 0.5rem 1rem; font-size: 0.9rem;">
            <div style="color: var(--text-muted); font-weight: 600;">Nama:</div>
            <div style="font-weight: 700; color: var(--text-main);">{{ session('participant_name') }}</div>
            
            <div style="color: var(--text-muted); font-weight: 600;">Sesi:</div>
            <div style="font-weight: 600; color: var(--text-main);">{{ session('session_title') }}</div>
            
            <div style="color: var(--text-muted); font-weight: 600;">Waktu Ttd:</div>
            <div style="font-weight: 600; color: var(--text-main);">{{ session('time') }} WIB</div>
        </div>
    </div>

    @if (session('motivation_quote'))
    <div class="motivation-quote-card" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(168, 85, 247, 0.08) 100%); border: 1.5px solid rgba(99, 102, 241, 0.25); border-radius: var(--radius-md); padding: 1.15rem 1.25rem; margin-bottom: 1.5rem; position: relative; text-align: left; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.05);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.55rem;">
            <div style="display: flex; align-items: center; gap: 0.45rem; color: var(--accent-indigo); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;">
                <i class="fa-solid fa-sparkles" style="color: #f59e0b;"></i>
                <span>Pesan Motivasi Untuk Anda</span>
            </div>
            <i class="fa-solid fa-quote-right" style="color: rgba(99, 102, 241, 0.3); font-size: 1.15rem;"></i>
        </div>
        <p style="font-size: 0.92rem; line-height: 1.55; color: var(--text-main); font-style: italic; margin: 0 0 0.5rem 0; font-weight: 500;">
            “{{ session('motivation_quote') }}”
        </p>
        <div style="display: flex; justify-content: flex-end; align-items: center; font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">
            <span>— {{ session('motivation_author', 'Inspirasi Hari Ini') }}</span>
        </div>
    </div>
    @endif

    <a href="{{ route('apel.index') }}" class="btn btn-primary btn-block">
        <i class="fa-solid fa-rotate-left"></i> Kembali ke Form Presensi
    </a>

    <div class="app-footer">
        &copy; {{ date('Y') }} SMKN 1 Ciamis. All rights reserved.
    </div>
</div>

@if(session('session_code'))
<script>
    // Set a flag in localStorage that this device has checked in for this session
    const sessionCode = "{{ session('session_code') }}";
    if (sessionCode) {
        const today = new Date().toISOString().split('T')[0];
        localStorage.setItem(`attended_${sessionCode.toUpperCase()}_${today}`, 'true');
    }
</script>
@endif

@endsection
