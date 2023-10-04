<?php

namespace App\Enums;

enum RideSyncStatus: string
{
    case Idle = 'idle';
    case NoItinerary = 'no_itinerary';
    case NoDeparture = 'no_departure';
    case NoArrival = 'no_arrival';
    case UnknownDeparture = 'unknown_departure';
    case UnknownArrival = 'unknown_arrival';
    case IdenticalStops = 'identical_stops';
    case FailedDownload = 'failed_download';
    case NoRides = 'no_rides';
    case HasRides = 'has_rides';

    /**
     * Get the emoji.
     */
    public function emoji(): string
    {
        $emojis = [
            RideSyncStatus::Idle->value => 'ℹ️',
            RideSyncStatus::NoItinerary->value => '⚠️',
            RideSyncStatus::NoDeparture->value => '⚠️',
            RideSyncStatus::NoArrival->value => '⚠️',
            RideSyncStatus::UnknownDeparture->value => '⚠️',
            RideSyncStatus::UnknownArrival->value => '⚠️',
            RideSyncStatus::IdenticalStops->value => '⚠️',
            RideSyncStatus::FailedDownload->value => '💥',
            RideSyncStatus::NoRides->value => '⚠️',
            RideSyncStatus::HasRides->value => '✔️',
        ];

        return $emojis[$this->value];
    }

    /**
     * Get the name.
     */
    public function name(): string
    {
        $names = [
            RideSyncStatus::Idle->value => 'Väntar på synkronisering',
            RideSyncStatus::NoItinerary->value => 'Kan inte identifiera resplanen',
            RideSyncStatus::NoDeparture->value => 'Ingen avgång',
            RideSyncStatus::NoArrival->value => 'Ingen ankomst',
            RideSyncStatus::UnknownDeparture->value => 'Okänd avgång',
            RideSyncStatus::UnknownArrival->value => 'Okänd ankomst',
            RideSyncStatus::IdenticalStops->value => 'Identiska hållplatser',
            RideSyncStatus::FailedDownload->value => 'Synkroniseringsfel',
            RideSyncStatus::NoRides->value => 'Ingen åktur hittades',
            RideSyncStatus::HasRides->value => 'Åkturer hittades',
        ];

        return $names[$this->value];
    }

    /**
     * Get the variant.
     */
    public function variant(): string
    {
        $variants = [
            RideSyncStatus::Idle->value => 'info',
            RideSyncStatus::NoItinerary->value => 'warning',
            RideSyncStatus::NoDeparture->value => 'warning',
            RideSyncStatus::NoArrival->value => 'warning',
            RideSyncStatus::UnknownDeparture->value => 'warning',
            RideSyncStatus::UnknownArrival->value => 'warning',
            RideSyncStatus::IdenticalStops->value => 'warning',
            RideSyncStatus::FailedDownload->value => 'danger',
            RideSyncStatus::NoRides->value => 'warning',
            RideSyncStatus::HasRides->value => 'success',
        ];

        return $variants[$this->value];
    }
}
