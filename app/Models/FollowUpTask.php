<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowUpTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'verification_id',
        'issue_description',
        'priority',
        'assigned_to_id',
        'status',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function verification()
    {
        return $this->belongsTo(CleaningVerification::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function schedule()
    {
        return $this->hasOneThrough(
            CleaningSchedule::class,
            CleaningVerification::class,
            'id',
            'id',
            'verification_id',
            'schedule_id'
        );
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'Open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'In Progress');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'Closed');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'High')
                     ->orWhere('priority', 'Critical');
    }

    public function scopeByPriority($query)
    {
        return $query->orderByRaw("
            CASE priority
                WHEN 'Critical' THEN 1
                WHEN 'High' THEN 2
                WHEN 'Medium' THEN 3
                WHEN 'Low' THEN 4
                ELSE 5
            END ASC
        ");
    }

    // Helpers
    public function isOpen()
    {
        return $this->status === 'Open';
    }

    public function isInProgress()
    {
        return $this->status === 'In Progress';
    }

    public function isClosed()
    {
        return $this->status === 'Closed';
    }

    public function isPriority()
    {
        return in_array($this->priority, ['High', 'Critical']);
    }

    public function markAsInProgress()
    {
        $this->update(['status' => 'In Progress']);
        return $this;
    }

    public function markAsClosed($notes = null)
    {
        $this->update([
            'status' => 'Closed',
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
        return $this;
    }
}