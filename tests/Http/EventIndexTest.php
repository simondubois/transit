<?php

namespace Tests\Http;

use App\Enums\CalendarSyncStatus;
use App\Enums\RideSyncStatus;
use App\Models\Account;
use App\Models\Calendar;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventIndexTest extends TestCase
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
        $response = $this->getJson('/' . Str::orderedUuid() . "/api/calendars/$calendar->id/events");

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
        $response = $this->getJson("/$account->id/api/calendars/1/events");

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
        $response = $this->getJson("/$account2->id/api/calendars/$calendar->id/events");

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
        $calendar1 = Calendar::factory()->for($account)->createOne();
        $calendar2 = Calendar::factory()->for($account)->createOne();
        $event1 = Event::factory()->for($calendar1)->createOne([
            'name' => 'Event 1',
            'description' => 'Description 1',
            'location' => 'Location 1',
            'incoming_ride_sync_status' => RideSyncStatus::Idle,
            'outgoing_ride_sync_status' => RideSyncStatus::Idle,
            'start' => '2020-01-01 09:00:00',
            'end' => '2020-01-01 12:00:00',
        ]);
        $event2 = Event::factory()->for($calendar1)->createOne([
            'name' => 'Event 2',
            'description' => 'Description 2',
            'location' => 'Location 2',
            'incoming_ride_sync_status' => RideSyncStatus::Idle,
            'outgoing_ride_sync_status' => RideSyncStatus::Idle,
            'start' => '2020-01-01 14:00:00',
            'end' => '2020-01-01 17:00:00',
        ]);
        Event::factory()->for($calendar2)->createOne();
        Event::factory()->for($calendar2)->createOne();

        // when
        $response = $this->getJson("/$account->id/api/calendars/$calendar1->id/events");

        // then
        $response->assertOk();
        $response->assertJsonPath('data', [
            [
                'id' => $event1->id,
                'calendar_id' => $calendar1->id,
                'name' => 'Event 1',
                'description' => 'Description 1',
                'location' => 'Location 1',
                'incoming_ride_sync_status' => RideSyncStatus::Idle->value,
                'outgoing_ride_sync_status' => RideSyncStatus::Idle->value,
                'start' => '2020-01-01 09:00:00',
                'end' => '2020-01-01 12:00:00',
            ],
            [
                'id' => $event2->id,
                'calendar_id' => $calendar1->id,
                'name' => 'Event 2',
                'description' => 'Description 2',
                'location' => 'Location 2',
                'incoming_ride_sync_status' => RideSyncStatus::Idle->value,
                'outgoing_ride_sync_status' => RideSyncStatus::Idle->value,
                'start' => '2020-01-01 14:00:00',
                'end' => '2020-01-01 17:00:00',
            ],
        ]);
    }
}
