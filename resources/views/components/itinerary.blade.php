@if (is_null($itinerary))

    <x-ride-sync-statuses
        :is-future="$isFuture"
        :ride-sync-statuses="collect([App\Enums\RideSyncStatus::NoItinerary])"
    />

@elseif ($itinerary->rideSyncStatuses->contains(App\Enums\RideSyncStatus::HasRides))

    <div class="row flex-nowrap">

        @foreach ($itinerary->rides as $ride)
            <x-ride
                :has-longer-post-margin="
                    $ride->post_margin
                        && $itinerary->rides
                            ->where('post_margin.totalMinutes', '<', $ride->post_margin->totalMinutes)
                            ->isNotEmpty()
                "
                :has-longer-pre-margin="
                    $ride->pre_margin
                        && $itinerary->rides
                            ->where('pre_margin.totalMinutes', '<', $ride->pre_margin->totalMinutes)
                            ->isNotEmpty()
                "
                :is-future="$isFuture"
                :is-one-of-many="$itinerary->rides->count() > 1"
                :ride="$ride"
            />
        @endforeach

    </div>

@else

    <x-ride-sync-statuses
        :is-future="$isFuture"
        :ride-sync-statuses="$itinerary->rideSyncStatuses"
    />

@endif
