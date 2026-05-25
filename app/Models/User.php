<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    protected string $guard_name = 'web';


    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function areaAsPic()
    {
        return $this->hasMany(Area::class, 'pic_user_id');
    }

    public function schedulesAssigned()
    {
        return $this->hasMany(CleaningSchedule::class, 'assigned_to_id');
    }

    public function schedulesSupervised()
    {
        return $this->hasMany(CleaningSchedule::class, 'supervisor_id');
    }

    public function verifications()
    {
        return $this->hasMany(CleaningVerification::class, 'verified_by_id');
    }

    public function followUpsAssigned()
    {
        return $this->hasMany(FollowUpTask::class, 'assigned_to_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}