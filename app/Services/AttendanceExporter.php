<?php

namespace App\Services;

use App\Models\ApelSession;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class AttendanceExporter
{
    // Kepala Sekolah info (hardcoded per school data)
    const KEPSEK_NAME  = 'H. Cepy Wahyudin, A.Md., S.Kom., M.Kom.';
    const KEPSEK_GOLOK = 'Penata Tk. I/III/d';
    const KEPSEK_NIP   = 'NIP. 198408252010011010';

    /**
     * Build a formatted Excel spreadsheet matching the school's official attendance format.
     *
     * Template selection:
     *  - PPG / PLP / PPL / GEMA UPI → columns: No | Nama | NIM | Program Studi | Tanda Tangan
     *  - Guru / Wali Kelas / TU     → columns: No | Nama | NIP | Jabatan       | Tanda Tangan
     */
    public static function buildExcel(
        ApelSession $session,
        Collection  $attendances,
        string      $jabatanFilter = ''
    ): Spreadsheet {

        // ── Choose template ───────────────────────────────────────────────────
        // PPG / PLP / PPL / GEMA UPI → NIM + Program Studi columns
        // Guru / Wali Kelas / TU / other → NIP + Jabatan columns
        $isPPG = in_array(strtolower($jabatanFilter), ['ppl', 'ppg', 'plp', 'gema upi']);

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Dynamic sheet tab name and title based on filter
        if (strtolower($jabatanFilter) === 'wali kelas') {
            $sheetTabName = 'Wali Kelas';
            $titleText    = 'DAFTAR HADIR APEL WALI KELAS';
        } elseif ($isPPG) {
            $sheetTabName = strtoupper($jabatanFilter) ?: 'PPG/PLP/PPL';
            $titleText    = 'DAFTAR HADIR APEL ' . strtoupper($jabatanFilter ?: 'PPG/PLP/PPL');
        } else {
            $sheetTabName = 'Guru' . ($jabatanFilter ? ' ' . ucwords(strtolower($jabatanFilter)) : '');
            $titleText    = 'DAFTAR HADIR APEL GURU' . ($jabatanFilter ? ' ' . strtoupper($jabatanFilter) : '');
        }

        $sheet->setTitle($sheetTabName);

        // ── Column widths ─────────────────────────────────────────────────────
        // Column A is kept narrow (No column); logo is a floating image so it
        // visually overlaps into B area without altering column widths.
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(38);
        $sheet->getColumnDimension('C')->setWidth(24);
        $sheet->getColumnDimension('D')->setWidth(32);
        $sheet->getColumnDimension('E')->setWidth(18);

        // ── KOP SEKOLAH — rows 1-7 ───────────────────────────────────────────
        // Text merges B:E per row so the logo in A1 sits separately on the left.
        $kopRows = [
            1 => ['PEMERINTAH DAERAH PROVINSI JAWA BARAT',                                                      11, true,  20],
            2 => ['DINAS PENDIDIKAN',                                                                            11, true,  18],
            3 => ['CABANG DINAS PENDIDIKAN WILAYAH XIII',                                                        11, true,  18],
            4 => ['SMK NEGERI 1 CIAMIS',                                                                         14, true,  24],
            5 => ['Jalan : Jenderal Sudirman Nomor : 269 Tlp. (0265) 771204',                                     9, false, 14],
            6 => ['Faksimile : (0265) 771204/777719   Website : www.smkn1ciamis.sch.id   E-mail : surat@smkn1cms.net', 8, false, 13],
            7 => ['Ciamis – 46215',                                                                               9, false, 14],
        ];

        // Merge A1:A7 for logo area (vertical centering of logo cell)
        $sheet->mergeCells('A1:A7');
        $sheet->getStyle('A1')->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach ($kopRows as $row => [$text, $size, $bold, $height]) {
            $sheet->mergeCells("B{$row}:E{$row}");
            $sheet->setCellValue("B{$row}", $text);

            $style = $sheet->getStyle("B{$row}");
            $style->getFont()->setSize($size)->setBold($bold)->setName('Times New Roman');
            $style->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension($row)->setRowHeight($height);
        }

        // Thick double-line border below row 7
        $sheet->getStyle('A7:E7')->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_MEDIUM);

        // ── Logo — floating Drawing (no GD required for PNG embedding) ────────
        // getimagesize() is a PHP built-in that does NOT need GD — it reads
        // the image header directly. We call setPath() first (triggers
        // getimagesize to get natural 96×101 dimensions), then setHeight() only
        // to scale proportionally. Avoids the stretching caused by setting both.
        $logoPath = public_path('icons/logoadmin.png');
        if (file_exists($logoPath) && is_readable($logoPath)) {
            try {
                $drawing = new Drawing();
                $drawing->setName('Logo SMKN 1 Ciamis');
                $drawing->setPath($logoPath);   // reads 96×101 via getimagesize()
                $drawing->setHeight(90);        // scale to 90px; width auto ~86px (proportional)
                $drawing->setCoordinates('A1');
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);
            } catch (\Throwable $e) {
                // Silently skip if image embedding fails on this server
            }
        }

        // ── Empty separator row 8 ─────────────────────────────────────────────
        $sheet->getRowDimension(8)->setRowHeight(8);

        // ── Title row 9 ───────────────────────────────────────────────────────
        $sheet->mergeCells('A9:E9');
        $sheet->setCellValue('A9', $titleText);
        $s9 = $sheet->getStyle('A9');
        $s9->getFont()->setBold(true)->setSize(12)->setName('Times New Roman')
            ->setUnderline(Font::UNDERLINE_SINGLE);
        $s9->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(9)->setRowHeight(20);

        // ── HARI/TANGGAL row 10 ───────────────────────────────────────────────
        $sheet->mergeCells('A10:E10');
        $sheet->setCellValue('A10', 'HARI/TANGGAL : ' . self::formatDateId($session->date));
        $s10 = $sheet->getStyle('A10');
        $s10->getFont()->setBold(true)->setSize(11)->setName('Times New Roman');
        $s10->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(10)->setRowHeight(18);

        // ── Empty gap row 11 ──────────────────────────────────────────────────
        $sheet->getRowDimension(11)->setRowHeight(6);

        // ── Table header row 12 ───────────────────────────────────────────────
        $col3  = $isPPG ? 'NIM'           : 'NIP';
        $col4  = $isPPG ? 'Program Studi' : 'Jabatan';
        $hdrs  = ['No', 'Nama', $col3, $col4, 'Tanda Tangan'];
        $cols  = ['A',  'B',    'C',   'D',    'E'          ];

        foreach ($hdrs as $i => $label) {
            $cell = $cols[$i] . '12';
            $sheet->setCellValue($cell, $label);
            $st = $sheet->getStyle($cell);
            $st->getFont()->setBold(true)->setSize(10)->setName('Times New Roman');
            $st->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            $st->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD3D3D3');
            $st->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        $sheet->getRowDimension(12)->setRowHeight(22);

        // ── Data rows from row 13 ─────────────────────────────────────────────
        $dataRow = 13;
        $total   = $attendances->count();

        foreach ($attendances as $idx => $a) {
            $p = $a->participant;

            $sheet->setCellValue("A{$dataRow}", $idx + 1);
            $sheet->setCellValue("B{$dataRow}", $p->name ?? $a->participant_nik);

            if ($isPPG) {
                // PPG / PLP / PPL / GEMA UPI: NIM (from other_id) + Program Studi (from jabatan)
                $nim      = $p->other_id ?? ($p->nip ?? $a->participant_nik);
                $progStud = $p->jabatan  ?? ($p->role ?? '-');
                $sheet->setCellValue("C{$dataRow}", $nim);
                $sheet->setCellValue("D{$dataRow}", $progStud);
            } else {
                // Guru / Wali Kelas / TU: NIP + Jabatan
                $nip     = $p->nip     ?? ($p->other_id ?? '-');
                $jabatan = $p->jabatan ?? ($p->role      ?? '-');
                $sheet->setCellValue("C{$dataRow}", $nip);
                $sheet->setCellValue("D{$dataRow}", $jabatan);
            }

            // Tanda Tangan — leave EMPTY per user request ("kosongin aja")
            $sheet->setCellValue("E{$dataRow}", '');

            // Cell styles
            $sheet->getStyle("A{$dataRow}:E{$dataRow}")->getBorders()
                ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("A{$dataRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$dataRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension($dataRow)->setRowHeight(20);

            $dataRow++;
        }

        // ── Extra empty rows for manual fill (min 5 blank rows beyond data) ───
        $minEnd = max($dataRow, 13 + max(5, $total));
        while ($dataRow <= $minEnd) {
            $sheet->getStyle("A{$dataRow}:E{$dataRow}")->getBorders()
                ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getRowDimension($dataRow)->setRowHeight(20);
            $dataRow++;
        }

        // ── Signature block — right-aligned, matches reference foto ke-4 ──────
        $sigRow = $dataRow + 1;  // 1 blank row gap

        // "Ciamis, DD Bulan YYYY"
        $sheet->mergeCells("C{$sigRow}:E{$sigRow}");
        $sheet->setCellValue("C{$sigRow}", 'Ciamis, ' . self::formatDateSimpleId(Carbon::now()));
        $sheet->getStyle("C{$sigRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C{$sigRow}")->getFont()->setName('Times New Roman')->setSize(10);

        // "Kepala Sekolah,"
        $sigRow++;
        $sheet->mergeCells("C{$sigRow}:E{$sigRow}");
        $sheet->setCellValue("C{$sigRow}", 'Kepala Sekolah,');
        $sheet->getStyle("C{$sigRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C{$sigRow}")->getFont()->setName('Times New Roman')->setSize(10);

        // 4 blank rows for actual signature space
        $sigRow += 4;

        // Kepala Sekolah name (bold + underline)
        $sheet->mergeCells("C{$sigRow}:E{$sigRow}");
        $sheet->setCellValue("C{$sigRow}", self::KEPSEK_NAME);
        $nsStyle = $sheet->getStyle("C{$sigRow}");
        $nsStyle->getFont()->setBold(true)->setUnderline(Font::UNDERLINE_SINGLE)
            ->setName('Times New Roman')->setSize(10);
        $nsStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Pangkat/Golongan
        $sigRow++;
        $sheet->mergeCells("C{$sigRow}:E{$sigRow}");
        $sheet->setCellValue("C{$sigRow}", self::KEPSEK_GOLOK);
        $sheet->getStyle("C{$sigRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C{$sigRow}")->getFont()->setName('Times New Roman')->setSize(10);

        // NIP
        $sigRow++;
        $sheet->mergeCells("C{$sigRow}:E{$sigRow}");
        $sheet->setCellValue("C{$sigRow}", self::KEPSEK_NIP);
        $sheet->getStyle("C{$sigRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C{$sigRow}")->getFont()->setName('Times New Roman')->setSize(10);

        // Set default font for the spreadsheet as Times New Roman (official document style)
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(10);

        return $spreadsheet;
    }

    /**
     * Format a Carbon date in Indonesian long format:
     * e.g. "Senin, 18 Agustus 2026"
     */
    public static function formatDateId($date): string
    {
        $days   = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];
        $d = Carbon::parse($date);
        return $days[$d->dayOfWeek] . ', ' . $d->format('d') . ' '
            . $months[(int) $d->format('n')] . ' ' . $d->format('Y');
    }

    /**
     * Format a Carbon date in simple Indonesian format (no day name):
     * e.g. "18 Agustus 2026"
     */
    public static function formatDateSimpleId($date): string
    {
        $months = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];
        $d = Carbon::parse($date);
        return $d->format('d') . ' ' . $months[(int) $d->format('n')] . ' ' . $d->format('Y');
    }
}
