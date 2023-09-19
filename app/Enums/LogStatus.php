<?php

namespace App\Enums;

enum LogStatus: string
{
    // \App\Jobs\SyncEventsJob
    case DownloadingEvents = 'downloading_events';
    case ParsingEvents = 'parsing_events';
    case DeletingEvents = 'deleting_events';
    case SavingEvents = 'saving_events';

    // \App\Jobs\SyncStopsJob
    case DownloadingStops = 'downloading_stops';
    case ExtractingStops = 'extracting_stops';
    case ParsingStops = 'parsing_stops';
    case DeletingStops = 'deleting_stops';
    case CreatingStops = 'creating_stops';

    case Completed = 'completed';
}
