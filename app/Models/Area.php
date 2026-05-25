<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Area extends Model
{
    use HasFactory;

protected $fillable = [
        'area_code',
        'area_name',
        'location',
        'floor',
        'building',
        'pic_user_id',
        'status',
        'schedule_frequency',
    ];

public function setStatusAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['status'] = null;
            return;
        }

        // Keep original casing as tests expect (e.g., 'Inactive'/'Active').
        $this->attributes['status'] = trim((string) $value);
    }

    public function setScheduleFrequencyAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['schedule_frequency'] = null;
            return;
        }

        $this->attributes['schedule_frequency'] = trim((string) $value);
    }

    // Relationships
    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function schedules()
    {
        return $this->hasMany(CleaningSchedule::class);
    }

    public function checklistItems()
    {
        return $this->hasMany(ChecklistItem::class);
    }
}