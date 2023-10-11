<div class="card mb-3">
    <div class="list-group text-center">

        @foreach ($rideSyncStatuses as $rideSyncStatus)
            <div @class([
                'list-group-item',
                "text-{$rideSyncStatus->variant()}" => $isFuture,
                'bg-transparent' => $isFuture !== true,
            ])>
                {{ $rideSyncStatus->emoji() }} {{ $rideSyncStatus->name() }}
            </div>
        @endforeach

    </div>
</div>
