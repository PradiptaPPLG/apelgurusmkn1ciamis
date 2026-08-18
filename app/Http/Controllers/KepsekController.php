<?php

namespace App\Http\Controllers;

use App\Models\ApelLocation;
use App\Models\ApelSession;
use App\Models\Participant;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KepsekController extends Controller
{
    /**
     * Kepala Sekolah dashboard – read-only sessions list + stats.
     */
    public function dashboard()
    {
        $todayStr = Carbon::today()->format('Y-m-d');

        $totalParticipants  = Participant::where('status', 'aktif')->count();
        $totalSessions      = ApelSession::count();
        $todayAttendances   = Attendance::whereDate('signed_in_at', $todayStr)->count();

        $sessions = ApelSession::withCount('attendances')
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        return view('kepsek.dashboard', compact(
            'totalParticipants', 'totalSessions', 'todayAttendances', 'sessions'
        ));
    }

    /**
     * Kepala Sekolah participants list – read-only.
     */
    public function participants(Request $request)
    {
        $query = Participant::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $participants = $query->orderBy('name', 'asc')->paginate(20);
        return view('kepsek.participants', compact('participants'));
    }

    /**
     * Kepala Sekolah session detail – with filters.
     */
    public function sessionDetail($id, Request $request)
    {
        $session = ApelSession::findOrFail($id);

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

        $attendances = $query->orderBy('signed_in_at', 'asc')->get();

        $allCheckedInNiks   = Attendance::where('apel_session_id', $id)->pluck('participant_nik')->toArray();
        $absentParticipants = Participant::where('status', 'aktif')
            ->whereNotIn('nik', $allCheckedInNiks)
            ->orderBy('name')
            ->get();

        return view('kepsek.session_detail', compact('session', 'attendances', 'absentParticipants'));
    }

    /**
     * Export session to PDF (with filters).
     * Note: Requires ext-gd for logo rendering. Falls back to text-only kop if GD unavailable.
     */
    public function exportPDF($id, Request $request)
    {
        $session     = ApelSession::findOrFail($id);
        $attendances = $this->getFilteredAttendances($id, $request);

        // Only embed logo if GD extension is available (DomPDF requires it for image rendering)
        $logoBase64 = null;
        if (extension_loaded('gd')) {
            $logoPath = public_path('icons/logojawabaratheader.png');
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
     * Export session to Excel (with filters) — matches school kop format.
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
     */
    public function previewHTML($id, Request $request)
    {
        $session     = ApelSession::findOrFail($id);
        $attendances = $this->getFilteredAttendances($id, $request);

        // base64 for browser — no GD needed, browser decodes the image
        $logoBase64 = null;
        $logoPath   = public_path('icons/logojawabaratheader.png');
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        return view('exports.session_pdf', compact('session', 'attendances', 'logoBase64'))->with('isPreview', true);
    }

    private function getFilteredAttendances($id, Request $request)
    {
        $query = Attendance::with('participant')->where('apel_session_id', $id);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('participant', fn ($q) => $q->where('name', 'like', "%{$s}%"));
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
}
