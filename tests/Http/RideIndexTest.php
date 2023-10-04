<?php

namespace Tests\Http;

use App\Models\Account;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\Itinerary;
use App\Models\Ride;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;
use Webmozart\Assert\Assert;

class RideIndexTest extends TestCase
{
    /**
     * Test unknown account.
     */
    public function testUnknownAccount(): void
    {
        // given

        // when
        $response = $this->getJson('/' . Str::orderedUuid() . "/api/rides");

        // then
        $response->assertNotFound();
    }

    /**
     * Test Ok response.
     */
    public function testOk(): void
    {
        // given
        $this->travelTo(now());
        $account1 = Account::factory()->createOne();
        $account2 = Account::factory()->createOne();
        $calendar1 = Calendar::factory()->for($account1)->createOne();
        $calendar2 = Calendar::factory()->for($account2)->createOne();
        $event1 = Event::factory()->for($calendar1)->createOne([
            'start' => '2020-01-01 10:00:00',
            'end' => '2020-01-01 12:00:00',
        ]);
        $event2 = Event::factory()->for($calendar2)->createOne([
            'start' => '2020-01-01 14:00:00',
            'end' => '2020-01-01 16:00:00',
        ]);
        $itinerary1 = Itinerary::query()->whereBelongsTo($event1, 'nextEvent')->first();
        Assert::isInstanceOf($itinerary1, Itinerary::class);
        $itinerary2 = Itinerary::query()->whereBelongsTo($event2, 'nextEvent')->first();
        Assert::isInstanceOf($itinerary2, Itinerary::class);
        $ride1 = Ride::factory()->for($itinerary1)->createOne([
            'legs' => [[
                'Origin' => ['date' => '2020-01-01', 'name' => 'Lund Centralstation', 'time' => '08:00:00'],
                'Destination' => ['date' => '2020-01-01', 'name' => 'Malmö Centralstation', 'time' => '09:00:00'],
                'Product' => [['catCode' => '4', 'displayNumber' => '123']],
                'direction' => 'Köpenhamn Centralstation',
            ]],
            'date' => '2020-01-01',
        ]);
        $ride2 = Ride::factory()->for($itinerary1)->createOne([
            'legs' => [[
                'Origin' => ['date' => '2020-01-01', 'name' => 'Lund Centralstation', 'time' => '09:00:00'],
                'Destination' => ['date' => '2020-01-01', 'name' => 'Malmö Centralstation', 'time' => '10:00:00'],
                'Product' => [['catCode' => '1', 'displayNumber' => '456']],
                'direction' => 'Köpenhamn CPH',
            ]],
            'date' => '2020-01-02',
        ]);
        Ride::factory()->for($itinerary2)->createOne();
        Ride::factory()->for($itinerary2)->createOne();

        // when
        $response = $this->getJson("/$account1->id/api/rides");

        // then
        $response->assertOk();
        $response->assertJsonPath('data', [
            [
                'id' => $ride1->id,
                'name' => '🕓1 tim 🚆123',
                'legs' => [
                    '🚏 08:00 Lund Centralstation' . PHP_EOL
                        . '🚆 123 Köpenhamn Centralstation 🕓 1 tim' . PHP_EOL
                        . '🚏 09:00 Malmö Centralstation',
                ],
                'pre_margin' => null,
                'post_margin' => [
                    'y' => 0,
                    'm' => 0,
                    'd' => 0,
                    'h' => 1,
                    'i' => 0,
                    's' => 0,
                    'f' => 0,
                    'invert' => 0,
                    'days' => false,
                    'from_string' => false,
                ],
                'start' => '2020-01-01 08:00:00',
                'end' => '2020-01-01 09:00:00',
                'created_at' => now()->toDateTimeString(),
            ],
            [
                'id' => $ride2->id,
                'name' => '🕓1 tim 🚄456',
                'legs' => [
                    '🚏 09:00 Lund Centralstation' . PHP_EOL
                        . '🚄 456 Köpenhamn CPH 🕓 1 tim' . PHP_EOL
                        . '🚏 10:00 Malmö Centralstation',
                ],
                'pre_margin' => null,
                'post_margin' => [
                    'y' => 0,
                    'm' => 0,
                    'd' => 0,
                    'h' => 0,
                    'i' => 0,
                    's' => 0,
                    'f' => 0,
                    'invert' => 0,
                    'days' => false,
                    'from_string' => false,
                ],
                'start' => '2020-01-01 09:00:00',
                'end' => '2020-01-01 10:00:00',
                'created_at' => now()->toDateTimeString(),
            ],
        ]);
    }
}
