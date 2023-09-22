<?php

namespace Tests\Http;

use App\Enums\AccountSyncStatus;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountShowTest extends TestCase
{
    /**
     * Test unknown account.
     */
    public function testUnknownAccount(): void
    {
        // given

        // when
        $response = $this->getJson(Str::orderedUuid() . '/api/account');

        // then
        $response->assertNotFound();
    }

    /**
     * Test Ok response.
     */
    public function testOk(): void
    {
        // given
        $account = Account::factory()->createOne([
            'name' => 'Account',
            'default_location' => 'Default location',
            'current_sync_status' => AccountSyncStatus::Running,
        ]);

        // when
        $response = $this->getJson("/$account->id/api/account");

        // then
        $response->assertOk();
        $response->assertJsonPath('data', [
            'id' => $account->id,
            'name' => 'Account',
            'default_location' => 'Default location',
            'current_sync_status' => AccountSyncStatus::Running->value,
        ]);
    }
}
