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
        $now = Carbon::now();
        $todayStr = $now->format('Y-m-d');
        
        // Find any session active today
        $activeSessions = ApelSession::where('date', $todayStr)->get();
        
        // Filter sessions that are currently open
        $openSession = $activeSessions->filter(function($session) {
            return $session->isOpen();
        })->first();

        // If a code was specified in URL, look for that session
        $urlSession = null;
        if ($code) {
            $urlSession = ApelSession::where('code', strtoupper($code))
                ->where('date', $todayStr)
                ->first();
        }

        $selectedCode = $code ? strtoupper($code) : '';

        return view('checkin', [
            'openSession' => $openSession,
            'urlSession' => $urlSession,
            'selectedCode' => $selectedCode,
            'now' => $now,
        ]);
    }

    /**
     * Submit check-in form.
     */
    public function submit(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'name' => 'required|string|max:255',
            'role' => 'required|in:Guru,TU,PPL,PPG',
            'code' => 'required|string|size:5',
            'signature' => 'required|string', // Base64 signature
            'photo' => 'nullable|string', // Optional Base64 selfie
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $code = strtoupper($request->input('code'));
        $nik = $request->input('nik');

        // 1. Find or Create the participant in master data
        $participant = Participant::where('nik', $nik)->first();
        if ($participant) {
            if ($participant->status !== 'aktif') {
                return back()->withErrors(['nik' => 'Status NIK Anda saat ini nonaktif. Silakan hubungi admin.'])->withInput();
            }
            // Update name and role in case of updates/corrections
            $participant->update([
                'name' => $request->input('name'),
                'role' => $request->input('role'),
            ]);
        } else {
            // Self-register to master data on-the-fly
            $participant = Participant::create([
                'nik' => $nik,
                'name' => $request->input('name'),
                'role' => $request->input('role'),
                'status' => 'aktif',
            ]);
        }

        // 2. Find the session by code
        $session = ApelSession::where('code', $code)
            ->where('date', Carbon::today()->format('Y-m-d'))
            ->first();

        if (!$session) {
            return back()->withErrors(['code' => 'Kode registrasi salah atau bukan untuk hari ini.'])->withInput();
        }

        // 3. Verify session time window
        if (!$session->isOpen()) {
            $startTime = Carbon::parse($session->start_time)->format('H:i');
            $endTime = Carbon::parse($session->end_time)->format('H:i');
            return back()->withErrors(['code' => "Sesi apel ({$session->title}) saat ini sudah ditutup. Jam operasional: {$startTime} - {$endTime}."])->withInput();
        }

        // 4. Check if already checked in
        $exists = Attendance::where('apel_session_id', $session->id)
            ->where('participant_nik', $nik)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nik' => 'Anda sudah melakukan absensi untuk sesi apel ini.'])->withInput();
        }

        // 5. Save attendance
        Attendance::create([
            'apel_session_id' => $session->id,
            'participant_nik' => $nik,
            'signature' => $request->input('signature'),
            'photo' => $request->input('photo'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
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
