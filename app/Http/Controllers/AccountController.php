<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Jobs\SyncAccountJob;
use App\Models\Account;
use Illuminate\Http\Response;

class AccountController
{
    /**
     * Display the specified resource.
     */
    public function show(Account $account): AccountResource
    {
        return new AccountResource(
            $account
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccountRequest $request, Account $account): AccountResource
    {
        return new AccountResource(
            tap($account)->update($request->validated())
        );
    }

    /**
     * Sync the specified resource data.
     */
    public function sync(Account $account): Response
    {
        SyncAccountJob::dispatchSync($account);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
