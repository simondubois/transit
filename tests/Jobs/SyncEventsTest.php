<?php

namespace Tests\Jobs;

use App\Enums\LogStatus;
use App\Enums\RideSyncStatus;
use App\Jobs\SyncEventsJob;
use App\Models\Account;
use App\Models\Calendar;
use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncEventsTest extends TestCase
{
    /**
     * Test invalid url.
     */
    public function testInvalidUrl(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne(['url' => 'invalid']);
        $event = Event::factory()->for($calendar)->createOne();
        Http::fake([]);
        $this->travelTo(now());

        // when
        $this->assertThrows(fn () => SyncEventsJob::dispatchSync($calendar));

        // then
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
        ]);
        $this->assertDatabaseCount('events', 1);
        $this->assertDatabaseHas('logs', [
            'holder_id' => $calendar->id,
            'holder_type' => Calendar::class,
            'job_type' => SyncEventsJob::class,
            'job_started_at' => now(),
            'status' => LogStatus::DownloadingEvents,
        ]);
        $this->assertDatabaseMissing('logs', ['status' => LogStatus::ParsingEvents]);
    }

    /**
     * Test unreadable stream.
     */
    public function testUnreadableStream(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne([
            'url' => 'https://www.google.com/',
        ]);
        $event = Event::factory()->for($calendar)->createOne();
        Http::fake([]);
        $this->travelTo(now());

        // when
        $this->assertThrows(fn () => SyncEventsJob::dispatchSync($calendar));

        // then
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
        ]);
        $this->assertDatabaseCount('events', 1);
        $this->assertDatabaseHas('logs', [
            'holder_id' => $calendar->id,
            'holder_type' => Calendar::class,
            'job_type' => SyncEventsJob::class,
            'job_started_at' => now(),
            'status' => LogStatus::ParsingEvents,
        ]);
        $this->assertDatabaseMissing('logs', ['status' => LogStatus::DeletingEvents]);
    }

    /**
     * Test completed.
     */
    public function testCompleted(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne([
            'url' => 'https://www.officeholidays.com/ics-local-name/sweden',
        ]);
        Http::fake([
            '*' => Http::response(
                'BEGIN:VCALENDAR' . PHP_EOL
                    . 'BEGIN:VEVENT' . PHP_EOL
                    . 'SUMMARY:Name' . PHP_EOL
                    . 'DESCRIPTION:Description' . PHP_EOL
                    . 'DTSTART:20500101T090000Z' . PHP_EOL
                    . 'DTEND:20500101T120000Z' . PHP_EOL
                    . 'LOCATION:Location' . PHP_EOL
                    . 'END:VEVENT' . PHP_EOL
                    . 'END:VCALENDAR' . PHP_EOL
            ),
        ]);
        $event = Event::factory()->for($calendar)->createOne();
        $this->travelTo(now());

        // when
        SyncEventsJob::dispatchSync($calendar);

        // then
        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
        ]);
        $this->assertDatabaseCount('events', 1);
        $this->assertDatabaseHas('events', [
            'name' => 'Name',
            'description' => 'Description',
            'location' => 'Location',
            'incoming_ride_sync_status' => RideSyncStatus::Idle,
            'outgoing_ride_sync_status' => RideSyncStatus::Idle,
            'start' => '2050-01-01 09:00:00',
            'end' => '2050-01-01 12:00:00',
        ]);
        $this->assertDatabaseHas('logs', [
            'holder_id' => $calendar->id,
            'holder_type' => Calendar::class,
            'job_type' => SyncEventsJob::class,
            'job_started_at' => now(),
            'status' => LogStatus::Completed,
        ]);
    }
}
