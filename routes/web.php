<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KepsekController;

// ============================================================
// ROUTE LOGIN (DEFAULT LARAVEL)
// ============================================================
// Laravel auth middleware expects a route named 'login'
Route::get('/login', [AdminController::class, 'loginForm'])->name('login');

// Participant Attendance Flow
Route::get('/', function () {
    return redirect()->route('apel.index');
});

Route::get('/apel/{code?}', [AttendanceController::class, 'index'])->name('apel.index');
Route::post('/apel/submit', [AttendanceController::class, 'submit'])
    ->middleware('throttle:300,1')
    ->name('apel.submit');
Route::get('/apel-sukses', [AttendanceController::class, 'success'])->name('apel.success');

// Public API: participant lookup (300 req/min allows full school Wi-Fi rush hour while stopping bots)
Route::get('/api/participant/{nik}', function ($nik) {
    $participant = \App\Models\Participant::where('nik', $nik)
        ->orWhere('nip', $nik)
        ->orWhere('other_id', $nik)
        ->first();
    if ($participant) {
        return response()->json([
            'name' => $participant->name,
            'role' => $participant->role
        ]);
    }
    return response()->json(['message' => 'Not found'], 404);
})->middleware('throttle:300,1');

// Public API: get apel geofence location (for client-side validation)
Route::get('/api/apel-location', [AdminController::class, 'getApelLocation'])
    ->middleware('throttle:300,1')
    ->name('api.apel.location');

// Admin Authentication (alias untuk /login sudah di atas - Rate limited to 5 attempts per minute)
Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('admin.login.submit');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// Admin Dashboard & Management (Protected – Admin only for write routes)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->middleware('admin.only')->name('dashboard');

    // Sessions (Kelola Sesi & Riwayat)
    Route::get('/sessions', [AdminController::class, 'sessions'])->middleware('admin.only')->name('sessions.index');
    Route::post('/sessions', [AdminController::class, 'storeSession'])->middleware('admin.only')->name('sessions.store');
    Route::delete('/sessions/{id}', [AdminController::class, 'deleteSession'])->middleware('admin.only')->name('sessions.delete');
    Route::get('/sessions/{id}', [AdminController::class, 'sessionDetail'])->middleware('admin.only')->name('sessions.detail');

    // Export routes (PDF, Excel, Preview)
    Route::get('/sessions/{id}/export-pdf',   [AdminController::class, 'exportPDF'])->middleware('admin.only')->name('sessions.export.pdf');
    Route::get('/sessions/{id}/export-excel', [AdminController::class, 'exportExcel'])->middleware('admin.only')->name('sessions.export.excel');
    Route::get('/sessions/{id}/preview',      [AdminController::class, 'previewHTML'])->middleware('admin.only')->name('sessions.preview');

    // Participants CRUD (admin only)
    Route::get('/participants', [AdminController::class, 'participants'])->middleware('admin.only')->name('participants');
    Route::post('/participants', [AdminController::class, 'storeParticipant'])->middleware('admin.only')->name('participants.store');
    Route::put('/participants/{nik}', [AdminController::class, 'updateParticipant'])->middleware('admin.only')->name('participants.update');
    Route::delete('/participants/{nik}', [AdminController::class, 'deleteParticipant'])->middleware('admin.only')->name('participants.delete');
    Route::post('/participants/import-preview', [AdminController::class, 'importPreview'])->middleware('admin.only')->name('participants.import.preview');
    Route::post('/participants/import', [AdminController::class, 'import'])->middleware('admin.only')->name('participants.import');

    // Apel Location (Geofence) – admin only
    Route::get('/lokasi-apel', [AdminController::class, 'apelLocation'])->middleware('admin.only')->name('apel.location');
    Route::post('/lokasi-apel', [AdminController::class, 'saveApelLocation'])->middleware('admin.only')->name('apel.location.save');

    // Monthly Recap (Admin)
    Route::get('/rekap-bulanan',              [AdminController::class, 'rekapBulanan'])->middleware('admin.only')->name('rekap.index');
    Route::get('/rekap-bulanan/export-excel', [AdminController::class, 'exportRekapExcel'])->middleware('admin.only')->name('rekap.export.excel');
    Route::get('/rekap-bulanan/export-pdf',   [AdminController::class, 'exportRekapPDF'])->middleware('admin.only')->name('rekap.export.pdf');
    Route::get('/rekap-bulanan/preview',      [AdminController::class, 'previewRekapHTML'])->middleware('admin.only')->name('rekap.preview');
});

// Kepala Sekolah Routes (Protected – read-only)
Route::middleware(['auth'])->prefix('kepsek')->name('kepsek.')->group(function () {
    Route::get('/dashboard', [KepsekController::class, 'dashboard'])->name('dashboard');
    Route::get('/sessions', [KepsekController::class, 'sessions'])->name('sessions.index');
    Route::get('/participants', [KepsekController::class, 'participants'])->name('participants');
    Route::get('/sessions/{id}', [KepsekController::class, 'sessionDetail'])->name('sessions.detail');
    Route::get('/sessions/{id}/export-pdf',   [KepsekController::class, 'exportPDF'])->name('sessions.export.pdf');
    Route::get('/sessions/{id}/export-excel', [KepsekController::class, 'exportExcel'])->name('sessions.export.excel');
    Route::get('/sessions/{id}/preview',      [KepsekController::class, 'previewHTML'])->name('sessions.preview');

    // Monthly Recap (Kepsek)
    Route::get('/rekap-bulanan',              [KepsekController::class, 'rekapBulanan'])->name('rekap.index');
    Route::get('/rekap-bulanan/export-excel', [KepsekController::class, 'exportRekapExcel'])->name('rekap.export.excel');
    Route::get('/rekap-bulanan/export-pdf',   [KepsekController::class, 'exportRekapPDF'])->name('rekap.export.pdf');
    Route::get('/rekap-bulanan/preview',      [KepsekController::class, 'previewRekapHTML'])->name('rekap.preview');
});