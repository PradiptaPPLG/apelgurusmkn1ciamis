<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'apel_session_id',
        'participant_nik',
        'signature',
        'photo',
        'latitude',
        'longitude',
        'location_name',
        'device_uuid',
        'signed_in_at',
    ];

    protected $casts = [
        'signed_in_at' => 'datetime',
    ];

    /**
     * Get the apel session.
     */
    public function apelSession(): BelongsTo
    {
        return $this->belongsTo(ApelSession::class, 'apel_session_id');
    }

    /**
     * Get the participant.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'participant_nik', 'nik');
    }
}
