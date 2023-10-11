<div class="col">

    @if ($ride->pre_margin)
        <div class="card mb-3">
            <ul class="list-group">
                <li
                    @class([
                        'list-group-item',
                        'py-4' => $hasLongerPreMargin,
                        'text-center',
                        'bg-transparent' => $isFuture !== true,
                        'text-danger' => $isFuture && $ride->pre_margin->totalMinutes < 5,
                        'text-warning' => $isFuture && $ride->pre_margin->totalMinutes < 10,
                        'text-success' => $isFuture && $ride->pre_margin->totalMinutes >= 10,
                    ])
                >
                    {{ $ride->durationFor($ride->pre_margin) }}
                </li>
            </ul>
        </div>
    @endif

    <a
        @class(['card', 'mb-3', 'border-secondary' => $isFuture, 'text-decoration-none', 'small'])
        href="#"
        x-on:click.prevent="
            ($event.target.classList.contains('card') ? $event.target : $event.target.closest('.card'))
                .firstElementChild.classList.toggle('d-none');
            ($event.target.classList.contains('card') ? $event.target : $event.target.closest('.card'))
                .lastElementChild.classList.toggle('d-none');
        "
    >

        <div
            @class([
                'card-body',
                'd-flex',
                'flex-nowrap',
                'flex-column' => $isOneOfMany,
                'justify-content-between',
                'align-items-center',
                'text-center',
            ])
        >

            <span @class(['badge', 'bg-secondary' => $isFuture])>
                {{ $ride->start->toTimeString('minute') }}
            </span>

            @foreach (explode(' ', $ride->name) as $word)
                <span>
                    {{ $word }}
                </span>
            @endforeach

            <span @class(['badge', 'bg-secondary' => $isFuture])>
                {{ $ride->end->toTimeString('minute') }}
            </span>

        </div>

        <ul class="list-group list-group-flush d-none">

            @foreach ($ride->printable_legs as $leg)
                <li class="list-group-item text-end text-break">
                    {!! nl2br(e($leg))!!}
                </li>
            @endforeach

        </ul>

    </a>

    @if ($ride->post_margin)
        <div class="card mb-3">
            <ul class="list-group">
                <li
                    @class([
                        'list-group-item',
                        'py-4' => $hasLongerPostMargin,
                        'text-center',
                        'bg-transparent' => $isFuture !== true,
                        'text-danger' => $isFuture && $ride->post_margin->totalMinutes < 5,
                        'text-warning' => $isFuture && $ride->post_margin->totalMinutes < 10,
                        'text-success' => $isFuture && $ride->post_margin->totalMinutes >= 10,
                    ])
                >
                    {{ $ride->durationFor($ride->post_margin) }}
                </li>
            </ul>
        </div>
    @endif

</div>
