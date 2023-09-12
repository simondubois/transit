<?php

namespace Tests\Http;

use App\Models\Account;
use App\Models\Calendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class CalendarIndexTest extends TestCase
{
    /**
     * Test unknown account.
     */
    public function testUnknownAccount(): void
    {
        // given

        // when
        $response = $this->getJson('/' . Str::orderedUuid() . "/api/calendars");

        // then
        $response->assertNotFound();
    }

    /**
     * Test Ok response.
     */
    public function testOk(): void
    {
        // given
        $account1 = Account::factory()->createOne();
        $account2 = Account::factory()->createOne();
        $calendar1 = Calendar::factory()->for($account1)->createOne([
            'name' => 'Calendar 1',
            'url' => 'https://domain.com/ics1',
        ]);
        $calendar2 = Calendar::factory()->for($account1)->createOne([
            'name' => 'Calendar 2',
            'url' => 'https://domain.com/ics2',
        ]);
        Calendar::factory()->for($account2)->createOne();
        Calendar::factory()->for($account2)->createOne();

        // when
        $response = $this->getJson("/$account1->id/api/calendars");

        // then
        $response->assertOk();
        $response->assertJsonPath('data', [
            [
                'id' => $calendar1->id,
                'account_id' => $account1->id,
                'name' => 'Calendar 1',
                'url' => 'https://domain.com/ics1',
            ],
            [
                'id' => $calendar2->id,
                'account_id' => $account1->id,
                'name' => 'Calendar 2',
                'url' => 'https://domain.com/ics2',
            ],
        ]);
    }
}
