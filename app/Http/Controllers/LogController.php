<?php

namespace App\Http\Controllers;

use App\Http\Resources\LogResource;
use App\Models\Account;
use App\Models\Calendar;
use App\Models\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LogController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Account $account): AnonymousResourceCollection
    {
        return LogResource::collection(
            Log::query()
                ->where(function (Builder $builder) use ($account) {
                    $builder->orWhere('holder_type', null);
                    $builder->orWhereMorphRelation('holder', Account::class, 'id', $account->id);
                    $builder->orWhereMorphRelation('holder', Calendar::class, 'account_id', $account->id);
                })
                ->with('holder')
                ->latest()
                ->paginate($request->integer('perPage'))
        );
    }
}
