<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApelSession extends Model
{
    protected $table = 'apel_sessions';

    protected $fillable = [
        'title',
        'date',
        'type',
        'start_time',
        'end_time',
        'code',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the attendances for the session.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'apel_session_id');
    }

    /**
     * Check if this session is open for attendance right now.
     */
    public function isOpen(): bool
    {
        $now = Carbon::now();
        
        // Date check
        if ($this->date->format('Y-m-d') !== $now->format('Y-m-d')) {
            return false;
        }

        // Time check
        $startTime = Carbon::createFromTimeString($this->start_time);
        $endTime = Carbon::createFromTimeString($this->end_time);

        return $now->between($startTime, $endTime);
    }
}
