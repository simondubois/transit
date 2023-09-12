<?php

namespace Tests\Http;

use App\Enums\CalendarSyncStatus;
use App\Models\Account;
use App\Models\Calendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class CalendarShowTest extends TestCase
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
        $response = $this->getJson('/' . Str::orderedUuid() . "/api/calendars/$calendar->id");

        // then
        $response->assertNotFound();
    }

    /**
     * Test unknown calendar.
     */
    public function testUnknownCalendar(): void
    {
        // given
        $account = Account::factory()->createOne();

        // when
        $response = $this->getJson("/$account->id/api/calendars/1");

        // then
        $response->assertNotFound();
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
        $response = $this->getJson("/$account2->id/api/calendars/$calendar->id");

        // then
        $response->assertNotFound();
    }

    /**
     * Test Ok response.
     */
    public function testOk(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne([
            'name' => 'Calendar',
            'url' => 'https://domain.com/ics',
        ]);

        // when
        $response = $this->getJson("/$account->id/api/calendars/$calendar->id");

        // then
        $response->assertOk();
        $response->assertJsonPath('data', [
            'id' => $calendar->id,
            'account_id' => $account->id,
            'name' => 'Calendar',
            'url' => 'https://domain.com/ics',
        ]);
    }
}
