<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Webmozart\Assert\Assert;

class CalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createSimonsCalendars();
        $this->createDelphinesCalendars();
    }

    public function createSimonsCalendars(): void
    {
        $account = Account::firstWhere('name', 'Simon');
        Assert::isInstanceOf($account, Account::class);

        $account->calendars()->create([
            'name' => decrypt(
                'eyJpdiI6Ii9iUW52Q1NySmQvT1RyR3JlbzAweFE9PSIsInZhbHVlIjoiOXBXTWxUTHpWMFF2MGhuUUZZ'
                    . 'KzRza1U2RjBUMlZUQzZ3bzNCYmpRVGtiWT0iLCJtYWMiOiJhYjQ4MjA4OGEwNzljNDc4Y2U2MG'
                    . 'VlNGEyYTgxZDNlZTViYzUzNDM4ZGRiNDM0Y2Q0M2Q4NjIyNmVhNGRjNDk5IiwidGFnIjoiIn0='
            ),
            'url' => decrypt(
                'eyJpdiI6IjZRVUNSOTYxTmcwZ0FmdzBJd1JKcEE9PSIsInZhbHVlIjoic3RjZ1VSbUZuZmVWYlZ0RmxRRVAwMEVmVG5JL2ZQdF'
                    . 'NKcHd5NVJJYmdES05PQkV2ZS9pcUlNVVZZd3pzR2xTT1dYSlFTazlUdWZpNmtpR2JRWUFMTUovU1U2dmFDcEpTUTB2Z1'
                    . 'ZFMjRiTjRxd1dOZ3B4MnkweithQ3ZISW9qRVFsa0FwZG5FbEs0VE5tQk44eTQ1Q0JBPT0iLCJtYWMiOiIyMThiZjRmYz'
                    . 'BkMWQ3Y2Y4ZWM3MzBjNTcyNDE4OWE1MWQ1MzkxOTYwYTMxN2Q5NThiNWU5MDg5Nzk3MDg2MTU5IiwidGFnIjoiIn0='
            ),
        ]);

        $account->calendars()->create([
            'name' => decrypt(
                'eyJpdiI6Im1XcGRlMS9WZ3hYb0IvcGl6REIxYWc9PSIsInZhbHVlIjoibFZuSEZjcEx0dHExdHdIbWha'
                    . 'dWJLMDd5a1g3bG85aFpyZHdtT0p2aHNyaz0iLCJtYWMiOiIxMWE5MzlkMDIwMDNlNjM2NmYyNm'
                    . 'U1NDhhM2I5OWRlZWVhMmJiNGNhZGU5YzUyMzVjMDkwNzM5MTU3YzE2NTc3IiwidGFnIjoiIn0='
            ),
            'url' => decrypt(
                'eyJpdiI6Im5mcTJJWXJkeXU1VFExSDgvajFxU3c9PSIsInZhbHVlIjoiVjJ2bGE2RkxSVTJQaXJVN2c4ekM2UXN0b3YvbWFjdU'
                    . 'grbHowNlR1bHZsQ1piWnVGcGppbVRXN3c1ck1haFRNL3JDeGhkalNPMkVBVzNxTmRBbzFHaDNYMzlveGt2ZEROcFBWVH'
                    . 'dURXNHcjRVWlRxWnRRaWo2eTVNQ20wdVFxTGNEQitLaHFTczErYktiMGNBLzdzNk9RPT0iLCJtYWMiOiJlNDc1NWJlZD'
                    . 'Q5YzJkYzM1ZTQ3MTkyZmYwNTI5ZmFlYzFkMTI2NDRiOWM3MjIyMTU2M2EzMzM4ZGJiNDBjNTQwIiwidGFnIjoiIn0='
            ),
        ]);
    }

    public function createDelphinesCalendars(): void
    {
        $account = Account::firstWhere('name', 'Delphine');
        Assert::isInstanceOf($account, Account::class);

        $account->calendars()->create([
            'name' => decrypt(
                'eyJpdiI6IldGN05VZ0RqbEtYQ3RTNUpvOUVuUGc9PSIsInZhbHVlIjoiNm1ac3pucVpBUTBzdXBpQWN5L0V'
                    . 'SS1REcTdwM0pRSXNhb0p0Qmd5aG9UZXpIOXdkczhlbnUrbnVDeTVkOVZxSTYvYzB3R0l2RkhWbk5u'
                    . 'bEFTbVZEalRIR0lueGpDSTIwT1ZXcVhkQUpCYlU9IiwibWFjIjoiYjgwZjE1ZGVjMTFiMjBlYzI5N'
                    . 'TdlMmU0MWNhZDM5NjU3ZTZlMWMyNzA0ODAyYTVlNmQ4YWQ4ZTU5MTQ3MGZkMCIsInRhZyI6IiJ9'
            ),
            'url' => decrypt(
                'eyJpdiI6Im5EaWQ2anhEMFF4SDJZa2FtRU96U0E9PSIsInZhbHVlIjoiWkZRcDB4REVocy9wZ1JSRVRubGxrMW1RZWdwL0loZF'
                    . 'JNMThaREFweHBSQzJXM3RNVVI1aStnMGREckFCY0dsVTJrR1owV1lLb1NSMGVVNitrZXRocWE1WkY4dHloK1p4QTQ1Q0'
                    . 'ZFS1NSSXUvcDRuMG9rNWlrcUFreTd6TEoxTi9YVW90WUZsWVJFcndMYjBlTFNLZW53PT0iLCJtYWMiOiIxMDIzMDY2Ym'
                    . 'MzNWRkZjUxOTVjZGM5MzU3ZjAyZDJiZjVlOTdkMzVkYzM2OWU5NzZkZGQyNDcxMDk5ZDNjYWY1IiwidGFnIjoiIn0='
            ),
        ]);

        $account->calendars()->create([
            'name' => decrypt(
                'eyJpdiI6InV6UmtwcFQ5OGk3WkJUZXJUTGJwV1E9PSIsInZhbHVlIjoiOEpZL0NHSXBVVDg3bWhKSzZpdEc'
                    . 'zUWdHck55RG5jamVWVHRUS3preS9hclZmSUdJeHpKSXZxTkFxWFdVcDdJbk9HZFV2ZS9YOEcyeVZm'
                    . 'SEY5UTBZNmc3UVBuMVVVakMzNGlEdlJJOHh3NFk9IiwibWFjIjoiNmIyODcwNTExMjk1N2Y5MmQ5M'
                    . '2MzNWY0OTZiZWViZjljM2NkMzkwNzk5YzQ1NDc4ZTgxM2I5Mzg0NjVjODkyYyIsInRhZyI6IiJ9'
            ),
            'url' => decrypt(
                'eyJpdiI6IkFKS2hIYzBxMk5kOHM1TkYwZEZ5cmc9PSIsInZhbHVlIjoiK0lmTWdqeVhKclR5N0hnQWZNOThCSFNaRTU3WFlLdz'
                    . 'c3aFg1UTUyaUc1TE10UW9Jb292YVo3UWNIN2pKNXdBME5JQWM0OGV0NE9vWjRQK1h5R2RRQWZOSXh0RkM1azRyVnNuUm'
                    . 'FnR0FsQ2ZrY0hCUjhoRlJtUm9MeGlCdkFmL3IzVDNGQjhHc0pvTExaMW05QVd6OURBPT0iLCJtYWMiOiIyMTQyMWZjYm'
                    . 'E1NGE3YmQzMzA4Yzk5YWExMjY1ZTlhOWU4MGRiNjliNGY2OTlkMTBmZjVmYzBiZDEzMWY5ZjUyIiwidGFnIjoiIn0='
            ),
        ]);

        $account->calendars()->create([
            'name' => decrypt(
                'eyJpdiI6IjltWGMwVWlzVU1yOHFIdXBkMldHSHc9PSIsInZhbHVlIjoiTXFBZzdtK1BlWEVtUk9XTEFhNTR'
                    . 'vdlprUXNLSmZkVi81ZDBFZFpYNnErZ013dkZrUWd5ZVVLaE51SmZ5NC93ZDNuRU4rVlVUL0VmK0RC'
                    . 'ZDlURjZoMFZscFN2VmM3U05Sblc1eVFvMWczMTg9IiwibWFjIjoiZGJlYzBhMjc2OThhMjA2MjY5Y'
                    . 'mEyZDJhMGE3MTgxNGIyZDk2NThlM2QyMjUxMjgxNThhNDA2OGRlNDk4ZTcwNyIsInRhZyI6IiJ9'
            ),
            'url' => decrypt(
                'eyJpdiI6Ik9BMFZPWjJhNHVZazkrckdwd0hXRlE9PSIsInZhbHVlIjoiVkEwNnlzR1FGWGdZdE1ncXdxclhxT09PQi9SYlBldT'
                    . 'NiR3ZKZlptSjIxK0o1ZHZiT0NGc0d4Rlp0TGlBV1lPTC8rNWtYYnpmWjl2bGw0emRxKzNmNlZIMThHM2lCbzJaa2R2RD'
                    . 'dOeFhDejMvNG5HaXBTWGdVVThydmJ0WnM3em84UTErV2pkbXJwMUtmUTNkdUthRFF3PT0iLCJtYWMiOiJkYjg1OTIxMD'
                    . 'diNzI0ZmRlMmUxM2U0ZTcwZmI2MTY2MDg3MTY1ZmE1YzBhNDdkMzhmOGEzMzRmMzEzMTE4MmM2IiwidGFnIjoiIn0='
            ),
        ]);
    }
}
