<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CleaningChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'item_id',
        'condition',
        'notes',
    ];

    // Relationships
    public function schedule()
    {
        return $this->belongsTo(CleaningSchedule::class, 'schedule_id');
    }

    public function item()
    {
        return $this->belongsTo(ChecklistItem::class, 'item_id');
    }

    public function evidence()
    {
        return $this->hasMany(CleaningEvidence::class, 'checklist_id');
    }

    // Scopes
    public function scopeClean($query)
    {
        return $query->where('condition', 'Clean');
    }

    public function scopeDirty($query)
    {
        return $query->where('condition', 'Dirty');
    }

    public function scopeDamaged($query)
    {
        return $query->where('condition', 'Damaged');
    }
}