<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CleaningEvidence extends Model
{
    use HasFactory;

    protected $table = 'cleaning_evidences';

    protected $fillable = [
        'schedule_id',
        'checklist_id',
        'photo_type',
        'file_path',
        'file_name',
        'file_size',
        'uploaded_by_id',
    ];

    // Relationships
    public function schedule()
    {
        return $this->belongsTo(CleaningSchedule::class, 'schedule_id');
    }

    public function checklist()
    {
        return $this->belongsTo(CleaningChecklist::class, 'checklist_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    // Scopes
    public function scopeBefore($query)
    {
        return $query->where('photo_type', 'Before');
    }

    public function scopeAfter($query)
    {
        return $query->where('photo_type', 'After');
    }

    public function scopeIssue($query)
    {
        return $query->where('photo_type', 'Issue');
    }

    // Accessors
    public function getFileSizeInMbAttribute()
    {
        return round($this->file_size / 1024 / 1024, 2);
    }
}