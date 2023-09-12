<?php

namespace Tests\Http;

use App\Enums\CalendarSyncStatus;
use App\Models\Account;
use App\Models\Calendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class CalendarDeleteTest extends TestCase
{
    /**
     * Test unknown account.
     */
    public function testUnknownAccount(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne();

        // when
        $response = $this->deleteJson('/' . Str::orderedUuid() . "/api/calendars/$calendar->id");

        // then
        $response->assertNotFound();
        $this->assertDatabaseHas('calendars', ['id' => $calendar->id]);
    }

    /**
     * Test different account.
     */
    public function testDifferentAccount(): void
    {
        // given
        $account1 = Account::factory()->createOne();
        $account2 = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account1)->createOne();

        // when
        $response = $this->deleteJson("/$account2->id/api/calendars/$calendar->id");

        // then
        $response->assertNotFound();
        $this->assertDatabaseHas('calendars', ['id' => $calendar->id]);
    }

    /**
     * Test Ok response.
     */
    public function testOk(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne();

        // when
        $response = $this->deleteJson("/$account->id/api/calendars/$calendar->id");

        // then
        $response->assertNoContent();
        $this->assertDatabaseEmpty('calendars');
    }
}
