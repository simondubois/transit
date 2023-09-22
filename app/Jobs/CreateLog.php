<?php

namespace App\Jobs;

use App\Enums\LogStatus;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

trait CreateLog
{
    /**
     * When the job was started.
     */
    protected Carbon $startedAt;

    /**
     * Holder to attach to newly created logs.
     */
    abstract protected function logHolder(): ?Model;

    /**
     * Create a new log for the current job with the provided status.
     */
    protected function createLog(LogStatus $status): Log
    {
        $log = new Log([
            'job_type' => static::class,
            'job_started_at' => $this->startedAt,
            'status' => $status,
        ]);

        $holder = $this->logHolder();
        if ($holder instanceof Model) {
            $log->holder()->associate($holder);
        }

        $log->save();

        return $log;
    }
}
