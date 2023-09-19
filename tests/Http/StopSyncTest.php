<?php

namespace Tests\Http;

use App\Jobs\SyncStopsJob;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Tests\TestCase;

class StopSyncTest extends TestCase
{
    /**
     * Test unknown account.
     */
    public function testUnknownAccount(): void
    {
        // given
        Account::factory()->createOne();
        Bus::fake(SyncStopsJob::class);

        // when
        $response = $this->getJson(Str::orderedUuid() . "/api/stops/sync");

        // then
        $response->assertNotFound();
        Bus::assertNotDispatchedSync(SyncStopsJob::class);
    }

    /**
     * Test ok response.
     */
    public function testOk(): void
    {
        // given
        $account = Account::factory()->createOne();
        Bus::fake(SyncStopsJob::class);

        // when
        $response = $this->getJson("/$account->id/api/stops/sync");

        // then
        $response->assertNoContent();
        Bus::assertDispatchedSync(SyncStopsJob::class);
    }
}
