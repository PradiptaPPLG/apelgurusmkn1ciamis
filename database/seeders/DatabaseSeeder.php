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
                'name'     => 'Admin Apel SMKN 1 Ciamis',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ]
        );

        // Seed Kepala Sekolah user
        User::updateOrCreate(
            ['email' => 'kepsek@apel.com'],
            [
                'name'     => 'Kepala Sekolah SMKN 1 Ciamis',
                'password' => Hash::make('kepsek123'),
                'role'     => 'kepala_sekolah',
            ]
        );

        // Seed some sample participants
        $participants = [
            [
                'nik' => '3207010101810001',
                'nip' => '198101012005011001',
                'other_id' => null,
                'name' => 'H. Ahmad Sodikin, S.Pd., M.Pd.',
                'jabatan' => 'Guru',
                'role' => 'Guru',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'asn',
            ],
            [
                'nik' => '3207010202850002',
                'nip' => '198502022010012002',
                'other_id' => null,
                'name' => 'Dewi Lestari, S.Kom.',
                'jabatan' => 'Guru',
                'role' => 'Guru',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'pns',
            ],
            [
                'nik' => '3207010303900003',
                'nip' => '199003032015011003',
                'other_id' => null,
                'name' => 'Andi Wijaya, A.Md.',
                'jabatan' => 'TU',
                'role' => 'TU',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'p3k',
            ],
            [
                'nik' => '3207010404020001',
                'nip' => null,
                'other_id' => '2201010001',
                'name' => 'Roni Hidayat (PPL UPI)',
                'jabatan' => 'PPL',
                'role' => 'PPL',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'asn',
            ],
            [
                'nik' => '3207010505020002',
                'nip' => null,
                'other_id' => '2201010002',
                'name' => 'Siti Rahma (PPL UNIGAL)',
                'jabatan' => 'PPL',
                'role' => 'PPL',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'p3k-paruhwaktu',
            ],
            [
                'nik' => '3207010606030001',
                'nip' => null,
                'other_id' => '2302020001',
                'name' => 'Budi Hartono (PPG)',
                'jabatan' => 'PPG',
                'role' => 'PPG',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'p3k-paruhwaktu',
            ],
            [
                'nik' => '3207010707030002',
                'nip' => null,
                'other_id' => '2302020002',
                'name' => 'Lina Marlina (PPG)',
                'jabatan' => 'PPG',
                'role' => 'PPG',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'p3k-paruhwaktu',
            ],
            [
                'nik' => '12345',
                'nip' => '67890',
                'other_id' => '99999',
                'name' => 'Test Dummy (Aktif)',
                'jabatan' => 'Guru',
                'role' => 'Guru',
                'status' => 'aktif',
                'jenis_kepegawaian' => 'p3k-paruhwaktu',
            ],
            [
                'nik' => '54321',
                'nip' => '09876',
                'other_id' => '88888',
                'name' => 'Test Dummy (Nonaktif)',
                'jabatan' => 'PPL',
                'role' => 'PPL',
                'status' => 'nonaktif',
                'jenis_kepegawaian' => 'p3k-paruhwaktu',
            ],
        ];

        foreach ($participants as $p) {
            DB::table('participants')->updateOrInsert(
                ['nik' => $p['nik']],
                [
                    'nip' => $p['nip'],
                    'other_id' => $p['other_id'],
                    'name' => $p['name'],
                    'jabatan' => $p['jabatan'],
                    'role' => $p['role'],
                    'jenis_kepegawaian' => $p['jenis_kepegawaian'],
                    'status' => $p['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
