<?php

namespace App\Enums;

enum AccountSyncStatus: string
{
    case Idle = 'idle';
    case Running = 'running';
    case Triggered = 'triggered';
}
