<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalendarRequest;
use App\Http\Requests\Request;
use App\Http\Requests\StoreCalendarRequest;
use App\Http\Requests\UpdateCalendarRequest;
use App\Http\Resources\CalendarResource;
use App\Models\Account;
use App\Models\Calendar;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CalendarController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Account $account): AnonymousResourceCollection
    {
        return CalendarResource::collection(
            $account->calendars
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCalendarRequest $request, Account $account): CalendarResource
    {
        return new CalendarResource(
            $account->calendars()->create($request->validated())
        );
    }

    /**
     * Display the specified resource.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function show(Account $account, Calendar $calendar): CalendarResource
    {
        return new CalendarResource(
            $calendar
        );
    }

    /**
     * Update the specified resource in storage.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update(UpdateCalendarRequest $request, Account $account, Calendar $calendar): CalendarResource
    {
        return new CalendarResource(
            tap($calendar)->update($request->validated())
        );
    }

    /**
     * Remove the specified resource from storage.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function destroy(Account $account, Calendar $calendar): Response
    {
        $calendar->events()->getQuery()->delete();
        $calendar->logs()->getQuery()->delete();
        $calendar->delete();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
