<?php

namespace App\Http\Controllers;

use App\Models\ApelSession;
use App\Models\Participant;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Show the check-in form.
     */
    public function index(Request $request, $code = null)
    {
        $now      = Carbon::now();
        $todayStr = $now->format('Y-m-d');

        // Find sessions active today (multi-day: date <= today <= end_date)
        $activeSessions = ApelSession::where('date', '<=', $todayStr)
            ->where(function ($q) use ($todayStr) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $todayStr);
            })
            ->get();

        // Filter sessions that are currently open (within time window)
        $openSession = $activeSessions->filter(function ($session) {
            return $session->isOpen();
        })->first();

        // If a code was specified in URL, look for that session
        $urlSession = null;
        if ($code) {
            $urlSession = ApelSession::where('code', strtoupper($code))
                ->where('date', '<=', $todayStr)
                ->where(function ($q) use ($todayStr) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $todayStr);
                })
                ->first();
        }

        $selectedCode = $code ? strtoupper($code) : '';

        return view('checkin', [
            'openSession'  => $openSession,
            'urlSession'   => $urlSession,
            'selectedCode' => $selectedCode,
            'now'          => $now,
        ]);
    }

    /**
     * Submit check-in form.
     */
    public function submit(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'code' => 'required|string|size:5',
            'signature' => 'required|string', // Base64 signature
            'photo' => 'nullable|string', // Optional Base64 selfie
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_name' => 'nullable|string',
        ]);

        $code = strtoupper($request->input('code'));
        $nik = $request->input('nik');

        // 1. Find the participant in master data by NIK, NIP, or Other ID
        $participant = Participant::where('nik', $nik)
            ->orWhere('nip', $nik)
            ->orWhere('other_id', $nik)
            ->first();

        if (!$participant) {
            return back()->withErrors(['nik' => 'NIK / NIP / ID Anda salah dan tidak ditemukan.'])->withInput();
        }

        if ($participant->status !== 'aktif') {
            return back()->withErrors(['nik' => 'Status NIK / NIP / ID Anda saat ini nonaktif. Silakan hubungi admin.'])->withInput();
        }

        $todayStr = Carbon::today()->format('Y-m-d');

        // 2. Find the session by code — supports multi-day sessions
        $session = ApelSession::where('code', $code)
            ->where('date', '<=', $todayStr)
            ->where(function ($q) use ($todayStr) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $todayStr);
            })
            ->first();

        if (!$session) {
            return back()->withErrors(['code' => 'Kode registrasi salah atau masa berlakunya sudah habis.'])->withInput();
        }

        // 3. Verify session time window
        if (!$session->isOpen()) {
            $startTime = Carbon::parse($session->start_time)->format('H:i');
            $endTime = Carbon::parse($session->end_time)->format('H:i');
            return back()->withErrors(['code' => "Sesi apel ({$session->title}) saat ini sudah ditutup. Jam operasional: {$startTime} - {$endTime}."])->withInput();
        }

        // 4. Check if already checked in using the primary key (NIK)
        $exists = Attendance::where('apel_session_id', $session->id)
            ->where('participant_nik', $participant->nik)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nik' => 'Anda sudah melakukan absensi untuk sesi apel ini.'])->withInput();
        }

        // 5. Save attendance
        Attendance::create([
            'apel_session_id' => $session->id,
            'participant_nik' => $participant->nik, // Store actual primary key NIK
            'signature' => $request->input('signature'),
            'photo' => $request->input('photo'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'location_name' => $request->input('location_name'),
            'signed_in_at' => Carbon::now(),
        ]);

        return redirect()->route('apel.success')->with([
            'success_message' => 'Absensi Apel berhasil disimpan!',
            'participant_name' => $participant->name,
            'session_title' => $session->title,
            'time' => Carbon::now()->format('H:i:s'),
        ]);
    }

    /**
     * Show success page.
     */
    public function success()
    {
        if (!session('success_message')) {
            return redirect()->route('apel.index');
        }
        return view('checkin_success');
    }
}
