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
        // PPG / PLP / PPL / GEMA UPI → NIM + Kategori columns
        // Guru / Wali Kelas / TU / other → NIP + Jabatan columns
        $jabatanLower = strtolower(trim($jabatanFilter));
        $isPPG = in_array($jabatanLower, ['ppl', 'ppg', 'plp', 'gema upi']);
        $isTU  = in_array($jabatanLower, ['tu', 'tutt', 'tut', 'tu tt']);

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Dynamic sheet tab name and title based on filter
        if ($jabatanLower === 'wali kelas') {
            $sheetTabName = 'Wali Kelas';
            $titleText    = 'DAFTAR HADIR APEL WALI KELAS';
        } elseif ($isPPG) {
            $catName      = strtoupper($jabatanFilter) ?: 'PLP/PPG';
            $sheetTabName = $catName;
            $titleText    = 'DAFTAR HADIR APEL ' . $catName;
        } elseif ($isTU) {
            $sheetTabName = 'Tata Usaha';
            $titleText    = 'DAFTAR HADIR APEL TATA USAHA (TU)';
        } else {
            $sheetTabName = 'Guru' . ($jabatanFilter ? ' ' . ucwords($jabatanLower) : '');
            $titleText    = 'DAFTAR HADIR APEL ' . ($jabatanFilter ? strtoupper($jabatanFilter) : 'GURU');
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
        $logoPath = public_path('icons/logojawabaratheader.png');
        if (file_exists($logoPath) && is_readable($logoPath)) {
            try {
                $drawing = new Drawing();
                $drawing->setName('Logo Pemprov Jabar');
                $drawing->setPath($logoPath);
                $drawing->setHeight(90);        // scale to 90px height
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
        $col3  = $isPPG ? 'NIM'      : 'NIP';
        $col4  = $isPPG ? 'Kategori' : 'Jabatan';
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
                // PPG / PLP / PPL / GEMA UPI: NIM (from other_id or nip) + Kategori (PLP/PPG/PPL)
                $nim      = $p->other_id ?? ($p->nip ?? $a->participant_nik);
                $kategori = in_array(strtoupper($p->role ?? ''), ['PLP', 'PPG', 'PPL']) ? strtoupper($p->role) : ($p->jabatan ?? ($p->role ?? '-'));
                $sheet->setCellValue("C{$dataRow}", $nim);
                $sheet->setCellValue("D{$dataRow}", $kategori);
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
     * Build Monthly Recap Matrix Excel spreadsheet.
     */
    public static function buildMonthlyRecapExcel(
        int $month,
        int $year,
        string $jabatanFilter,
        $sessions,
        $participants,
        array $matrix
    ): Spreadsheet {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Presensi Apel');
        $sheet->setShowGridLines(true);

        $monthNames = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $monthName = $monthNames[$month] ?? '';

        // Calculate total columns
        $sessionCount = count($sessions);
        $lastColIndex = 4 + $sessionCount + 2; // No, Nama, NIP/NIM, Jabatan + sessions + Total + %
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIndex);

        // ── 1. KOP SEKOLAH ───────────────────────────────────────────────────
        $kopLines = [
            1 => ['text' => 'PEMERINTAH DAERAH PROVINSI JAWA BARAT',    'bold' => false, 'size' => 10],
            2 => ['text' => 'DINAS PENDIDIKAN',                          'bold' => true,  'size' => 10],
            3 => ['text' => 'CABANG DINAS PENDIDIKAN WILAYAH XIII',      'bold' => true,  'size' => 10],
            4 => ['text' => 'SMK NEGERI 1 CIAMIS',                       'bold' => true,  'size' => 14],
            5 => ['text' => 'Jalan : Jenderal Sudirman Nomor : 269 Tlp. (0265) 771204', 'bold' => false, 'size' => 8.5],
            6 => ['text' => 'Faksimile : (0265) 771204/777719  Website : www.smkn1ciamis.sch.id  E-mail : surat@smkn1cms.net', 'bold' => false, 'size' => 8.5],
            7 => ['text' => 'Ciamis – 46215',                            'bold' => false, 'size' => 8.5],
        ];

        foreach ($kopLines as $row => $line) {
            $sheet->mergeCells("A{$row}:{$lastColLetter}{$row}");
            $sheet->setCellValue("A{$row}", $line['text']);
            $sheet->getStyle("A{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$row}")->getFont()
                ->setName('Times New Roman')
                ->setSize($line['size'])
                ->setBold($line['bold']);
            $sheet->getRowDimension($row)->setRowHeight($row === 4 ? 20 : 13);
        }

        $sheet->getStyle("A7:{$lastColLetter}7")->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_MEDIUM);

        // Logo
        $logoPath = public_path('icons/logojawabaratheader.png');
        if (file_exists($logoPath) && is_readable($logoPath)) {
            try {
                $drawing = new Drawing();
                $drawing->setName('Logo Pemprov Jabar');
                $drawing->setPath($logoPath);
                $drawing->setHeight(85);
                $drawing->setCoordinates('A1');
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);
            } catch (\Throwable $e) {}
        }

        // Empty row 8
        $sheet->getRowDimension(8)->setRowHeight(8);

        // ── 2. TITLE & FILTER INFO ───────────────────────────────────────────
        $title = "REKAPITULASI DAFTAR HADIR APEL BULAN " . strtoupper($monthName) . " {$year}";
        if ($jabatanFilter) {
            $title .= " - " . strtoupper($jabatanFilter);
        }
        $sheet->mergeCells("A9:{$lastColLetter}9");
        $sheet->setCellValue("A9", $title);
        $sheet->getStyle("A9")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A9")->getFont()->setName('Times New Roman')->setSize(12)->setBold(true);
        $sheet->getRowDimension(9)->setRowHeight(20);

        $sheet->mergeCells("A10:{$lastColLetter}10");
        $sheet->setCellValue("A10", "Total Pelaksanaan Apel: {$sessionCount} Sesi | Total Peserta: " . count($participants) . " Orang");
        $sheet->getStyle("A10")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A10")->getFont()->setName('Times New Roman')->setSize(9.5)->setItalic(true);
        $sheet->getRowDimension(10)->setRowHeight(15);

        // Empty row 11
        $sheet->getRowDimension(11)->setRowHeight(8);

        // ── 3. TABLE HEADERS ─────────────────────────────────────────────────
        $headerRow = 12;
        $sheet->setCellValue("A{$headerRow}", "No");
        $sheet->setCellValue("B{$headerRow}", "Nama Lengkap");
        $sheet->setCellValue("C{$headerRow}", "NIP / NIM");
        $sheet->setCellValue("D{$headerRow}", "Kategori / Jabatan");

        $colIdx = 5;
        foreach ($sessions as $session) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $dateStr = Carbon::parse($session->date)->format('d/m');
            $sheet->setCellValue("{$colLetter}{$headerRow}", "Tgl " . $dateStr . "\n" . $session->code);
            $sheet->getStyle("{$colLetter}{$headerRow}")->getAlignment()->setWrapText(true);
            $sheet->getColumnDimension($colLetter)->setWidth(12);
            $colIdx++;
        }

        $totalColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
        $sheet->setCellValue("{$totalColLetter}{$headerRow}", "Total\nHadir");
        $sheet->getStyle("{$totalColLetter}{$headerRow}")->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension($totalColLetter)->setWidth(10);
        $colIdx++;

        $pctColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
        $sheet->setCellValue("{$pctColLetter}{$headerRow}", "%\nHadir");
        $sheet->getStyle("{$pctColLetter}{$headerRow}")->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension($pctColLetter)->setWidth(10);

        // Header styles
        $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 9.5, 'name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']], // Dark slate
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']],
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // Fixed column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);

        // ── 4. DATA ROWS ─────────────────────────────────────────────────────
        $currentRow = $headerRow + 1;
        $no = 1;

        foreach ($participants as $p) {
            $sheet->setCellValue("A{$currentRow}", $no);
            $sheet->setCellValue("B{$currentRow}", $p->name);
            
            // Identifier (NIP or other_id/NIM)
            $idVal = $p->nip ?: ($p->other_id ?: $p->nik);
            $sheet->setCellValueExplicit("C{$currentRow}", $idVal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            
            // Jabatan / Kategori
            $jabatanDisplay = $p->jabatan ?: $p->role;
            $sheet->setCellValue("D{$currentRow}", $jabatanDisplay);

            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $attended = 0;
            $sessColIdx = 5;

            foreach ($sessions as $s) {
                $colLtr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($sessColIdx);
                $att = $matrix[$p->nik][$s->id] ?? null;

                if ($att) {
                    $attended++;
                    $timeStr = Carbon::parse($att->signed_in_at)->format('H:i');
                    $sheet->setCellValue("{$colLtr}{$currentRow}", "✓ {$timeStr}");
                    $sheet->getStyle("{$colLtr}{$currentRow}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF047857')); // Emerald green
                } else {
                    $sheet->setCellValue("{$colLtr}{$currentRow}", "-");
                    $sheet->getStyle("{$colLtr}{$currentRow}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF94A3B8')); // Muted slate
                }
                $sheet->getStyle("{$colLtr}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sessColIdx++;
            }

            // Total Hadir & Percentage
            $totalColLtr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($sessColIdx);
            $sheet->setCellValue("{$totalColLtr}{$currentRow}", $attended);
            $sheet->getStyle("{$totalColLtr}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$totalColLtr}{$currentRow}")->getFont()->setBold(true);
            $sessColIdx++;

            $pctVal = $sessionCount > 0 ? round(($attended / $sessionCount) * 100, 1) : 0;
            $pctColLtr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($sessColIdx);
            $sheet->setCellValue("{$pctColLtr}{$currentRow}", "{$pctVal}%");
            $sheet->getStyle("{$pctColLtr}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$pctColLtr}{$currentRow}")->getFont()->setBold(true);

            // Zebra striping
            if ($no % 2 === 0) {
                $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF8FAFC');
            }

            $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->getBorders()
                ->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE2E8F0');
            $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->getFont()
                ->setName('Times New Roman')->setSize(9.5);

            $sheet->getRowDimension($currentRow)->setRowHeight(20);
            $currentRow++;
            $no++;
        }

        // ── 5. SIGNATURE BLOCK ───────────────────────────────────────────────
        $sigRow = $currentRow + 2;
        $sigStartColIdx = max(1, $lastColIndex - 3);
        $sigStartColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($sigStartColIdx);

        $tglCiamis = "Ciamis, " . self::formatDateSimpleId(Carbon::now());
        $sheet->mergeCells("{$sigStartColLetter}{$sigRow}:{$lastColLetter}{$sigRow}");
        $sheet->setCellValue("{$sigStartColLetter}{$sigRow}", $tglCiamis);
        $sheet->getStyle("{$sigStartColLetter}{$sigRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sigRow++;
        $sheet->mergeCells("{$sigStartColLetter}{$sigRow}:{$lastColLetter}{$sigRow}");
        $sheet->setCellValue("{$sigStartColLetter}{$sigRow}", "Kepala Sekolah,");
        $sheet->getStyle("{$sigStartColLetter}{$sigRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sigRow += 4; // Space for signature
        $sheet->mergeCells("{$sigStartColLetter}{$sigRow}:{$lastColLetter}{$sigRow}");
        $sheet->setCellValue("{$sigStartColLetter}{$sigRow}", self::KEPSEK_NAME);
        $sheet->getStyle("{$sigStartColLetter}{$sigRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$sigStartColLetter}{$sigRow}")->getFont()->setBold(true)->setUnderline(true);

        $sigRow++;
        $sheet->mergeCells("{$sigStartColLetter}{$sigRow}:{$lastColLetter}{$sigRow}");
        $sheet->setCellValue("{$sigStartColLetter}{$sigRow}", self::KEPSEK_GOLOK);
        $sheet->getStyle("{$sigStartColLetter}{$sigRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sigRow++;
        $sheet->mergeCells("{$sigStartColLetter}{$sigRow}:{$lastColLetter}{$sigRow}");
        $sheet->setCellValue("{$sigStartColLetter}{$sigRow}", self::KEPSEK_NIP);
        $sheet->getStyle("{$sigStartColLetter}{$sigRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(9.5);

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
