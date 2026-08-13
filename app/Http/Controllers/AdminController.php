<?php

namespace App\Http\Controllers;

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
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
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
        
        $totalParticipants = Participant::count();
        $activeParticipants = Participant::where('status', 'aktif')->count();
        $totalSessions = ApelSession::count();
        $todayAttendances = Attendance::whereDate('signed_in_at', $todayStr)->count();

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
            'sessions'
        ));
    }

    /**
     * Store new session.
     */
    public function storeSession(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:pagi,sore',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        // Auto generate unique 5-character code
        do {
            $code = '';
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for ($i = 0; $i < 5; $i++) {
                $code .= $chars[rand(0, strlen($chars) - 1)];
            }
        } while (ApelSession::where('code', $code)->exists());

        ApelSession::create([
            'title' => $request->title,
            'date' => $request->date,
            'type' => $request->type,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'code' => $code,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Sesi Apel baru berhasil dibuat dengan Kode: ' . $code);
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
            $query->where(function($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
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

        return view('admin.participants', compact('participants'));
    }

    /**
     * Store new participant.
     */
    public function storeParticipant(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|unique:participants,nik',
            'name' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'jenis_kepegawaian' => 'nullable|in:asn,pns,p3k,honorer,mahasiswa',
            'role' => 'required|in:Guru,TU,PPL,PPG',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        Participant::create($request->only(['nik', 'name', 'jabatan', 'jenis_kepegawaian', 'role', 'status']));

        return redirect()->route('admin.participants')->with('success', 'Data Guru/Peserta berhasil ditambahkan.');
    }

    /**
     * Update participant.
     */
    public function updateParticipant(Request $request, $nik)
    {
        $participant = Participant::findOrFail($nik);

        $request->validate([
            'nik' => 'required|string|unique:participants,nik,' . $nik . ',nik',
            'name' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'jenis_kepegawaian' => 'nullable|in:asn,pns,p3k,honorer,mahasiswa',
            'role' => 'required|in:Guru,TU,PPL,PPG',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $participant->update($request->only(['nik', 'name', 'jabatan', 'jenis_kepegawaian', 'role', 'status']));

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
    public function sessionDetail($id)
    {
        $session = ApelSession::findOrFail($id);
        $attendances = Attendance::with('participant')
            ->where('apel_session_id', $id)
            ->orderBy('signed_in_at', 'asc')
            ->get();

        return view('admin.session_detail', compact('session', 'attendances'));
    }

    /**
     * Export attendance list to CSV.
     */
    public function exportCSV($id)
    {
        $session = ApelSession::findOrFail($id);
        $attendances = Attendance::with('participant')
            ->where('apel_session_id', $id)
            ->orderBy('signed_in_at', 'asc')
            ->get();

        $filename = "Absensi_" . str_replace(' ', '_', $session->title) . "_" . $session->date->format('Y-m-d') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['No', 'NIK', 'Nama', 'Jabatan', 'Jenis Kepegawaian', 'Peran', 'Waktu Hadir', 'Latitude', 'Longitude'];

        $callback = function() use($attendances, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper Excel compatibility in Indonesian/Windows
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, $columns, ';'); // Use semicolon as separator for Indonesian Excel regional settings compatibility

            foreach ($attendances as $idx => $attendance) {
                $row = [
                    $idx + 1,
                    $attendance->participant_nik,
                    $attendance->participant->name ?? 'N/A',
                    $attendance->participant->jabatan ?? 'N/A',
                    $attendance->participant->jenis_kepegawaian ?? 'N/A',
                    $attendance->participant->role ?? 'N/A',
                    $attendance->signed_in_at->format('Y-m-d H:i:s'),
                    $attendance->latitude ?? 'N/A',
                    $attendance->longitude ?? 'N/A',
                ];

                fputcsv($file, $row, ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
