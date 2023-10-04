<?php

namespace App\Models;

use App\Enums\AccountSyncStatus;
use App\Enums\LogStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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

    /**
     * Get the itineraries for the account.
     * @return HasMany<Itinerary>
     */
    public function itineraries(): HasMany
    {
        return $this->hasMany(Itinerary::class);
    }

    /**
     * Get all of the account's logs.
     * @return MorphMany<Log>
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(Log::class, 'holder');
    }

    /**
     * Get all of the rides for the account.
     * @return HasManyThrough<Ride>
     */
    public function rides(): HasManyThrough
    {
        return $this->hasManyThrough(Ride::class, Itinerary::class);
    }

    /**
     * Get the last synced date.
     */
    public function getLastSyncedAtAttribute(): ?Carbon
    {
        return $this->logs()
            ->getQuery()
            ->where('status', LogStatus::Completed)
            ->latest('job_started_at')
            ->first()
            ?->job_started_at;
    }
}
