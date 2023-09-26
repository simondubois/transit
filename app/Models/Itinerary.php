<?php

namespace App\Models;

use App\Enums\RideSyncStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property-read string $departure
 * @property-read string $arrival
 * @property-read \Carbon\Carbon $start
 * @property-read \Carbon\Carbon $end
 */
class Itinerary extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Get the arrival stop that owns the itinerary.
     * @return BelongsTo<Stop, Itinerary>
     */
    public function arrivalStop(): BelongsTo
    {
        return $this->belongsTo(Stop::class, 'arrival', 'name');
    }

    /**
     * Get the next event that owns the itinerary.
     * @return BelongsTo<Event, Itinerary>
     */
    public function nextEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the departure stop that owns the itinerary.
     * @return BelongsTo<Stop, Itinerary>
     */
    public function departureStop(): BelongsTo
    {
        return $this->belongsTo(Stop::class, 'departure', 'name');
    }

    /**
     * Get the previous event that owns the itinerary.
     * @return BelongsTo<Event, Itinerary>
     */
    public function previousEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the rides for the itinerary.
     * @return HasMany<Ride>
     */
    public function rides(): HasMany
    {
        return $this->hasMany(Ride::class);
    }

    /**
     * Get unique RideSyncStatuses related to the itineray's events.
     *
     * @return Collection<int, RideSyncStatus>
     */
    public function getRideSyncStatusesAttribute(): Collection
    {
        return collect([
            $this->previousEvent?->outgoing_ride_sync_status,
            $this->nextEvent?->incoming_ride_sync_status,
        ])
            ->filter()
            ->filter(fn (RideSyncStatus $rideSyncStatus) => $rideSyncStatus !== RideSyncStatus::Idle)
            ->whenEmpty(fn (Collection $rideSyncStatuses) => $rideSyncStatuses->push(RideSyncStatus::Idle))
            ->unique();
    }
}
