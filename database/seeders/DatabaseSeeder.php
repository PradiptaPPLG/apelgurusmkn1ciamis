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
                'role' => 'Guru',
                'status' => 'aktif',
            ],
            [
                'nik' => '198502022010012002',
                'name' => 'Dewi Lestari, S.Kom.',
                'role' => 'Guru',
                'status' => 'aktif',
            ],
            [
                'nik' => '199003032015011003',
                'name' => 'Andi Wijaya, A.Md.',
                'role' => 'TU',
                'status' => 'aktif',
            ],
            [
                'nik' => '2201010001',
                'name' => 'Roni Hidayat (PPL UPI)',
                'role' => 'PPL',
                'status' => 'aktif',
            ],
            [
                'nik' => '2201010002',
                'name' => 'Siti Rahma (PPL UNIGAL)',
                'role' => 'PPL',
                'status' => 'aktif',
            ],
            [
                'nik' => '2302020001',
                'name' => 'Budi Hartono (PPG)',
                'role' => 'PPG',
                'status' => 'aktif',
            ],
            [
                'nik' => '2302020002',
                'name' => 'Lina Marlina (PPG)',
                'role' => 'PPG',
                'status' => 'aktif',
            ],
            [
                'nik' => '12345',
                'name' => 'Test Dummy (Aktif)',
                'role' => 'Guru',
                'status' => 'aktif',
            ],
            [
                'nik' => '54321',
                'name' => 'Test Dummy (Nonaktif)',
                'role' => 'PPL',
                'status' => 'nonaktif',
            ],
        ];

        foreach ($participants as $p) {
            DB::table('participants')->updateOrInsert(
                ['nik' => $p['nik']],
                [
                    'name' => $p['name'],
                    'role' => $p['role'],
                    'status' => $p['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
