<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed default Admin
        User::updateOrCreate(
            ['email' => 'admin@apel.com'],
            [
                'name' => 'Admin Apel SMKN 1 Ciamis',
                'password' => Hash::make('admin123'),
            ]
        );

        // Seed some sample participants
        $participants = [
            [
                'nik' => '198101012005011001',
                'name' => 'H. Ahmad Sodikin, S.Pd., M.Pd.',
                'jabatan' => 'Guru',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'asn',
            ],
            [
                'nik' => '198502022010012002',
                'name' => 'Dewi Lestari, S.Kom.',
                'jabatan' => 'Guru',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'pns',
            ],
            [
                'nik' => '199003032015011003',
                'name' => 'Andi Wijaya, A.Md.',
                'jabatan' => 'TU',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'p3k',
            ],
            [
                'nik' => '2201010001',
                'name' => 'Roni Hidayat (PPL UPI)',
                'jabatan' => 'PPL',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'asn',
            ],
            [
                'nik' => '2201010002',
                'name' => 'Siti Rahma (PPL UNIGAL)',
                'jabatan' => 'PPL',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'p3k-paruhwaktu',
            ],
            [
                'nik' => '2302020001',
                'name' => 'Budi Hartono (PPG)',
                'jabatan' => 'PPG',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'p3k-paruhwaktu',
            ],
            [
                'nik' => '2302020002',
                'name' => 'Lina Marlina (PPG)',
                'jabatan' => 'PPG',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'p3k-paruhwaktu',
            ],
            [
                'nik' => '12345',
                'name' => 'Test Dummy (Aktif)',
                'jabatan' => 'Guru',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'p3k-paruhwaktu',
            ],
            [
                'nik' => '54321',
                'name' => 'Test Dummy (Nonaktif)',
                'jabatan' => 'PPL',
                'status' => 'nonaktif',
                'jenis_kepegawaian' => 'p3k-paruhwaktu',
            ],
        ];

        foreach ($participants as $p) {
            DB::table('participants')->updateOrInsert(
                ['nik' => $p['nik']],
                [
                    'name' => $p['name'],
                    'jabatan' => $p['jabatan'],
                    'jenis_kepegawaian' => $p['jenis_kepegawaian'],
                    'status' => $p['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
