<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Webmozart\Assert\Assert;

/**
 * @property array<int, array<string, mixed>> $legs
 * @property Carbon $created_at
 * @property Collection<int, string> $printable_legs
 */
class Ride extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'legs' => 'array',
        'date' => 'date',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'legs',
        'date',
    ];

    /**
     * Get the ride's account.
     *
     * @return HasOneThrough<Account>
     */
    public function account(): HasOneThrough
    {
        return $this->hasOneThrough(Account::class, Itinerary::class, 'id', 'id', 'itinerary_id', 'account_id');
    }

    /**
     * Get the itinerary that owns the ride.
     *
     * @return BelongsTo<Itinerary, Ride>
     */
    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(Itinerary::class);
    }

    /**
     * Get name.
     */
    public function getNameAttribute(): string
    {
        return $this
            ->structured_legs
            ->map(fn (array $leg) => "{$leg['emoji']}{$leg['number']}")
            ->prepend('🕓' . $this->durationBetween($this->start, $this->end))
            ->implode(' ');
    }

    /**
     * Get legs.
     *
     * @return Collection<
     *  int,
     *  array{
     *      emoji: string,
     *      number: string,
     *      direction: string,
     *      departure: string,
     *      arrival: string,
     *      start: Carbon,
     *      end: Carbon
     *  }
     * >
     */
    public function getStructuredLegsAttribute(): Collection
    {
        return collect($this->legs)->map(function (array $leg) {
            $number = Arr::get($leg, 'Product.0.displayNumber', Arr::get($leg, 'dist') . 'm');
            $direction = Arr::get($leg, 'direction');
            $departure = Arr::get($leg, 'Origin.name');
            $arrival = Arr::get($leg, 'Destination.name');

            return [
                'emoji' => ['🚶', '🚄', '🚆', '🚌', '🚆', '🚇', '🚊', '🚌', '🚢', '🚕']
                    [Arr::get($leg, 'Product.0.catCode', 0)],
                'number' => is_string($number) ? $number : '',
                'direction' => is_string($direction) ? $direction : '',
                'departure' => is_string($departure) ? $departure : '',
                'arrival' => is_string($arrival) ? $arrival : '',
                'start' => Carbon::parse(Arr::get($leg, 'Origin.date') . ' ' . Arr::get($leg, 'Origin.time')),
                'end' => Carbon::parse(Arr::get($leg, 'Destination.date') . ' ' . Arr::get($leg, 'Destination.time')),
            ];
        });
    }

    /**
     * Get printable legs.
     *
     * @return Collection<int, string>
     */
    public function getPrintableLegsAttribute(): Collection
    {
        return $this->structured_legs->flatMap(function (array $leg, int $position) {
            $previousLeg = $this->structured_legs->get($position - 1);
            $departure = "🚏 {$leg['start']->toTimeString('minute')} {$leg['departure']}";
            $direction = "{$leg['emoji']} {$leg['number']} {$leg['direction']} "
                . '🕓 ' . $this->durationBetween($leg['start'], $leg['end']);
            $arrival = "🚏 {$leg['end']->toTimeString('minute')} {$leg['arrival']}";

            $printableLeg = collect([$departure . PHP_EOL . $direction . PHP_EOL . $arrival]);
            if ($previousLeg !== null) {
                $printableLeg->prepend("⏸️ " . $this->durationBetween($previousLeg['end'], $leg['start']));
            }

            return $printableLeg;
        });
    }

    /**
     * Get pre margin.
     */
    public function getPreMarginAttribute(): ?CarbonInterval
    {
        if (is_null($this->itinerary)) {
            return null;
        }

        if (is_null($this->itinerary->previousEvent)) {
            return null;
        }

        return $this->itinerary->start->diffAsCarbonInterval($this->start);
    }

    /**
     * Get post margin.
     */
    public function getPostMarginAttribute(): ?CarbonInterval
    {
        if (is_null($this->itinerary)) {
            return null;
        }

        if (is_null($this->itinerary->nextEvent)) {
            return null;
        }

        return $this->itinerary->end->diffAsCarbonInterval($this->end);
    }

    /**
     * Get start.
     */
    public function getStartAttribute(): Carbon
    {
        $first = $this->structured_legs->first();
        Assert::isArray($first);

        return $first['start'];
    }

    /**
     * Get end.
     */
    public function getEndAttribute(): Carbon
    {
        $last = $this->structured_legs->last();
        Assert::isArray($last);

        return $last['end'];
    }

    /**
     * Get iCal summary.
     */
    public function getIcalSummaryAttribute(): string
    {
        return collect()
            ->when(
                $this->pre_margin,
                fn (Collection $lines, CarbonInterval $margin) => $lines->push('⬆️' . $this->durationFor($margin))
            )
            ->when(
                $this->post_margin,
                fn (Collection $lines, CarbonInterval $margin) => $lines->push('⬇️' . $this->durationFor($margin))
            )
            ->push($this->name)
            ->implode(' ');
    }

    /**
     * Get iCal summary.
     */
    public function getIcalDescriptionAttribute(): string
    {
        return $this->printable_legs
            ->when(
                $this->pre_margin,
                fn (Collection $legs, CarbonInterval $margin) => $legs->prepend('⬆️ ' . $this->durationFor($margin))
            )
            ->when(
                $this->post_margin,
                fn (Collection $legs, CarbonInterval $margin) => $legs->push('⬇️ ' . $this->durationFor($margin))
            )
            ->push("🔄 {$this->created_at->toDateTimeString()}")
            ->implode(PHP_EOL . PHP_EOL);
    }

    /**
     * Format time duration between the two provided dates as string.
     */
    public function durationBetween(Carbon $from, Carbon $to): string
    {
        return $this->durationFor($from->diffAsCarbonInterval($to));
    }

    /**
     * Format time duration for the provided interval as string.
     */
    public function durationFor(CarbonInterval $interval): string
    {
        return str_replace(' ', ' ', $interval->forHumans(null, true, -1, $interval->isEmpty() ? 0 : null));
    }
}
