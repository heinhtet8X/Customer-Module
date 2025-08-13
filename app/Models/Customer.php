<?php

namespace Modules\Customers\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// use Modules\Customers\Database\Factories\CustomerFactory;

class Customer extends Model
{
    use HasFactory, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'password',
        'email',
        'dial_code',
        'phone',
        'status',
        'otp_code',
        'otp_expires_at',
        'last_active_at',
    ];

    protected $hidden = ['password', 'otp_code', 'deleted_at', 'otp_expires_at'];

    public const STATUS_ACTIVE   = '0';
    public const STATUS_INACTIVE = '1';
    public const STATUS_BANNED   = '2';

    // public const STATUS_VERIFY   = 'verify';


    #[Scope]
    protected function isActive(Builder $query): void
    {
        $query->where('status', '0');
    }

    #[Scope]
    protected function inActive(Builder $query)
    {
        $query->where('status', '1');
    }
}
