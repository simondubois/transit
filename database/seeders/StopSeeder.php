<?php

namespace Database\Seeders;

use App\Jobs\SyncStopsJob;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SyncStopsJob::dispatchSync();
    }
}
