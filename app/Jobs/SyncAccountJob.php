<?php

namespace App\Jobs;

use App\Enums\AccountSyncStatus;
use App\Jobs\Middleware\PreventOverlappingSyncAccountJob;
use App\Models\Account;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncAccountJob
{
    use Dispatchable;

    /**
     * Account to sync
     */
    public Account $account;

    /**
     * Create a new job instance.
     */
    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->before() !== true) {
            return;
        }

        try {
            $this->account->calendars->each([SyncEventsJob::class, 'dispatchSync']);
            SyncRidesJob::dispatchSync($this->account);
        } finally {
            $this->after();
        }
    }

    /**
     * Prevent job overlapping, return true if the job can continue.
     */
    protected function before(): bool
    {
        $this->account->refresh();

        if ($this->account->current_sync_status === AccountSyncStatus::Triggered) {
            return false;
        }

        if ($this->account->current_sync_status === AccountSyncStatus::Running) {
            $this->account->current_sync_status = AccountSyncStatus::Triggered;
            $this->account->save();

            return false;
        }

        $this->account->current_sync_status = AccountSyncStatus::Running;
        $this->account->save();

        return true;
    }
    /**
     * Dispatch the job again if a job overlapping was detected.
     */
    protected function after(): void
    {
        $this->account->refresh();

        if ($this->account->current_sync_status === AccountSyncStatus::Triggered) {
            $this->account->current_sync_status = AccountSyncStatus::Running;
            $this->account->save();
            SyncAccountJob::dispatchSync($this->account);
        }

        $this->account->current_sync_status = AccountSyncStatus::Idle;
        $this->account->save();
    }
}
