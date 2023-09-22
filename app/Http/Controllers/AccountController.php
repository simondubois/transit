<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;

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
}
