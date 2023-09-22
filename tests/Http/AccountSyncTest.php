<?php

namespace Tests\Http;

use App\Enums\AccountSyncStatus;
use App\Jobs\SyncAccountJob;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountSyncTest extends TestCase
{
    /**
     * Test unknown account.
     */
    public function testUnknownAccount(): void
    {
        // given
        Account::factory()->createOne();
        Bus::fake(SyncAccountJob::class);

        // when
        $response = $this->getJson(Str::orderedUuid() . "/api/account/sync");

        // then
        $response->assertNotFound();
        Bus::assertNotDispatchedSync(SyncAccountJob::class);
    }

    /**
     * Test ok response.
     */
    public function testOk(): void
    {
        // given
        $account = Account::factory()->createOne();
        Bus::fake(SyncAccountJob::class);

        // when
        $response = $this->getJson("/$account->id/api/account/sync");

        // then
        $response->assertNoContent();
        Bus::assertDispatchedSync(fn (SyncAccountJob $job) => $job->account->is($account));
    }
}
