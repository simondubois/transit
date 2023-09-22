<?php

namespace App\Models;

use App\Enums\CalendarSyncStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property-read Account $account
 */
class Calendar extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'url',
    ];

    /**
     * Get the account that owns the calendar.
     * @return BelongsTo<Account, Calendar>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the events for the calendar.
     * @return HasMany<Event>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get all of the calendar's logs.
     * @return MorphMany<Log>
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(Log::class, 'holder');
    }
}
