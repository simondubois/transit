@php
    $routes = [
        'agenda' => 'Kalendar',
    ];
@endphp

<nav class="navbar navbar-expand" data-bs-theme="dark" wire:poll="refreshAccount">
    <div class="container-fluid">

        <ul class="navbar-nav ms-auto me-0">

            @foreach ($routes as $routeName => $routeTitle)
                <li class="nav-item">
                    <a
                        @class(['nav-link', 'active' => Route::currentRouteName() === $routeName])
                        href="{{ route($routeName, $account) }}"
                        wire:navigate
                    >
                        {{ $routeTitle }}
                    </a>
                </li>
            @endforeach

        </ul>

        @if ($account->lastSyncedAt)
            <small class="navbar-text pe-2 text-nowrap text-info" wire:loading.remove wire:target="syncAccount">
                🔄 {{ $account->lastSyncedAt->shortRelativeDiffForHumans() }}
            </small>
        @endif

        <span class="navbar-text pe-2 text-nowrap text-warning" wire:loading wire:target="syncAccount">
            Synkar...
        </span>

        <button
            class="btn btn-outline-success btn-sm text-nowrap"
            wire:click="syncAccount"
            wire:loading.attr="disabled"
            wire:target="syncAccount"
        >
            Synka nu!
        </button>

    </div>
</nav>
