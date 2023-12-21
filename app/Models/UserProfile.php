<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const CREATED_AT = null;

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'dob',
        'timezone',
    ];

    /**
     * Get the user's full name.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => sprintf('%s %s', $attributes['first_name'], $attributes['last_name']),
        );
    }

    /**
     * Scope a query to only include popular users.
     */
    public function scopeBirthdayTimeNow(Builder $query): void
    {
        $month = now(config('app.timezone'))->format('m');
        $day = now(config('app.timezone'))->format('d');

        $query->whereDay('dob', $day)->whereMonth('dob', $month);
    }

    /**
     * Get the related user's for this user profile's
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id');
    }
}
