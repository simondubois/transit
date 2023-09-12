<?php

namespace App\Models;

use App\Enums\AccountSyncStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The model's default values for attributes.
     *
     * @var array<mixed>
     */
    protected $attributes = [
        'current_sync_status' => AccountSyncStatus::Idle,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'current_sync_status' => AccountSyncStatus::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'default_location',
    ];

    /**
     * Get the calendars for the account.
     * @return HasMany<Calendar>
     */
    public function calendars(): HasMany
    {
        return $this->hasMany(Calendar::class);
    }
}
