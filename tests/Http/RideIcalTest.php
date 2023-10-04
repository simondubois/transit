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

class RideIcalTest extends TestCase
{
    /**
     * Test unknown account.
     */
    public function testUnknownAccount(): void
    {
        // given

        // when
        $response = $this->getJson('/' . Str::orderedUuid() . "/api/rides/ical");

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
        Ride::factory()->for($itinerary1)->createOne([
            'legs' => [
                [
                    'Origin' => ['date' => '2020-01-01', 'name' => 'Lund Centralstation', 'time' => '08:00:00'],
                    'Destination' => ['date' => '2020-01-01', 'name' => 'Malmö Centralstation', 'time' => '09:00:00'],
                    'Product' => [['catCode' => '4', 'displayNumber' => '123']],
                    'direction' => 'Köpenhamn Centralstation',
                ],
            ],
            'date' => '2020-01-01',
        ]);
        Ride::factory()->for($itinerary1)->createOne([
            'legs' => [
                [
                    'Origin' => ['date' => '2020-01-01', 'name' => 'Lund Centralstation', 'time' => '09:00:00'],
                    'Destination' => ['date' => '2020-01-01', 'name' => 'Malmö Centralstation', 'time' => '10:00:00'],
                    'Product' => [['catCode' => '1', 'displayNumber' => '456']],
                    'direction' => 'Köpenhamn CPH',
                ],
            ],
            'date' => '2020-01-02',
        ]);
        Ride::factory()->for($itinerary2)->createOne();
        Ride::factory()->for($itinerary2)->createOne();
        $this->travelTo(now());

        // when
        $response = $this->getJson("/$account1->id/api/rides/ical");

        // then
        $response->assertOk();
        $content = $response->baseResponse->getContent();
        Assert::string($content);
        $uids = collect(explode("\r\n", $content))->filter(fn (string $line) => str_starts_with($line, 'UID:'));
        $response->assertContent(
            "BEGIN:VCALENDAR\r\n"
                . "PRODID:-//eluceo/ical//2.0/EN\r\n"
                . "VERSION:2.0\r\n"
                . "CALSCALE:GREGORIAN\r\n"
                . "BEGIN:VEVENT\r\n"
                . $uids->first() . "\r\n"
                . "DTSTAMP:" . now('UTC')->format('Ymd\THis\Z') . "\r\n"
                . "SUMMARY:⬇️1 tim 🕓1 tim 🚆123\r\n"
                . "DESCRIPTION:🚏 08:00 Lund Centralstation\\n🚆 123 Köpenhamn Centralst\r\n"
                . " ation 🕓 1 tim\\n🚏 09:00 Malmö Centralstation\\n\\n⬇️ 1 tim\\n\\\r\n"
                . " n🔄 " . now()->toDateTimeString() . "\r\n"
                . "DTSTART:20200101T080000\r\n"
                . "DTEND:20200101T090000\r\n"
                . "END:VEVENT\r\n"
                . "BEGIN:VEVENT\r\n"
                . $uids->last() . "\r\n"
                . "DTSTAMP:" . now('UTC')->format('Ymd\THis\Z') . "\r\n"
                . "SUMMARY:⬇️0 s 🕓1 tim 🚄456\r\n"
                . "DESCRIPTION:🚏 09:00 Lund Centralstation\\n🚄 456 Köpenhamn CPH 🕓\r\n"
                . "  1 tim\\n🚏 10:00 Malmö Centralstation\\n\\n⬇️ 0 s\\n\\n🔄 "
                    . substr(now()->toDateTimeString(), 0, 5) . "\r\n"
                . " " . substr(now()->toDateTimeString(), 5) . "\r\n"
                . "DTSTART:20200101T090000\r\n"
                . "DTEND:20200101T100000\r\n"
                . "END:VEVENT\r\n"
                . "END:VCALENDAR\r\n"
        );
    }
}
