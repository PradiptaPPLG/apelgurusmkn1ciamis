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
        'valid_days',
        'end_date',
    ];

    protected $casts = [
        'date'       => 'date',
        'end_date'   => 'date',
        'valid_days' => 'integer',
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
     * Multi-day sessions: valid on any day between date and end_date.
     * Time window (start_time–end_time) is checked every day the session is active.
     */
    public function isOpen(): bool
    {
        $now     = Carbon::now();
        $today   = $now->toDateString();
        $start   = $this->date->toDateString();
        $end     = $this->end_date ? $this->end_date->toDateString() : $start;

        // Date range check
        if ($today < $start || $today > $end) {
            return false;
        }

        // Time window check (same every day)
        $startTime = Carbon::createFromTimeString($this->start_time);
        $endTime   = Carbon::createFromTimeString($this->end_time);

        return $now->between($startTime, $endTime);
    }

    /**
     * Check if session has fully expired (past end_date entirely).
     */
    public function isExpired(): bool
    {
        $end = $this->end_date ?? $this->date;
        return Carbon::today()->gt($end);
    }

    /**
     * Human-readable date range label.
     */
    public function dateRangeLabel(): string
    {
        $start = $this->date->format('d M Y');
        if (!$this->end_date || $this->end_date->eq($this->date)) {
            return $start;
        }
        // Same year: "18 – 20 Agt 2026"
        if ($this->date->year === $this->end_date->year) {
            return $this->date->format('d') . ' – ' . $this->end_date->format('d M Y');
        }
        return $start . ' – ' . $this->end_date->format('d M Y');
    }
}
