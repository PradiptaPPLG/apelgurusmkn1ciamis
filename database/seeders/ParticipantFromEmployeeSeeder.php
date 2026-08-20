<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Intern;
use App\Models\Participant;
use Illuminate\Database\Seeder;

class ParticipantFromEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        $count = 0;
        $skipped = 0;
        
        foreach ($employees as $emp) {
            // Tentukan role berdasarkan jabatan/status
            // Satukan TUT dan TUTT ke dalam role 'TU'
            $role = 'Guru'; // default
            $jenisKepegawaian = $emp->status ?? 'Guru/Tendik';
            
            if ($emp->jabatan && str_contains($emp->jabatan, 'Kepala Sekolah')) {
                $role = 'Guru';
            } elseif ($emp->status && (str_contains(strtoupper($emp->status), 'TU') || in_array($emp->status, ['TU', 'TU TT', 'TUT', 'TUTT']))) {
                $role = 'TU';
                $jenisKepegawaian = 'TU';
            } elseif ($emp->status && $emp->status === 'PPPK PW') {
                $role = 'Guru';
            }
            
            // Tentukan status: 'aktif' atau 'nonaktif'
            $status = 'aktif';
            
            try {
                Participant::updateOrCreate(
                    ['nik' => $emp->nip],
                    [
                        'nip'               => $emp->nip,
                        'other_id'          => null,
                        'name'              => $emp->name,
                        'jabatan'           => $emp->jabatan,
                        'jenis_kepegawaian' => $jenisKepegawaian,
                        'role'              => $role,
                        'status'            => $status,
                    ]
                );
                $count++;
            } catch (\Exception $e) {
                $this->command->error("Failed to insert employee: {$emp->name} - " . $e->getMessage());
                $skipped++;
            }
        }

        // Seed PLP, PPG, & Interns into participants
        $interns = Intern::all();
        $internCount = 0;

        foreach ($interns as $intern) {
            $jenisUpper = strtoupper(trim($intern->jenis));
            $role = in_array($jenisUpper, ['PLP', 'PPG']) ? $jenisUpper : 'PPL';
            
            try {
                Participant::updateOrCreate(
                    ['nik' => $intern->nim],
                    [
                        'nip'               => null,
                        'other_id'          => $intern->nim,
                        'name'              => $intern->name,
                        'jabatan'           => $intern->jenis, // Misal: PLP / PPG / GEMA UPI
                        'jenis_kepegawaian' => 'mahasiswa',
                        'role'              => $role,
                        'status'            => 'aktif',
                    ]
                );
                $internCount++;
            } catch (\Exception $e) {
                $this->command->error("Failed to insert intern: {$intern->name} - " . $e->getMessage());
            }
        }
        
        $this->command->info("Successfully seeded {$count} employee participants (TU unified) and {$internCount} intern participants (PLP/PPG).");
    }
}