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
     */
    public function exportPDF($id, Request $request)
    {
        $session     = ApelSession::findOrFail($id);
        $attendances = $this->getFilteredAttendances($id, $request);
        $logoPath    = public_path('icons/logojawabaratheader.png');
        $logoBase64  = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.session_pdf', compact('session', 'attendances', 'logoBase64'))
            ->setPaper('a4', 'portrait');

        $filename = 'Absensi_' . str_replace(' ', '_', $session->title) . '_' . $session->date->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export session to Excel (with filters).
     */
    public function exportExcel($id, Request $request)
    {
        $session     = ApelSession::findOrFail($id);
        $attendances = $this->getFilteredAttendances($id, $request);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Absensi');

        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'DINAS PENDIDIKAN CABANG DINAS PENDIDIKAN WILAYAH XIII');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'SMK NEGERI 1 CIAMIS');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        $sheet->mergeCells('A3:E3');
        $sheet->setCellValue('A3', 'Jl. Jenderal Sudirman No. 269 Tlp. (0265) 771204 – Ciamis 46215');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');

        $sheet->mergeCells('A4:E4');
        $sheet->setCellValue('A4', 'DAFTAR HADIR APEL – ' . strtoupper($session->title));
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');

        $sheet->mergeCells('A5:E5');
        $sheet->setCellValue('A5', 'HARI/TANGGAL: ' . $session->date->translatedFormat('l, d F Y'));
        $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');

        $headers = ['No', 'Nama', 'NIP', 'Jabatan', 'Tanda Tangan'];
        $cols    = ['A', 'B', 'C', 'D', 'E'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . '7', $h);
            $sheet->getStyle($cols[$i] . '7')->getFont()->setBold(true);
            $sheet->getStyle($cols[$i] . '7')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD3D3D3');
        }

        $row = 8;
        foreach ($attendances as $idx => $a) {
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $a->participant->name ?? $a->participant_nik);
            $sheet->setCellValue('C' . $row, $a->participant->nip ?? '-');
            $sheet->setCellValue('D' . $row, $a->participant->jabatan ?? ($a->participant->role ?? '-'));
            $sheet->setCellValue('E' . $row, $idx + 1 . '.');
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(15);

        if ($row > 8) {
            $sheet->getStyle('A7:E' . ($row - 1))->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Absensi_' . str_replace(' ', '_', $session->title) . '_' . $session->date->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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
