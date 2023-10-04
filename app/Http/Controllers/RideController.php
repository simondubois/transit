<?php

namespace App\Http\Controllers;

use App\Http\Resources\RideResource;
use App\Models\Account;
use App\Models\Ride;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RideController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Account $account): AnonymousResourceCollection
    {
        return RideResource::collection(
            $account->rides->load('itinerary')
        );
    }

    /**
     * Generate an iCal file of the resource.
     */
    public function ical(Account $account): Response
    {
        $events = $account->rides->map(
            fn (Ride $ride) => (new Event())
                ->setSummary($ride->icalSummary)
                ->setDescription($ride->icalDescription)
                ->setOccurrence(new TimeSpan(new DateTime($ride->start, false), new DateTime($ride->end, false)))
        );

        return response(
            (new CalendarFactory())->createCalendar(new Calendar($events->all())),
            Response::HTTP_OK,
            ['Content-Type' => 'text/calendar']
        );
    }
}
