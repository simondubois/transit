<?php

namespace Database\Seeders;

use App\Jobs\SyncAccountJob;
use App\Models\Account;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AccountSeeder::class);
        $this->call(CalendarSeeder::class);

        Account::all()->each(fn (Account $account) => SyncAccountJob::dispatchSync($account));
    }
}
