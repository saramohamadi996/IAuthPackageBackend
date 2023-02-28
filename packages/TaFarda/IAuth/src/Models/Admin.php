<?php

namespace TaFarda\IAuth\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\LogOptions;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, LogsActivity, HasRoles;

    /**
     *The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admins';

    /**
     * guard_name for roles and permission.
     *
     * @var string
     */
    protected string $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['status', 'email', 'password', 'webservice_call_token'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var string[]
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * The value on which to search.
     *
     * @var array|string[]
     */
    public static array $SEARCH_ITEMS = [
        'email'
    ];

    /**
     * Get the product associated with the admin.
     *
     * @return HasOne
     */
    public function product(): HasOne
    {
        return $this->hasOne(Product::class);
    }

    /**
     * get activity log options.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

}
