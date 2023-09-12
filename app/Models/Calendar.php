<?php

namespace App\Models;

use App\Enums\CalendarSyncStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
