<?php

namespace Tests\Jobs;

use App\Enums\AccountSyncStatus;
use App\Jobs\SyncAccountJob;
use App\Jobs\SyncEventsJob;
use App\Models\Account;
use App\Models\Calendar;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncAccountTest extends TestCase
{
    /**
     * Test idle account sync.
     */
    public function testIdleAccountSync(): void
    {
        // given
        $account = Account::factory()->createOne(['current_sync_status' => AccountSyncStatus::Idle]);
        $calendar1 = Calendar::factory()->for($account)->createOne();
        $calendar2 = Calendar::factory()->for($account)->createOne();
        Bus::fake([SyncEventsJob::class]);

        // when
        SyncAccountJob::dispatchSync($account);

        // then
        Bus::assertDispatchedSync(fn (SyncEventsJob $job) => $job->calendar->is($calendar1), 1);
        Bus::assertDispatchedSync(fn (SyncEventsJob $job) => $job->calendar->is($calendar2), 1);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'current_sync_status' => AccountSyncStatus::Idle,
        ]);
    }

    /**
     * Test running account sync.
     */
    public function testRunningAccountSync(): void
    {
        // given
        $account = Account::factory()->createOne(['current_sync_status' => AccountSyncStatus::Running]);
        Calendar::factory()->for($account)->createOne();
        Calendar::factory()->for($account)->createOne();
        Bus::fake([SyncEventsJob::class]);

        // when
        SyncAccountJob::dispatchSync($account);

        // then
        Bus::assertNotDispatchedSync(SyncEventsJob::class);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'current_sync_status' => AccountSyncStatus::Triggered,
        ]);
    }

    /**
     * Test triggered account sync.
     */
    public function testTriggeredAccountSync(): void
    {
        // given
        $account = Account::factory()->createOne(['current_sync_status' => AccountSyncStatus::Triggered]);
        Calendar::factory()->for($account)->createOne();
        Calendar::factory()->for($account)->createOne();
        Bus::fake([SyncEventsJob::class]);

        // when
        SyncAccountJob::dispatchSync($account);

        // then
        Bus::assertNotDispatchedSync(SyncEventsJob::class);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'current_sync_status' => AccountSyncStatus::Triggered,
        ]);
    }
}
