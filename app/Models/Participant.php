<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    protected $table = 'participants';
    
    // NIK is primary key, non-incrementing string
    protected $primaryKey = 'nik';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nik',
        'name',
        'role',
        'jabatan',
        'jenis_kepegawaian',
        'status',
    ];

    /**
     * Get the attendances for the participant.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'participant_nik', 'nik');
    }
}
