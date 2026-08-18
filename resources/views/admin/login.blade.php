@extends('layouts.app')

@section('title', 'Admin Login - Asign SMKN 1 Ciamis')

@section('body-class', 'client-layout')

@section('content')
<div class="glass-container">
    <div class="brand-header">
        <div class="brand-logo">
            <i class="fa-solid fa-lock"></i>
        </div>
        <h1 class="brand-title">Admin Dashboard</h1>
        <p class="brand-subtitle">Silakan login untuk mengelola sistem presensi</p>
    </div>

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

    <form action="{{ route('admin.login.submit') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label class="form-label" for="email">Alamat Email</label>
            <input type="email" 
                   name="email" 
                   id="email" 
                   class="form-control" 
                   placeholder="admin@apel.com" 
                   value="{{ old('email') }}" 
                   required 
                   autofocus>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Kata Sandi</label>
            <input type="password" 
                   name="password" 
                   id="password" 
                   class="form-control" 
                   placeholder="••••••••" 
                   required>
        </div>

        <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1rem;">
            <input type="checkbox" name="remember" id="remember" style="width: 16px; height: 16px; cursor: pointer;">
            <label for="remember" style="font-size: 0.85rem; font-weight: 500; color: var(--text-muted); cursor: pointer; user-select: none;">Ingat Saya</label>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">
            <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
        </button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem;">
        <a href="{{ route('apel.index') }}"><i class="fa-solid fa-arrow-left"></i> Kembali ke Form Absensi</a>
    </div>

    <div class="app-footer">
        &copy; {{ date('Y') }} SMKN 1 Ciamis. All rights reserved.
    </div>
</div>
@endsection
