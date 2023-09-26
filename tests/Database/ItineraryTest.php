<?php

namespace Tests\Database;

use App\Models\Account;
use App\Models\Calendar;
use App\Models\Event;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ItineraryTest extends TestCase
{
    /**
     * Test itineraries MYSQL View.
     */
    public function test(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne();

        // when
        $events = $this->when($calendar);

        // then
        $this->then($account, $events);
    }

    /**
     * Create events.
     *
     * @return Collection<string, Event>
     */
    protected function when(Calendar $calendar): Collection
    {
        /** @var Collection<string, Event> */
        return Event::factory()->for($calendar)->createMany([
            '1' => [
                'location' => 'Location 1',
                'start' => today()->addDays(1)->hour(9),
                'end' => today()->addDays(1)->hour(18),
            ],
            '2A' => [
                'location' => 'Location 2A',
                'start' => today()->addDays(2)->hour(10),
                'end' => today()->addDays(2)->hour(12),
            ],
            '2B' => [
                'location' => 'Location 2B',
                'start' => today()->addDays(2)->hour(14),
                'end' => today()->addDays(2)->hour(17),
            ],
            '3A1' => [
                'location' => 'Location 3A1',
                'start' => today()->addDays(3)->hour(10),
                'end' => today()->addDays(3)->hour(11),
            ],
            '3A2' => [
                'location' => 'Location 3A2',
                'start' => today()->addDays(3)->hour(11),
                'end' => today()->addDays(3)->hour(12),
            ],
            '3B1' => [
                'location' => 'Location 3B1',
                'start' => today()->addDays(3)->hour(13),
                'end' => today()->addDays(3)->hour(14),
            ],
            '3B2' => [
                'location' => 'Location 3B2',
                'start' => today()->addDays(3)->hour(14),
                'end' => today()->addDays(3)->hour(15),
            ],
            '3C1' => [
                'location' => 'Location 3C1',
                'start' => today()->addDays(3)->hour(16),
                'end' => today()->addDays(3)->hour(17),
            ],
            '3C2' => [
                'location' => 'Location 3C2',
                'start' => today()->addDays(3)->hour(17),
                'end' => today()->addDays(3)->hour(18),
            ],
            '4A' => [
                'location' => 'Location 4A',
                'start' => today()->addDays(4)->hour(11),
                'end' => today()->addDays(4)->hour(13),
            ],
            '4B' => [
                'location' => 'Location 4B',
                'start' => today()->addDays(4)->hour(15),
                'end' => today()->addDays(5)->hour(11),
            ],
            '5' => [
                'location' => 'Location 5',
                'start' => today()->addDays(5)->hour(13),
                'end' => today()->addDays(5)->hour(15),
            ],
            '6' => [
                'location' => 'Location 6',
                'start' => today()->addDays(6)->startOfDay(),
                'end' => today()->addDays(6)->endOfDay(),
            ],
            '7A' => [
                'location' => 'Location 7A',
                'start' => today()->addDays(7)->hour(9),
                'end' => today()->addDays(7)->hour(14),
            ],
            '7B' => [
                'location' => 'Location 7B',
                'start' => today()->addDays(7)->hour(12),
                'end' => today()->addDays(7)->hour(17),
            ],
            '8A' => [
                'location' => 'Location 8A',
                'start' => today()->addDays(8)->hour(9),
                'end' => today()->addDays(8)->hour(18),
            ],
            '8B' => [
                'location' => 'Location 8B',
                'start' => today()->addDays(8)->hour(12),
                'end' => today()->addDays(8)->hour(14),
            ],
        ]);
    }

    /**
     * Assert Itinerary existence in database.
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @param Collection<string, Event> $events
     */
    protected function then(Account $account, Collection $events): void
    {
        $this->assertDatabaseCount('itineraries', 17);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => null,
            'next_event_id' => $events->get('1')?->id,
            'departure' => null,
            'arrival' => 'Location 1',
            'start' => null,
            'end' => today()->addDays(1)->hour(9),
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => $events->get('1')?->id,
            'next_event_id' => null,
            'departure' => 'Location 1',
            'arrival' => null,
            'start' => today()->addDays(1)->hour(18),
            'end' => null,
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => null,
            'next_event_id' => $events->get('2A')?->id,
            'departure' => null,
            'arrival' => 'Location 2A',
            'start' => null,
            'end' => today()->addDays(2)->hour(10),
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => $events->get('2A')?->id,
            'next_event_id' => $events->get('2B')?->id,
            'departure' => 'Location 2A',
            'arrival' => 'Location 2B',
            'start' => today()->addDays(2)->hour(12),
            'end' => today()->addDays(2)->hour(14),
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => $events->get('2B')?->id,
            'next_event_id' => null,
            'departure' => 'Location 2B',
            'arrival' => null,
            'start' => today()->addDays(2)->hour(17),
            'end' => null,
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => null,
            'next_event_id' => $events->get('3A1')?->id,
            'departure' => null,
            'arrival' => 'Location 3A1',
            'start' => null,
            'end' => today()->addDays(3)->hour(10),
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => $events->get('3A2')?->id,
            'next_event_id' => $events->get('3B1')?->id,
            'departure' => 'Location 3A2',
            'arrival' => 'Location 3B1',
            'start' => today()->addDays(3)->hour(12),
            'end' => today()->addDays(3)->hour(13),
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => $events->get('3B2')?->id,
            'next_event_id' => $events->get('3C1')?->id,
            'departure' => 'Location 3B2',
            'arrival' => 'Location 3C1',
            'start' => today()->addDays(3)->hour(15),
            'end' => today()->addDays(3)->hour(16),
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => $events->get('3C2')?->id,
            'next_event_id' => null,
            'departure' => 'Location 3C2',
            'arrival' => null,
            'start' => today()->addDays(3)->hour(18),
            'end' => null,
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => null,
            'next_event_id' => $events->get('4A')?->id,
            'departure' => null,
            'arrival' => 'Location 4A',
            'start' => null,
            'end' => today()->addDays(4)->hour(11),
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => $events->get('4A')?->id,
            'next_event_id' => $events->get('4B')?->id,
            'departure' => 'Location 4A',
            'arrival' => 'Location 4B',
            'start' => today()->addDays(4)->hour(13),
            'end' => today()->addDays(4)->hour(15),
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => $events->get('4B')?->id,
            'next_event_id' => $events->get('5')?->id,
            'departure' => 'Location 4B',
            'arrival' => 'Location 5',
            'start' => today()->addDays(5)->hour(11),
            'end' => today()->addDays(5)->hour(13),
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => $events->get('5')?->id,
            'next_event_id' => null,
            'departure' => 'Location 5',
            'arrival' => null,
            'start' => today()->addDays(5)->hour(15),
            'end' => null,
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => null,
            'next_event_id' => $events->get('7A')?->id,
            'departure' => null,
            'arrival' => 'Location 7A',
            'start' => null,
            'end' => today()->addDays(7)->hour(9),
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => $events->get('7B')?->id,
            'next_event_id' => null,
            'departure' => 'Location 7B',
            'arrival' => null,
            'start' => today()->addDays(7)->hour(17),
            'end' => null,
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => null,
            'next_event_id' => $events->get('8A')?->id,
            'departure' => null,
            'arrival' => 'Location 8A',
            'start' => null,
            'end' => today()->addDays(8)->hour(9),
        ]);

        $this->assertDatabaseHas('itineraries', [
            'account_id' => $account->id,
            'previous_event_id' => $events->get('8A')?->id,
            'next_event_id' => null,
            'departure' => 'Location 8A',
            'arrival' => null,
            'start' => today()->addDays(8)->hour(18),
            'end' => null,
        ]);
    }
}
