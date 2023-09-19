<?php

namespace App\Http\Controllers;

use App\Jobs\SyncStopsJob;
use App\Models\Account;
use Illuminate\Http\Response;

class StopController
{
    /**
     * Sync the specified resource data.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function sync(Account $account): Response
    {
        SyncStopsJob::dispatchSync();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
