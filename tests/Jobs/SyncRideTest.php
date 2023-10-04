<?php

namespace Tests\Jobs;

use App\Enums\LogStatus;
use App\Enums\RideSyncStatus;
use App\Jobs\SyncRideJob;
use App\Models\Account;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\Itinerary;
use App\Models\Ride;
use App\Models\Stop;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Webmozart\Assert\Assert;

class SyncRideTest extends TestCase
{
    /**
     * Test no departure.
     */
    public function testNoDeparture(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne();
        $event = Event::factory()->for($calendar)->createOne([
            'location' => '',
            'start' => '2020-01-01 12:00:00',
            'end' => '2020-01-01 14:00:00',
        ]);
        $itinerary = Itinerary::query()->whereBelongsTo($event, 'previousEvent')->first();
        Assert::isInstanceOf($itinerary, Itinerary::class);
        $ride = Ride::factory()->for($itinerary)->createOne();
        Http::fake([]);

        // when
        SyncRideJob::dispatchSync($itinerary);

        // then
        Http::assertNothingSent();
        $this->assertDatabaseCount('rides', 1);
        $this->assertDatabaseHas('rides', ['id' => $ride->id]);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'outgoing_ride_sync_status' => RideSyncStatus::NoDeparture,
        ]);
    }

    /**
     * Test no arrival.
     */
    public function testNoArrival(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne();
        $event = Event::factory()->for($calendar)->createOne([
            'location' => '',
            'start' => '2020-01-01 12:00:00',
            'end' => '2020-01-01 14:00:00',
        ]);
        $itinerary = Itinerary::query()->whereBelongsTo($event, 'nextEvent')->first();
        Assert::isInstanceOf($itinerary, Itinerary::class);
        $ride = Ride::factory()->for($itinerary)->createOne();
        Http::fake([]);

        // when
        SyncRideJob::dispatchSync($itinerary);

        // then
        Http::assertNothingSent();
        $this->assertDatabaseCount('rides', 1);
        $this->assertDatabaseHas('rides', ['id' => $ride->id]);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'incoming_ride_sync_status' => RideSyncStatus::NoArrival,
        ]);
    }

    /**
     * Test unknown departure.
     */
    public function testUnknownDeparture(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne();
        $event = Event::factory()->for($calendar)->createOne([
            'start' => '2020-01-01 12:00:00',
            'end' => '2020-01-01 14:00:00',
        ]);
        $itinerary = Itinerary::query()->whereBelongsTo($event, 'previousEvent')->first();
        Assert::isInstanceOf($itinerary, Itinerary::class);
        $ride = Ride::factory()->for($itinerary)->createOne();
        Http::fake([]);

        // when
        SyncRideJob::dispatchSync($itinerary);

        // then
        Http::assertNothingSent();
        $this->assertDatabaseCount('rides', 1);
        $this->assertDatabaseHas('rides', ['id' => $ride->id]);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'outgoing_ride_sync_status' => RideSyncStatus::UnknownDeparture,
        ]);
    }

    /**
     * Test unknown arrival.
     */
    public function testUnknownArrival(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne();
        $event = Event::factory()->for($calendar)->createOne([
            'start' => '2020-01-01 12:00:00',
            'end' => '2020-01-01 14:00:00',
        ]);
        $itinerary = Itinerary::query()->whereBelongsTo($event, 'nextEvent')->first();
        Assert::isInstanceOf($itinerary, Itinerary::class);
        $ride = Ride::factory()->for($itinerary)->createOne();
        Http::fake([]);

        // when
        SyncRideJob::dispatchSync($itinerary);

        // then
        Http::assertNothingSent();
        $this->assertDatabaseCount('rides', 1);
        $this->assertDatabaseHas('rides', ['id' => $ride->id]);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'incoming_ride_sync_status' => RideSyncStatus::UnknownArrival,
        ]);
    }

    /**
     * Test identical stops.
     */
    public function testIdenticalStops(): void
    {
        // given
        Stop::factory()->createOne(['name' => 'Malmö Centralstation', 'code' => '740000003']);
        $account = Account::factory()->createOne(['default_location' => 'Malmö Centralstation']);
        $calendar = Calendar::factory()->for($account)->createOne();
        $event = Event::factory()->for($calendar)->createOne([
            'location' => 'Malmö Centralstation',
            'start' => '2020-01-01 12:00:00',
            'end' => '2020-01-01 14:00:00',
        ]);
        $itinerary = Itinerary::query()->whereBelongsTo($event, 'nextEvent')->first();
        Assert::isInstanceOf($itinerary, Itinerary::class);
        $ride = Ride::factory()->for($itinerary)->createOne();
        Http::fake([]);

        // when
        SyncRideJob::dispatchSync($itinerary);

        // then
        Http::assertNothingSent();
        $this->assertDatabaseCount('rides', 1);
        $this->assertDatabaseHas('rides', ['id' => $ride->id]);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'incoming_ride_sync_status' => RideSyncStatus::IdenticalStops,
        ]);
    }

    /**
     * Test failed download.
     */
    public function testFailedDownload(): void
    {
        // given
        config(['trafiklab.rides.key' => 'invalid']);
        Stop::factory()->createMany([
            ['name' => 'Malmö Centralstation', 'code' => '740000003'],
            ['name' => 'Lund Centralstation', 'code' => '740000120'],
        ]);
        $account = Account::factory()->createOne(['default_location' => 'Lund Centralstation']);
        $calendar = Calendar::factory()->for($account)->createOne();
        $event = Event::factory()->for($calendar)->createOne([
            'location' => 'Malmö Centralstation',
            'start' => '2020-01-01 12:00:00',
            'end' => '2020-01-01 14:00:00',
        ]);
        $itinerary = Itinerary::query()->whereBelongsTo($event, 'nextEvent')->first();
        Assert::isInstanceOf($itinerary, Itinerary::class);
        $ride = Ride::factory()->for($itinerary)->createOne();
        Http::fake([]);

        // when
        $this->assertThrows(fn () => SyncRideJob::dispatchSync($itinerary));

        // then
        Http::assertSentCount(1);
        $this->assertDatabaseCount('rides', 1);
        $this->assertDatabaseHas('rides', ['id' => $ride->id]);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'incoming_ride_sync_status' => RideSyncStatus::FailedDownload,
        ]);
    }

    /**
     * Test no rides.
     */
    public function testNoRides(): void
    {
        // given
        Stop::factory()->createMany([
            ['name' => 'Malmö Centralstation', 'code' => '740000003'],
            ['name' => 'Lund Centralstation', 'code' => '740000120'],
        ]);
        $account = Account::factory()->createOne(['default_location' => 'Lund Centralstation']);
        $calendar = Calendar::factory()->for($account)->createOne();
        $event = Event::factory()->for($calendar)->createOne([
            'location' => 'Malmö Centralstation',
            'start' => '2020-01-01 12:00:00',
            'end' => '2020-01-01 14:00:00',
        ]);
        $itinerary = Itinerary::query()->whereBelongsTo($event, 'nextEvent')->first();
        Assert::isInstanceOf($itinerary, Itinerary::class);
        Ride::factory()->for($itinerary)->createOne();
        Http::fake(['*' => ['Trip' => []]]);

        // when
        SyncRideJob::dispatchSync($itinerary);

        // then
        Http::assertSentCount(1);
        $this->assertDatabaseCount('rides', 0);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'incoming_ride_sync_status' => RideSyncStatus::NoRides,
        ]);
    }

    /**
     * Test has rides.
     */
    public function testHasRides(): void
    {
        // given
        Stop::factory()->createMany([
            ['name' => 'Malmö Centralstation', 'code' => '740000003'],
            ['name' => 'Lund Centralstation', 'code' => '740000120'],
        ]);
        $account = Account::factory()->createOne(['default_location' => 'Lund Centralstation']);
        $calendar = Calendar::factory()->for($account)->createOne();
        $event = Event::factory()->for($calendar)->createOne([
            'location' => 'Malmö Centralstation',
            'start' => '2020-01-01 12:00:00',
            'end' => '2020-01-01 14:00:00',
        ]);
        $itinerary = Itinerary::query()->whereBelongsTo($event, 'nextEvent')->first();
        Assert::isInstanceOf($itinerary, Itinerary::class);
        Ride::factory()->for($itinerary)->createOne();
        $leg1 = [
            'Destination' => ['date' => '2020-01-01', 'time' => '09:00'],
            'Origin' => ['date' => '2020-01-01', 'time' => '08:00'],
            'Product' => ['name' => 'Länstrafik - Tåg 1215'],
        ];
        $leg2 = [
            'Destination' => ['date' => '2020-01-01', 'time' => '09:00'],
            'Origin' => ['date' => '2020-01-01', 'time' => '10:00'],
            'Product' => ['name' => 'Länstrafik - Tåg 1225'],
        ];
        Http::fake(['*' => ['Trip' => [['LegList' => ['Leg' => [$leg1]]], ['LegList' => ['Leg' => [$leg2]]]]]]);

        // when
        SyncRideJob::dispatchSync($itinerary);

        // then
        Http::assertSentCount(1);
        $this->assertDatabaseCount('rides', 2);
        $this->assertDatabaseHas('rides', [
            'legs' => json_encode([$leg1]),
            'date' => '2020-01-01',
        ]);
        $this->assertDatabaseHas('rides', [
            'legs' => json_encode([$leg2]),
            'date' => '2020-01-01',
        ]);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'incoming_ride_sync_status' => RideSyncStatus::HasRides,
        ]);
    }
}
