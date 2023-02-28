<?php

namespace TaFarda\IAuth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class UserProfile extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['user_id', 'first_name', 'last_name', 'father_name', 'sex', 'birth_date',
        'national_code', 'identity_number', 'address', 'phone', 'postal_code', 'state', 'city'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var string[]
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * @var array|string[]
     */
    public static array $IMAGES = [
        'profile_image' => 'تصویر پروفایل'
    ];

    /**
     * Get the user profile that owns the product.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    /**
     * get fillable fields from model.
     *
     * @return string[]
     */
    public function getFillable()
    {
        return $this->fillable;
    }
}
