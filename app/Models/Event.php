<?php

namespace App\Models;

use App\Enums\RideSyncStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array<mixed>
     */
    protected $attributes = [
        'incoming_ride_sync_status' => RideSyncStatus::Idle,
        'outgoing_ride_sync_status' => RideSyncStatus::Idle,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'incoming_ride_sync_status' => RideSyncStatus::class,
        'outgoing_ride_sync_status' => RideSyncStatus::class,
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'location',
        'incoming_ride_sync_status',
        'outgoing_ride_sync_status',
        'start',
        'end',
    ];

    /**
     * Get the calendar that owns the event.
     * @return BelongsTo<Calendar, Event>
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }
}
