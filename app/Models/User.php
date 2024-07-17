<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Observers\ActivityLogObserver;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Passwords\CanResetPassword as PasswordResetTrait;
use Illuminate\Database\Eloquent\SoftDeletes; {
}
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ActivityLogObserver::class])]

class User extends Authenticatable implements CanResetPassword
{
    use HasFactory, Notifiable, HasApiTokens, PasswordResetTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'is_approved',
        'account_number'

    ];

    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

//    public function deposits()
//    {
//        return $this->hasMany(Deposit::class);
//    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasPermission($per)
    {
        $this->permissions();
    }

    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function wallet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {

        return $this->hasOne(Wallet::class, 'user_id');
    }



//    public function getAccountNumberAttribute()
//    {
//        return $this->account_number;
//    }
    public function getBalanceAttribute()
    {
        return $this->wallet()->balance;
    }








    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
