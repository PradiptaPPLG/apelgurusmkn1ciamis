<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'school_name',
        'app_name',
        'school_address',
        'default_pagi_start',
        'default_pagi_end',
        'default_sore_start',
        'default_sore_end',
        'default_radius',
        'kepsek_name',
        'kepsek_nip',
        'kepsek_pangkat',
    ];

    /**
     * Singleton instance pattern for AppSetting.
     */
    public static function getInstance(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'school_name'        => 'SMKN 1 Ciamis',
            'app_name'           => 'Asign',
            'school_address'     => 'Jl. Jend. Sudirman No. 269, Ciamis, Jawa Barat 46211',
            'default_pagi_start' => '06:20',
            'default_pagi_end'   => '06:40',
            'default_sore_start' => '14:50',
            'default_sore_end'   => '15:20',
            'default_radius'     => 25,
            'kepsek_name'        => 'Drs. H. Asep Gunawan, M.Pd.',
            'kepsek_nip'         => '19680512 199403 1 005',
            'kepsek_pangkat'     => 'Pembina Utama Muda / IV c',
        ]);
    }
}
