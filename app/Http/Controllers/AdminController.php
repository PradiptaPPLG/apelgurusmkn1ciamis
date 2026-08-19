<?php

namespace App\Http\Controllers;

use App\Models\ApelLocation;
use App\Models\ApelSession;
use App\Models\Participant;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Show login form.
     */
    public function loginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            // Redirect based on role
            if (Auth::user()->isKepsek()) {
                return redirect()->intended(route('kepsek.dashboard'));
            }
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    /**
     * Show admin dashboard (Sessions list & stats).
     */
    public function dashboard()
    {
        $todayStr = Carbon::today()->format('Y-m-d');

        $totalParticipants  = Participant::count();
        $activeParticipants = Participant::where('status', 'aktif')->count();
        $totalSessions      = ApelSession::count();
        $todayAttendances   = Attendance::whereDate('signed_in_at', $todayStr)->count();
        $apelLocation       = ApelLocation::getInstance();

        // Get sessions, newest first
        $sessions = ApelSession::withCount('attendances')
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        return view('admin.dashboard', compact(
            'totalParticipants',
            'activeParticipants',
            'totalSessions',
            'todayAttendances',
            'sessions',
            'apelLocation'
        ));
    }

    /**
     * Store new session.
     */
    public function storeSession(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'date'       => 'required|date',
            'type'       => 'required|in:pagi,sore',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
            'valid_days' => 'required|integer|min:1|max:7',
        ]);

        $validDays = (int) $request->valid_days;
        $startDate = Carbon::parse($request->date);
        $endDate   = $startDate->copy()->addDays($validDays - 1);

        // Auto generate unique 5-character code
        do {
            $code  = '';
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for ($i = 0; $i < 5; $i++) {
                $code .= $chars[rand(0, strlen($chars) - 1)];
            }
        } while (ApelSession::where('code', $code)->exists());

        ApelSession::create([
            'title'      => $request->title,
            'date'       => $request->date,
            'type'       => $request->type,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'code'       => $code,
            'valid_days' => $validDays,
            'end_date'   => $endDate->format('Y-m-d'),
        ]);

        $rangeLabel = $validDays > 1
            ? ' (berlaku ' . $startDate->format('d M') . ' – ' . $endDate->format('d M Y') . ')'
            : '';

        return redirect()->route('admin.dashboard')->with('success', 'Sesi Apel baru berhasil dibuat dengan Kode: ' . $code . $rangeLabel);
    }

    /**
     * Delete session.
     */
    public function deleteSession($id)
    {
        $session = ApelSession::findOrFail($id);
        $session->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Sesi Apel berhasil dihapus.');
    }

    /**
     * List all participants.
     */
    public function participants(Request $request)
    {
        $query = Participant::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('other_id', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $participants = $query->orderBy('name', 'asc')->paginate(15);
        $apelLocation = ApelLocation::getInstance();

        return view('admin.participants', compact('participants', 'apelLocation'));
    }

    /**
     * Store new participant.
     */
    public function storeParticipant(Request $request)
    {
        $request->validate([
            'nik'               => 'required|string|unique:participants,nik',
            'nip'               => 'nullable|string|unique:participants,nip',
            'other_id'          => 'nullable|string|unique:participants,other_id',
            'name'              => 'required|string|max:255',
            'jabatan'           => 'nullable|string|max:255',
            'jenis_kepegawaian' => 'nullable|in:asn,pns,p3k,honorer,mahasiswa',
            'role'              => 'required|in:Guru,TU,PPL,PPG,Wali Kelas',
            'status'            => 'required|in:aktif,nonaktif',
        ]);

        Participant::create($request->only(['nik', 'nip', 'other_id', 'name', 'jabatan', 'jenis_kepegawaian', 'role', 'status']));

        return redirect()->route('admin.participants')->with('success', 'Data Guru/Peserta berhasil ditambahkan.');
    }

    /**
     * Update participant.
     */
    public function updateParticipant(Request $request, $nik)
    {
        $participant = Participant::findOrFail($nik);

        $request->validate([
            'nik'               => 'required|string|unique:participants,nik,' . $nik . ',nik',
            'nip'               => 'nullable|string|unique:participants,nip,' . $nik . ',nik',
            'other_id'          => 'nullable|string|unique:participants,other_id,' . $nik . ',nik',
            'name'              => 'required|string|max:255',
            'jabatan'           => 'nullable|string|max:255',
            'jenis_kepegawaian' => 'nullable|in:asn,pns,p3k,honorer,mahasiswa',
            'role'              => 'required|in:Guru,TU,PPL,PPG,Wali Kelas',
            'status'            => 'required|in:aktif,nonaktif',
        ]);

        $participant->update($request->only(['nik', 'nip', 'other_id', 'name', 'jabatan', 'jenis_kepegawaian', 'role', 'status']));

        return redirect()->route('admin.participants')->with('success', 'Data Guru/Peserta berhasil diperbarui.');
    }

    /**
     * Delete participant.
     */
    public function deleteParticipant($nik)
    {
        $participant = Participant::findOrFail($nik);
        $participant->delete();

        return redirect()->route('admin.participants')->with('success', 'Data Guru/Peserta berhasil dihapus.');
    }

    /**
     * View session attendances.
     */
    public function sessionDetail($id, Request $request)
    {
        $session = ApelSession::findOrFail($id);

        $query = Attendance::with('participant')->where('apel_session_id', $id);

        // Search by participant name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('participant', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        // Filter by jabatan (role)
        if ($request->filled('jabatan')) {
            $query->whereHas('participant', fn ($q) => $q->where('role', $request->jabatan));
        }

        // Filter by date range on signed_in_at
        if ($request->filled('date_from')) {
            $query->whereDate('signed_in_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('signed_in_at', '<=', $request->date_to);
        }

        $attendances  = $query->orderBy('signed_in_at', 'asc')->get();
        $apelLocation = ApelLocation::getInstance();

        // Collect NIKs of participants who already checked in (unfiltered for absent list)
        $allCheckedInNiks = Attendance::where('apel_session_id', $id)->pluck('participant_nik')->toArray();

        $absentParticipants = Participant::where('status', 'aktif')
            ->whereNotIn('nik', $allCheckedInNiks)
            ->orderBy('name')
            ->get();

        return view('admin.session_detail', compact(
            'session', 'attendances', 'apelLocation', 'absentParticipants'
        ));
    }

    /**
     * Export attendance list to PDF (DomPDF).
     * Note: Requires ext-gd for logo rendering. Falls back to text-only kop if GD unavailable.
     */
    public function exportPDF($id, Request $request)
    {
        $session     = ApelSession::findOrFail($id);
        $attendances = $this->getFilteredAttendances($id, $request);

        // Only embed logo if GD extension is available (DomPDF requires it for image rendering)
        $logoBase64 = null;
        if (extension_loaded('gd')) {
            $logoPath = public_path('icons/logoadmin.png');
            if (file_exists($logoPath)) {
                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.session_pdf', compact('session', 'attendances', 'logoBase64'))
            ->setPaper('a4', 'portrait');

        $filename = 'Absensi_' . str_replace(' ', '_', $session->title) . '_' . $session->date->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export attendance list to Excel (PhpSpreadsheet) — matches school kop format.
     */
    public function exportExcel($id, Request $request)
    {
        $session       = ApelSession::findOrFail($id);
        $attendances   = $this->getFilteredAttendances($id, $request);
        $jabatanFilter = $request->get('jabatan', '');

        $spreadsheet = \App\Services\AttendanceExporter::buildExcel($session, $attendances, $jabatanFilter);
        $writer      = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename    = 'Absensi_' . str_replace(' ', '_', $session->title) . '_' . $session->date->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Preview attendance as HTML in browser (no download, no GD required).
     * Browser handles image rendering — safe to use even without ext-gd.
     */
    public function previewHTML($id, Request $request)
    {
        $session     = ApelSession::findOrFail($id);
        $attendances = $this->getFilteredAttendances($id, $request);

        // base64 for browser — no GD needed, browser decodes the image
        $logoBase64 = null;
        $logoPath   = public_path('icons/logoadmin.png');
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        return view('exports.session_pdf', compact('session', 'attendances', 'logoBase64'))->with('isPreview', true);
    }



    /**
     * Helper: get attendances with filters applied.
     */
    private function getFilteredAttendances($id, Request $request)
    {
        $query = Attendance::with('participant')->where('apel_session_id', $id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('participant', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }
        if ($request->filled('jabatan')) {
            $query->whereHas('participant', fn ($q) => $q->where('role', $request->jabatan));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('signed_in_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('signed_in_at', '<=', $request->date_to);
        }

        return $query->orderBy('signed_in_at', 'asc')->get();
    }

    /**
     * Show apel location settings page.
     */
    public function apelLocation()
    {
        $apelLocation = ApelLocation::getInstance();
        return view('admin.apel_location', compact('apelLocation'));
    }

    /**
     * Save apel location.
     */
    public function saveApelLocation(Request $request)
    {
        $request->validate([
            'latitude'     => 'required|numeric|between:-90,90',
            'longitude'    => 'required|numeric|between:-180,180',
            'label'        => 'nullable|string|max:255',
            'radius_meter' => 'required|integer|between:5,50',
        ]);

        $location = ApelLocation::getInstance();
        $location->update([
            'latitude'     => $request->latitude,
            'longitude'    => $request->longitude,
            'radius_meter' => $request->radius_meter,
            'label'        => $request->label ?? 'Titik Apel',
            'updated_by'   => Auth::user()->name,
        ]);

        return redirect()->route('admin.apel.location')->with('success', 'Titik apel berhasil disimpan! Koordinat: ' . $request->latitude . ', ' . $request->longitude . ' — Radius: ' . $request->radius_meter . 'm');
    }

    /**
     * Public API: return apel geofence location as JSON (used by checkin form).
     */
    public function getApelLocation()
    {
        $loc = ApelLocation::getInstance();

        if (!$loc->isConfigured()) {
            return response()->json(['configured' => false]);
        }

        return response()->json([
            'configured'   => true,
            'latitude'     => $loc->latitude,
            'longitude'    => $loc->longitude,
            'radius_meter' => $loc->radius_meter,
            'label'        => $loc->label,
        ]);
    }
}
