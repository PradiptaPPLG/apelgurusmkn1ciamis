<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApelLocation extends Model
{
    protected $table = 'apel_location';

    protected $fillable = [
        'latitude',
        'longitude',
        'radius_meter',
        'label',
        'updated_by',
    ];

    protected $casts = [
        'latitude'     => 'float',
        'longitude'    => 'float',
        'radius_meter' => 'integer',
    ];

    /**
     * Get the single active apel location record (singleton pattern).
     * Creates a default record if none exists.
     */
    public static function getInstance(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'latitude'     => null,
            'longitude'    => null,
            'radius_meter' => 10,
            'label'        => null,
        ]);
    }

    /**
     * Check if the location has been configured.
     */
    public function isConfigured(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }
}
