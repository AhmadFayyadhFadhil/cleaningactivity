<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChecklistItem extends Model
{
    use HasFactory;

    public function setStatusAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['status'] = null;
            return;
        }

        $this->attributes['status'] = strtolower(trim((string) $value));
    }


    protected $fillable = [
        'item_code',
        'item_name',
        'category',
        'description',
        'instruction',
        'status',
    ];

    // Relationships
    public function submissions()
    {
        return $this->hasMany(CleaningChecklist::class, 'item_id');
    }
}