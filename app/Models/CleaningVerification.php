<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CleaningVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'verified_by_id',
        'verification_status',
        'notes',
        'findings',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function schedule()
    {
        return $this->belongsTo(CleaningSchedule::class, 'schedule_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function followUps()
    {
        return $this->hasMany(FollowUpTask::class, 'verification_id');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('verification_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('verification_status', 'rejected');
    }

    public function scopeNeedRevision($query)
    {
        return $query->where('verification_status', 'need-revision');
    }

    public function scopePending($query)
    {
        return $query->whereNull('verified_at');
    }

    // Helpers
    public function isApproved()
    {
        return $this->verification_status === 'approved';
    }

    public function isRejected()
    {
        return $this->verification_status === 'rejected';
    }

    public function isPending()
    {
        return is_null($this->verified_at);
    }
}