<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalendarRequest;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Account;
use App\Models\Calendar;
use App\Models\Event;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventController
{
    /**
     * Display a listing of the resource.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(Account $account, Calendar $calendar): AnonymousResourceCollection
    {
        return EventResource::collection(
            $calendar->events
        );
    }
}
