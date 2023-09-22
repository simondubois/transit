<?php

namespace App\Enums;

enum LogStatus: string
{
    // \App\Jobs\SyncEventsJob
    case DownloadingEvents = 'downloading_events';
    case ParsingEvents = 'parsing_events';
    case DeletingEvents = 'deleting_events';
    case SavingEvents = 'saving_events';

    case Completed = 'completed';
}
