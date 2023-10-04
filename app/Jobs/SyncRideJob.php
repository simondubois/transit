<?php

namespace App\Jobs;

use App\Enums\RideSyncStatus;
use App\Models\Ride;
use App\Models\Stop;
use App\Models\Itinerary;
use Exception;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Webmozart\Assert\Assert;

class SyncRideJob
{
    use Dispatchable;

    /**
     * Itinerary to sync
     */
    public Itinerary $itinerary;

    /**
     * Create a new job instance.
     */
    public function __construct(Itinerary $itinerary)
    {
        $this->itinerary = $itinerary;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->updateRideSyncStatus(RideSyncStatus::Idle, RideSyncStatus::Idle);

        if ($this->validate() !== true) {
            return;
        }

        $ridesData = $this->download();
        $this->delete();
        $this->create($ridesData);
    }

    /**
     * Has the current itinerary valid stops?
     */
    protected function validate(): bool
    {
        if ($this->itinerary->departure === '') {
            $this->updateRideSyncStatus(RideSyncStatus::NoDeparture, null);
        } elseif (is_null($this->itinerary->departureStop)) {
            $this->updateRideSyncStatus(RideSyncStatus::UnknownDeparture, null);
        }

        if ($this->itinerary->arrival === '') {
            $this->updateRideSyncStatus(null, RideSyncStatus::NoArrival);
        } elseif (is_null($this->itinerary->arrivalStop)) {
            $this->updateRideSyncStatus(null, RideSyncStatus::UnknownArrival);
        }

        if (is_null($this->itinerary->departureStop) || is_null($this->itinerary->arrivalStop)) {
            return false;
        }

        if ($this->itinerary->departureStop->is($this->itinerary->arrivalStop)) {
            $this->updateRideSyncStatus(RideSyncStatus::IdenticalStops, RideSyncStatus::IdenticalStops);

            return false;
        }

        return true;
    }

    /**
     * Download trip data from trafiklab API.
     *
     * @return Collection<int, array<mixed>>
     */
    protected function download(): Collection
    {
        Assert::isInstanceOf($this->itinerary->departureStop, Stop::class);
        Assert::isInstanceOf($this->itinerary->arrivalStop, Stop::class);

        $hasPreviousEvent = $this->itinerary->previousEvent !== null;

        try {
            $legsByRide = Http::get('https://api.resrobot.se/v2.1/trip', [
                'accessId' => config('trafiklab.rides.key'),
                'date' => ($hasPreviousEvent ? $this->itinerary->start : $this->itinerary->end)->toDateString(),
                'time' => ($hasPreviousEvent ? $this->itinerary->start : $this->itinerary->end)->toTimeString(),
                'searchForArrival' => $hasPreviousEvent ? 0 : 1,
                'originId' => $this->itinerary->departureStop->code,
                'destId' => $this->itinerary->arrivalStop->code,
                'numF' => 2,
                'format' => 'json',
            ])->throw()->json('Trip.*.LegList.Leg', []);
        } catch (Exception $exception) {
            $this->updateRideSyncStatus(RideSyncStatus::FailedDownload, RideSyncStatus::FailedDownload);
            throw $exception;
        }

        Assert::isArray($legsByRide);
        Assert::allIsArray($legsByRide);

        return collect(array_values($legsByRide))
            ->filter(
                fn (array $legs) => $this->itinerary->start->lessThanOrEqualTo(
                    data_get(Arr::first($legs), 'Origin.date') . ' ' . data_get(Arr::first($legs), 'Origin.time')
                )
            )
            ->filter(
                fn (array $legs) => $this->itinerary->end->greaterThanOrEqualTo(
                    data_get(Arr::last($legs), 'Destination.date')
                        . ' '
                        . data_get(Arr::last($legs), 'Destination.time')
                )
            )
            ->whenEmpty(function (Collection $legsByRide) {
                $this->updateRideSyncStatus(RideSyncStatus::NoRides, RideSyncStatus::NoRides);

                return $legsByRide;
            })
            ->take(2);
    }

    /**
     * Delete past rides which belong to the current account or which do not belong to any account.
     */
    protected function delete(): void
    {
        $this->itinerary->rides()->getQuery()->delete();
    }

    /**
     * Create a new ride for the provided itinerary and api result.
     * @param Collection<int, array<mixed>> $ridesData
     * @return Collection<int, Ride>
     */
    protected function create(Collection $ridesData): Collection
    {
        return $ridesData->map(function (array $legs): Ride {
            $ride = $this->itinerary->rides()->create(['legs' => $legs, 'date' => $this->itinerary->start]);
            $this->updateRideSyncStatus(RideSyncStatus::HasRides, RideSyncStatus::HasRides);

            return $ride;
        });
    }

    /**
     * Update itinerary's previous and next event statuses with the provided statuses.
     */
    protected function updateRideSyncStatus(?RideSyncStatus $forPreviousEvent, ?RideSyncStatus $forNextEvent): void
    {
        if ($forPreviousEvent !== null) {
            $this->itinerary->previousEvent?->update(['outgoing_ride_sync_status' => $forPreviousEvent]);
        }

        if ($forNextEvent !== null) {
            $this->itinerary->nextEvent?->update(['incoming_ride_sync_status' => $forNextEvent]);
        }
    }
}
