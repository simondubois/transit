<div class="container-fluid h-100 overflow-x-auto pb-3">
    <div class="row h-100 flex-nowrap">

        @foreach ($this->eventsByDay as $day => $events)
            <div class="h-100" style="width: 348px">
                <div class="card mh-100">

                    <div class="card-header text-center">
                        {{ $day }}
                    </div>

                    <div class="card-body overflow-y-auto pb-0 small">

                        @foreach ($events as $event)

                            <x-itinerary
                                :is-future="
                                    (
                                        $account->itineraries->firstWhere('nextEvent.id', $event->id) ?? $event
                                    )->end->isFuture()
                                "
                                :itinerary="$account->itineraries->firstWhere('nextEvent.id', $event->id)"
                            />
                            <x-event :event="$event" />

                        @endforeach

                        @if ($events->isNotEmpty())
                            <x-itinerary
                                :is-future="
                                    (
                                        $account->itineraries->firstWhere('previousEvent.id', $events->last()->id)
                                            ?? $events->last()
                                    )->end->isFuture()
                                "
                                :itinerary="$account->itineraries->firstWhere('previousEvent.id', $events->last()->id)"
                            />
                        @endif

                    </div>

                </div>
            </div>
        @endforeach

    </div>
</div>
