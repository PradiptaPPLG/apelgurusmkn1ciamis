<?php

namespace App\Services;

use App\Models\Participant;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ParticipantImporter
{
    // Daftar kolom database yang diperbolehkan untuk dipetakan
    public static $dbFields = [
        'nik' => 'NIK (Kunci Utama, Wajib)',
        'name' => 'Nama Lengkap (Wajib)',
        'nip' => 'NIP',
        'other_id' => 'ID Lainnya (Custom/Sekolah)',
        'jabatan' => 'Jabatan',
        'jenis_kepegawaian' => 'Jenis Kepegawaian (pns/asn/p3k/honorer/mahasiswa)',
        'role' => 'Peran (Guru/TU/PPL/PPG/Wali Kelas)',
        'status' => 'Status Keaktifan (aktif/nonaktif)'
    ];

    // Kamus sinonim kolom bahasa Indonesia & Inggris untuk mencocokkan kolom secara cerdas
    protected static $aliases = [
        'nik' => ['nik', 'n.i.k', 'ktp', 'nomorktp', 'no.ktp', 'id', 'identitas', 'nokependudukan'],
        'name' => ['nama', 'name', 'namalengkap', 'namaguru', 'namapeserta', 'namapegawai', 'namalengkapguru'],
        'nip' => ['nip', 'n.i.p', 'nomorindukpegawai', 'no.nip'],
        'other_id' => ['idlain', 'idlainnya', 'otherid', 'customid', 'nomorunik', 'idcustom', 'noyayasan'],
        'jabatan' => ['jabatan', 'posisi', 'tugas', 'pekerjaan', 'rolekerja'],
        'jenis_kepegawaian' => ['jeniskepegawaian', 'statuskepegawaian', 'kepegawaian', 'jenispegawai', 'statuskerja', 'tipekepegawaian'],
        'role' => ['role', 'peran', 'kategori', 'kelompok', 'peranpeserta', 'kategorianggota'],
        'status' => ['status', 'aktif', 'keaktifan', 'statusaktif', 'statusanggota']
    ];

    /**
     * Membaca file excel untuk header dan beberapa baris data pertama guna ditampilkan di pratinjau.
     */
    public function getPreviewData(string $filePath)
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        $highestColIndex = Coordinate::columnIndexFromString($highestCol);

        // Cari baris header (baris pertama yang memiliki setidaknya 2 kolom terisi)
        $headerRowIndex = 1;
        $headers = [];
        for ($row = 1; $row <= min(10, $highestRow); $row++) {
            $colsCount = 0;
            $tempHeaders = [];
            for ($col = 1; $col <= $highestColIndex; $col++) {
                $cellValue = trim($sheet->getCell([$col, $row])->getValue() ?? '');
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $tempHeaders[$colLetter] = $cellValue;
                if ($cellValue !== '') {
                    $colsCount++;
                }
            }
            if ($colsCount >= 2) {
                $headerRowIndex = $row;
                $headers = $tempHeaders;
                break;
            }
        }

        // Jika tidak ada header yang terdeteksi, default ke Baris 1
        if (empty($headers)) {
            $headerRowIndex = 1;
            for ($col = 1; $col <= $highestColIndex; $col++) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $headers[$colLetter] = trim($sheet->getCell([$col, 1])->getValue() ?? '');
            }
        }

        // Cari pencocokan sinonim otomatis (Smart Guesses)
        $guesses = [];
        foreach ($headers as $colLetter => $headerText) {
            $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $headerText));
            $guesses[$colLetter] = 'ignore'; // default diabaikan jika tidak cocok
            
            foreach (self::$aliases as $field => $fieldAliases) {
                if (in_array($normalized, $fieldAliases)) {
                    $guesses[$colLetter] = $field;
                    break;
                }
            }
        }

        // Ambil maksimal 5 baris pertama data setelah baris header untuk pratinjau data asli
        $previewRows = [];
        $dataStartRow = $headerRowIndex + 1;
        $previewCount = 0;
        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
            $rowData = [];
            $hasData = false;
            for ($col = 1; $col <= $highestColIndex; $col++) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $val = trim($sheet->getCell([$col, $row])->getValue() ?? '');
                $rowData[$colLetter] = $val;
                if ($val !== '') {
                    $hasData = true;
                }
            }
            if ($hasData) {
                $previewRows[] = $rowData;
                $previewCount++;
                if ($previewCount >= 5) {
                    break;
                }
            }
        }

        return [
            'headers' => $headers,
            'guesses' => $guesses,
            'previewRows' => $previewRows,
            'headerRowIndex' => $headerRowIndex,
            'totalRows' => $highestRow
        ];
    }

    /**
     * Memproses impor seluruh baris data excel berdasarkan pemetaan yang dipilih user.
     */
    public function import(string $filePath, array $mapping, string $duplicateAction, string $defaultRole, string $defaultStatus)
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        
        $highestRow = $sheet->getHighestRow();
        
        // Dapatkan indeks baris mulai data
        $previewInfo = $this->getPreviewData($filePath);
        $dataStartRow = $previewInfo['headerRowIndex'] + 1;

        $results = [
            'total' => 0,
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        DB::beginTransaction();
        try {
            for ($row = $dataStartRow; $row <= $highestRow; $row++) {
                $rowHasData = false;
                $mappedData = [];
                
                foreach ($mapping as $colLetter => $dbField) {
                    if ($dbField === 'ignore') {
                        continue;
                    }
                    $cellVal = trim($sheet->getCell($colLetter . $row)->getValue() ?? '');
                    if ($cellVal !== '') {
                        $rowHasData = true;
                    }
                    $mappedData[$dbField] = $cellVal;
                }

                // Lewati baris jika seluruh sel yang dipetakan kosong
                if (!$rowHasData) {
                    continue;
                }

                $results['total']++;

                $nik = $mappedData['nik'] ?? null;
                $name = $mappedData['name'] ?? null;

                if (empty($nik) || empty($name)) {
                    $results['skipped']++;
                    $results['errors'][] = "Baris #{$row}: Dilewati karena NIK atau Nama kosong.";
                    continue;
                }

                // Bersihkan NIK (hanya angka/huruf)
                $nik = preg_replace('/[^0-9a-zA-Z]/', '', $nik);

                // Periksa duplikat NIK
                $existing = Participant::where('nik', $nik)->first();
                if ($existing && $duplicateAction === 'skip') {
                    $results['skipped']++;
                    continue;
                }

                // 1. Normalisasi Jenis Kepegawaian (asn, pns, p3k, honorer, mahasiswa)
                $jenisPeg = strtolower(trim($mappedData['jenis_kepegawaian'] ?? ''));
                if (str_contains($jenisPeg, 'pns')) {
                    $jenisPeg = 'pns';
                } elseif (str_contains($jenisPeg, 'p3k') || str_contains($jenisPeg, 'pppk')) {
                    $jenisPeg = 'p3k';
                } elseif (str_contains($jenisPeg, 'asn')) {
                    $jenisPeg = 'asn';
                } elseif (str_contains($jenisPeg, 'honor') || str_contains($jenisPeg, 'gtt') || str_contains($jenisPeg, 'ptt') || str_contains($jenisPeg, 'non pns')) {
                    $jenisPeg = 'honorer';
                } elseif (str_contains($jenisPeg, 'mahasiswa') || str_contains($jenisPeg, 'ppl') || str_contains($jenisPeg, 'ppg') || str_contains($jenisPeg, 'magang')) {
                    $jenisPeg = 'mahasiswa';
                } else {
                    $jenisPeg = null; // null jika tidak terisi atau tidak valid
                }

                // 2. Normalisasi Peran / Kategori (Guru, TU, PPL, PPG, Wali Kelas)
                $role = trim($mappedData['role'] ?? '');
                $roleLower = strtolower($role);
                if (str_contains($roleLower, 'guru')) {
                    $role = 'Guru';
                } elseif (str_contains($roleLower, 'tu') || str_contains($roleLower, 'tata usaha') || str_contains($roleLower, 'staf')) {
                    $role = 'TU';
                } elseif (str_contains($roleLower, 'ppl')) {
                    $role = 'PPL';
                } elseif (str_contains($roleLower, 'ppg')) {
                    $role = 'PPG';
                } elseif (str_contains($roleLower, 'wali') || str_contains($roleLower, 'kelas')) {
                    $role = 'Wali Kelas';
                } else {
                    $role = $defaultRole;
                }

                // 3. Normalisasi Status
                $status = strtolower(trim($mappedData['status'] ?? ''));
                if ($status === '') {
                    $status = $defaultStatus;
                } elseif (str_contains($status, 'tidak') || str_contains($status, 'non') || str_contains($status, 'pasif')) {
                    $status = 'nonaktif';
                } else {
                    $status = 'aktif';
                }

                $dataToSave = [
                    'nik' => $nik,
                    'name' => $name,
                    'nip' => !empty($mappedData['nip']) ? trim($mappedData['nip']) : null,
                    'other_id' => !empty($mappedData['other_id']) ? trim($mappedData['other_id']) : null,
                    'jabatan' => !empty($mappedData['jabatan']) ? trim($mappedData['jabatan']) : null,
                    'jenis_kepegawaian' => $jenisPeg,
                    'role' => $role,
                    'status' => $status,
                ];

                try {
                    if ($existing) {
                        $existing->update($dataToSave);
                        $results['updated']++;
                    } else {
                        Participant::create($dataToSave);
                        $results['inserted']++;
                    }
                } catch (\Exception $e) {
                    $results['skipped']++;
                    $msg = $e->getMessage();
                    if (str_contains($msg, 'UNIQUE constraint failed')) {
                        $results['errors'][] = "Baris #{$row} (Nama: {$name}): Gagal karena NIP/ID Lain sudah digunakan oleh peserta lain.";
                    } else {
                        $results['errors'][] = "Baris #{$row} (Nama: {$name}): Gagal diimpor. Error database.";
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }
}
