<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;

// Participant Attendance Flow
Route::get('/', function () {
    return redirect()->route('apel.index');
});

Route::get('/api/participant/{nik}', function ($nik) {
    $participant = \App\Models\Participant::where('nik', $nik)->first();
    if ($participant) {
        return response()->json([
            'name' => $participant->name,
            'role' => $participant->role
        ]);
    }
    return response()->json(['message' => 'Not found'], 404);
});

Route::get('/apel/{code?}', [AttendanceController::class, 'index'])->name('apel.index');
Route::post('/apel/submit', [AttendanceController::class, 'submit'])->name('apel.submit');
Route::get('/apel-sukses', [AttendanceController::class, 'success'])->name('apel.success');

// Admin Authentication
Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// Admin Dashboard & Management (Protected)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Sessions
    Route::post('/sessions', [AdminController::class, 'storeSession'])->name('sessions.store');
    Route::delete('/sessions/{id}', [AdminController::class, 'deleteSession'])->name('sessions.delete');
    Route::get('/sessions/{id}', [AdminController::class, 'sessionDetail'])->name('sessions.detail');
    Route::get('/sessions/{id}/export', [AdminController::class, 'exportCSV'])->name('sessions.export');

    // Participants CRUD
    Route::get('/participants', [AdminController::class, 'participants'])->name('participants');
    Route::post('/participants', [AdminController::class, 'storeParticipant'])->name('participants.store');
    Route::put('/participants/{nik}', [AdminController::class, 'updateParticipant'])->name('participants.update');
    Route::delete('/participants/{nik}', [AdminController::class, 'deleteParticipant'])->name('participants.delete');
});
