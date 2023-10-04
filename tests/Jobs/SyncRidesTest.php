<?php

namespace Tests\Jobs;

use App\Enums\LogStatus;
use App\Enums\RideSyncStatus;
use App\Jobs\SyncRideJob;
use App\Jobs\SyncRidesJob;
use App\Models\Account;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\Itinerary;
use App\Models\Ride;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Webmozart\Assert\Assert;

class SyncRidesTest extends TestCase
{
    /**
     * Test.
     */
    public function test(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne();
        $event1 = Event::factory()->for($calendar)->createOne([
            'start' => today()->subDay()->hours(12),
            'end' => today()->subDay()->hours(14),
        ]);
        $incomingItinerary1 = Itinerary::query()->whereBelongsTo($event1, 'nextEvent')->first();
        $outgoingItinerary1 = Itinerary::query()->whereBelongsTo($event1, 'previousEvent')->first();
        Assert::isInstanceOf($incomingItinerary1, Itinerary::class);
        Assert::isInstanceOf($outgoingItinerary1, Itinerary::class);
        Ride::factory()->for($incomingItinerary1)->createOne(['date' => $event1->start]);
        Ride::factory()->for($outgoingItinerary1)->createOne(['date' => $event1->start]);
        $event2 = Event::factory()->for($calendar)->createOne([
            'start' => today()->hours(12),
            'end' => today()->hours(14),
        ]);
        $incomingItinerary2 = Itinerary::query()->whereBelongsTo($event2, 'nextEvent')->first();
        $outgoingItinerary2 = Itinerary::query()->whereBelongsTo($event2, 'previousEvent')->first();
        Assert::isInstanceOf($incomingItinerary2, Itinerary::class);
        Assert::isInstanceOf($outgoingItinerary2, Itinerary::class);
        $incomingRide2 = Ride::factory()->for($incomingItinerary2)->createOne(['date' => $event2->start]);
        $outgoingRide2 = Ride::factory()->for($outgoingItinerary2)->createOne(['date' => $event2->start]);
        Ride::factory()->for($incomingItinerary2)->createOne([
            'date' => $event2->start,
            'created_at' => today()->subDay(),
        ]);
        Ride::factory()->for($outgoingItinerary2)->createOne([
            'date' => $event2->start,
            'created_at' => today()->subDay(),
        ]);
        Bus::fake(SyncRideJob::class);
        Http::fake([]);
        $this->travelTo(now());

        // when
        SyncRidesJob::dispatchSync($account);

        // then
        Bus::assertDispatchedSync(fn (SyncRideJob $job) => $job->itinerary->is($incomingItinerary2), 1);
        Bus::assertDispatchedSync(fn (SyncRideJob $job) => $job->itinerary->is($outgoingItinerary2), 1);
        Bus::assertNotDispatchedSync(fn (SyncRideJob $job) => $job->itinerary->is($incomingItinerary1));
        Bus::assertNotDispatchedSync(fn (SyncRideJob $job) => $job->itinerary->is($outgoingItinerary1));
        $this->assertDatabaseHas('rides', ['id' => $incomingRide2->id]);
        $this->assertDatabaseHas('rides', ['id' => $outgoingRide2->id]);
        $this->assertDatabaseCount('rides', 2);
        $this->assertDatabaseHas('logs', [
            'holder_id' => $account->id,
            'holder_type' => Account::class,
            'job_type' => SyncRidesJob::class,
            'job_started_at' => now(),
            'status' => LogStatus::DeletingRides,
        ]);
        $this->assertDatabaseHas('logs', [
            'holder_id' => $account->id,
            'holder_type' => Account::class,
            'job_type' => SyncRidesJob::class,
            'job_started_at' => now(),
            'status' => LogStatus::DispatchingRides,
        ]);
        $this->assertDatabaseHas('logs', [
            'holder_id' => $account->id,
            'holder_type' => Account::class,
            'job_type' => SyncRidesJob::class,
            'job_started_at' => now(),
            'status' => LogStatus::Completed,
        ]);
    }
}
