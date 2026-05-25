<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CleaningSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_id',
        'schedule_date',
        'schedule_time',
        'assigned_to_id',
        'supervisor_id',
        'status',
        'priority',
        'notes',
    ];

    protected $casts = [
        'schedule_date' => 'date',
    ];

    // Relationships
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function checklists()
    {
        return $this->hasMany(CleaningChecklist::class, 'schedule_id');
    }

    public function evidence()
    {
        return $this->hasMany(CleaningEvidence::class, 'schedule_id');
    }

    public function verification()
    {
        return $this->hasOne(CleaningVerification::class, 'schedule_id');
    }

    public function followUps()
    {
        return $this->hasManyThrough(
            FollowUpTask::class,
            CleaningVerification::class,
            'schedule_id',
            'verification_id'
        );
    }
}