<?php

namespace App\Jobs;

use App\Enums\LogStatus;
use App\Models\Calendar;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Sabre\VObject\Reader;
use Sabre\VObject\Component\VEvent;
use Webmozart\Assert\Assert;

class SyncEventsJob
{
    use CreateLog;
    use Dispatchable;

    /**
     * Calendar to sync
     */
    public Calendar $calendar;

    /**
     * Create a new job instance.
     */
    public function __construct(Calendar $calendar)
    {
        $this->calendar = $calendar;
        $this->startedAt = now();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $icalContent = $this->download();
        $events = $this->parse($icalContent);

        $this->delete();
        $this->create($events);

        $this->createLog(LogStatus::Completed);
    }

    /**
     * Download iCal feed from Calendar's url.
     */
    protected function download(): string
    {
        $this->createLog(LogStatus::DownloadingEvents);

        return Http::get($this->calendar->url)
            ->throw()
            ->body();
    }

    /**
     * Parse the provided iCal data into Events.
     *
     * @return Collection<int, Event>
     */
    public function parse(string $icalContent): Collection
    {
        $this->createLog(LogStatus::ParsingEvents);

        /** @var array<int, VEvent> */
        $vEvents = Reader::read($icalContent)->VEVENT;
        Assert::allIsInstanceOf($vEvents, VEvent::class); // @phpstan-ignore-line instanceof.alwaysTrue

        return collect($vEvents)
            ->map(fn (VEvent $vEvent) => new Event([
                'name' => $vEvent->SUMMARY?->getValue(), // @phpstan-ignore-line method.nonObject
                'description' => $vEvent->DESCRIPTION?->getValue() ?? '', // @phpstan-ignore-line method.nonObject
                'location' => $vEvent->LOCATION?->getValue() ?? '', // @phpstan-ignore-line method.nonObject
                'start' => Carbon::instance($vEvent->DTSTART->getDateTime()), // @phpstan-ignore-line method.nonObject
                'end' => Carbon::instance($vEvent->DTEND->getDateTime())->min( // @phpstan-ignore-line method.nonObject
                    Carbon::instance($vEvent->DTSTART->getDateTime()) // @phpstan-ignore-line method.nonObject
                        ->endOfDay()
                ),
            ]))
            ->where('end', '>', today());
    }

    /**
     * Delete all existing Calendar's Events.
     */
    protected function delete(): void
    {
        $this->createLog(LogStatus::DeletingEvents);

        $this->calendar->events()->getQuery()->delete();
    }

    /**
     * Create new Events from the provided collection.
     *
     * @param Collection<int, Event> $events
     */
    protected function create(Collection $events): void
    {
        $this->createLog(LogStatus::SavingEvents);

        $this->calendar->events()->saveMany($events);
    }

    /**
     * Holder to attach to newly created logs.
     */
    protected function logHolder(): ?Model
    {
        return $this->calendar;
    }
}
