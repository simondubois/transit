<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Event;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Webmozart\Assert\Assert;

class Agenda extends Component
{
    /**
     * Request account.
     */
    public ?Account $account = null;

    /**
     * Events to listen to.
     *
     * @var mixed
     */
    protected $listeners = ['account-updated' => '$refresh'];

    /**
     * Account's events grouped by day.
     *
     * @return Collection<int|string, Collection<int|string, Event>>
     */
    #[Computed]
    public function eventsByDay(): Collection
    {
        Assert::isInstanceOf($this->account, Account::class);

        /** @var Collection<int, Event> */
        $events = $this->account->calendars->pluck('events')->flatten(1)->sortBy('start');

        return $events->groupBy(fn (Event $event) => $event->start->isoFormat('dddd D MMMM'));
    }

    /**
     * Set data to make them available in layout.
     */
    public function render(): mixed
    {
        return view('livewire.agenda')
            ->layoutData(['account' => $this->account]);
    }
}
