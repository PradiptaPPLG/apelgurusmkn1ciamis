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
    }
}
