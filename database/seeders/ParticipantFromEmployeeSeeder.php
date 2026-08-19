<?php

namespace Database\Seeders;

use App\Models\Employee;
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
            // Cek apakah sudah ada di participants (primary key = nik)
            $exists = Participant::where('nik', $emp->nip)->exists();
            
            if (!$exists) {
                // Tentukan role berdasarkan jabatan/status
                // Valid values: 'Guru', 'TU', 'PPL', 'PPG'
                $role = 'Guru'; // default
                
                if ($emp->jabatan && str_contains($emp->jabatan, 'Kepala Sekolah')) {
                    $role = 'Guru'; // Kepala Sekolah tetap sebagai Guru
                } elseif ($emp->status && in_array($emp->status, ['TU', 'TU TT'])) {
                    $role = 'TU';
                } elseif ($emp->status && $emp->status === 'PPPK PW') {
                    $role = 'Guru';
                }
                
                // Tentukan status: 'aktif' atau 'nonaktif'
                $status = 'aktif';
                
                try {
                    Participant::create([
                        'nik' => $emp->nip,
                        'nip' => $emp->nip,
                        'other_id' => null,
                        'name' => $emp->name,
                        'jabatan' => $emp->jabatan,
                        'jenis_kepegawaian' => $emp->status ?? 'Guru/Tendik',
                        'role' => $role,
                        'status' => $status,
                    ]);
                    $count++;
                } catch (\Exception $e) {
                    $this->command->error("Failed to insert: {$emp->name} - " . $e->getMessage());
                    $skipped++;
                }
            } else {
                $skipped++;
            }
        }
        
        $this->command->info("Successfully seeded {$count} participants from employees. Skipped: {$skipped}");
    }
}