<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Account::create([
            'name' => 'Simon',
            'default_location' => decrypt(
                'eyJpdiI6IlMvM2NWVlNQdytYeXBOdnpRZzNBenc9PSIsInZhbHVlIjoiclpSQVpCUEY4N3RtcTJabEgy'
                    . 'VUJnQmd1L210R1ZmeXBEbEsrOWRnVFI1ND0iLCJtYWMiOiJkNTMwYTQ0NTEzZTU2ZGUyMmZlZj'
                    . 'lkOTVlZWVmZTk5NTM1ZWQ1YzFkY2NkZDczNmI1YzExMTVkZmYzZjM1N2NjIiwidGFnIjoiIn0='
            ),
        ]);

        Account::create([
            'name' => 'Delphine',
            'default_location' => decrypt(
                'eyJpdiI6IlMvM2NWVlNQdytYeXBOdnpRZzNBenc9PSIsInZhbHVlIjoiclpSQVpCUEY4N3RtcTJabEgy'
                    . 'VUJnQmd1L210R1ZmeXBEbEsrOWRnVFI1ND0iLCJtYWMiOiJkNTMwYTQ0NTEzZTU2ZGUyMmZlZj'
                    . 'lkOTVlZWVmZTk5NTM1ZWQ1YzFkY2NkZDczNmI1YzExMTVkZmYzZjM1N2NjIiwidGFnIjoiIn0='
            ),
        ]);
    }
}
