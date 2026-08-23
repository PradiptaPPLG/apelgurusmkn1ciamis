<?php

namespace App\Http\Controllers;

use App\Models\ApelLocation;
use App\Models\ApelSession;
use App\Models\Participant;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\UniqueConstraintViolationException;

use App\Services\MotivationalQuoteService;

class AttendanceController extends Controller
{
    /**
     * Calculate distance between two GPS coordinates using Haversine formula in meters.
     */
    private function calculateDistanceMeters($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Show the check-in form.
     */
    public function index(Request $request, $code = null)
    {
        $now      = Carbon::now();
        $todayStr = $now->format('Y-m-d');

        // Find sessions active today (multi-day: date <= today <= end_date)
        $activeSessions = ApelSession::whereDate('date', '<=', $todayStr)
            ->where(function ($q) use ($todayStr) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $todayStr);
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
                ->whereDate('date', '<=', $todayStr)
                ->where(function ($q) use ($todayStr) {
                    $q->whereNull('end_date')->orWhereDate('end_date', '>=', $todayStr);
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
            ->whereDate('date', '<=', $todayStr)
            ->where(function ($q) use ($todayStr) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $todayStr);
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

        // 4. Server-Side Geofence Enforcement (Prevent bypass via Postman/Curl)
        $apelLocation = ApelLocation::getInstance();
        if ($apelLocation->isConfigured()) {
            $lat = $request->input('latitude');
            $lon = $request->input('longitude');

            if (!$lat || !$lon) {
                return back()->withErrors(['latitude' => 'Lokasi GPS perangkat Anda wajib diaktifkan untuk melakukan presensi apel.'])->withInput();
            }

            $distanceMeters = $this->calculateDistanceMeters(
                (float) $lat,
                (float) $lon,
                (float) $apelLocation->latitude,
                (float) $apelLocation->longitude
            );

            if ($distanceMeters > $apelLocation->radius_meter) {
                $roundedDist = round($distanceMeters);
                return back()->withErrors([
                    'latitude' => "Posisi Anda berada di luar radius lokasi apel ({$roundedDist} meter dari titik apel SMKN 1 Ciamis, batas maksimal: {$apelLocation->radius_meter} meter)."
                ])->withInput();
            }
        }

        // 5. Check if already checked in using the primary key (NIK)
        $exists = Attendance::where('apel_session_id', $session->id)
            ->where('participant_nik', $participant->nik)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nik' => 'Anda sudah melakukan absensi untuk sesi apel ini.'])->withInput();
        }

        // 6. Save attendance with race-condition handling
        try {
            Attendance::create([
                'apel_session_id' => $session->id,
                'participant_nik' => $participant->nik,
                'signature' => $request->input('signature'),
                'photo' => $request->input('photo'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'location_name' => $request->input('location_name'),
                'signed_in_at' => Carbon::now(),
            ]);
        } catch (UniqueConstraintViolationException $e) {
            return back()->withErrors(['nik' => 'Anda sudah melakukan absensi untuk sesi apel ini.'])->withInput();
        }

        // 7. Dapatkan kata motivasi unik untuk guru ini pada sesi hari ini
        $motivation = MotivationalQuoteService::getQuoteForAttendance($session, $participant);

        return redirect()->route('apel.success')->with([
            'success_message'   => 'Absensi Apel berhasil disimpan!',
            'participant_name'  => $participant->name,
            'session_title'     => $session->title,
            'time'              => Carbon::now()->format('H:i:s'),
            'motivation_quote'  => $motivation['quote'],
            'motivation_author' => $motivation['author'] ?? 'Inspirasi Hari Ini',
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
