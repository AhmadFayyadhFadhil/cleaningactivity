<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Disable updated_at since we only track created_at
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByModel($query, $model)
    {
        return $query->where('model', $model);
    }

    public function scopeByModelId($query, $modelId)
    {
        return $query->where('model_id', $modelId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helpers
    public function getChangesSummary()
    {
        $changes = [];
        
        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? null;
                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'from' => $oldValue,
                        'to' => $newValue,
                    ];
                }
            }
        }
        
        return $changes;
    }

    public function getUserName()
    {
        return $this->user->name ?? 'Unknown User';
    }

    public function getActionLabel()
    {
        $labels = [
            'CREATE' => 'Created',
            'UPDATE' => 'Updated',
            'DELETE' => 'Deleted',
            'VERIFY' => 'Verified',
            'APPROVE' => 'Approved',
            'REJECT' => 'Rejected',
            'LOGIN' => 'Logged in',
            'LOGOUT' => 'Logged out',
        ];

        return $labels[$this->action] ?? $this->action;
    }

    // Static methods for logging
    public static function log($action, $model, $modelId, $oldValues = null, $newValues = null, $userId = null)
    {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logCreate($model, $modelId, $data, $userId = null)
    {
        return self::log('CREATE', $model, $modelId, null, $data, $userId);
    }

    public static function logUpdate($model, $modelId, $oldData, $newData, $userId = null)
    {
        return self::log('UPDATE', $model, $modelId, $oldData, $newData, $userId);
    }

    public static function logDelete($model, $modelId, $data, $userId = null)
    {
        return self::log('DELETE', $model, $modelId, $data, null, $userId);
    }
}