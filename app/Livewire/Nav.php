<?php

namespace App\Livewire;

use App\Jobs\SyncAccountJob;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Livewire\Component;
use Webmozart\Assert\Assert;

class Nav extends Component
{
    /**
     * Nav account.
     */
    public ?Account $account = null;

    /**
     * Account's lastSyncedAt.
     */
    public ?Carbon $lastSyncedAt = null;

    /**
     * Set properties to make them available in component.
     */
    public function mount(): void
    {
        Assert::isInstanceOf($this->account, Account::class);

        $this->lastSyncedAt = $this->account->lastSyncedAt;
    }

    /**
     * Refresh account.
     */
    public function refreshAccount(): void
    {
        Assert::isInstanceOf($this->account, Account::class);

        if ($this->account->lastSyncedAt !== null) {
            if (is_null($this->lastSyncedAt) || $this->account->lastSyncedAt->notEqualTo($this->lastSyncedAt)) {
                $this->dispatch('account-updated');
                $this->lastSyncedAt = $this->account->lastSyncedAt;
            }
        }
    }

    /**
     * Sync account.
     */
    public function syncAccount(): void
    {
        Assert::isInstanceOf($this->account, Account::class);

        SyncAccountJob::dispatchSync($this->account);

        $this->dispatch('account-updated');
    }
}
