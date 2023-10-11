<div @class(['card', 'mb-3', 'border-primary' => $event->end->isFuture()])>
    <div class="card-body">

        <div class="d-flex flex-nowrap align-items-center justify-content-between">

            <span @class(['badge', 'bg-primary' => $event->end->isFuture()])>
                {{ $event->start->isoFormat('LT') }}
            </span>

            <span class="text-center">
                {{ $event->name }}
            </span>

            <span @class(['badge', 'bg-primary' => $event->end->isFuture()])>
                {{ $event->end->isoFormat('LT') }}
            </span>

        </div>

        @if ($event->location)
            <div class="text-center text-light-emphasis">
                {{ $event->location }}
            </div>
        @endif

    </div>

</div>
