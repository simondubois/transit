<?php

namespace App\Jobs;

use App\Enums\LogStatus;
use App\Models\Account;
use App\Models\Itinerary;
use App\Models\Ride;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncRidesJob
{
    use CreateLog;
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
        $this->startedAt = now();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->delete();

        $this->dispatch();

        $this->createLog(LogStatus::Completed);
    }

    /**
     * Delete past rides which either belong to the current account or do not belong to any account.
     */
    protected function delete(): void
    {
        $this->createLog(LogStatus::DeletingRides);

        Ride::query()
            ->where(
                fn (Builder $query) => $query
                    ->orWhereRelation('account', 'accounts.id', $this->account->id)
                    ->orDoesntHave('account')
            )
            ->where('date', '<', today())
            ->delete();

        Ride::query()
            ->whereRelation('account', 'accounts.id', $this->account->id)
            ->whereRaw('DATEDIFF(`date`, CURDATE()) < DATEDIFF(`date`, `created_at`) / 2')
            ->delete();
    }

    /**
     * Dispatch SyncRideJob for each account's itineraries.
     */
    protected function dispatch(): void
    {
        $this->createLog(LogStatus::DispatchingRides);

        $this->account
            ->itineraries()
            ->getQuery()
            ->where('start', '>=', today())
            ->get()
            ->each(fn (Itinerary $itinerary) => rescue(fn () => SyncRideJob::dispatchSync($itinerary)));
    }

    /**
     * Holder to attach to newly created logs.
     */
    protected function logHolder(): ?Model
    {
        return $this->account;
    }
}
