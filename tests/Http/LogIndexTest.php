<?php

namespace Tests\Http;

use App\Enums\AccountSyncStatus;
use App\Enums\LogStatus;
use App\Models\Account;
use App\Models\Calendar;
use App\Models\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;
use Webmozart\Assert\Assert;

class LogIndexTest extends TestCase
{
    /**
     * Test unknown account.
     */
    public function testUnknownAccount(): void
    {
        // given

        // when
        $response = $this->getJson('/' . Str::orderedUuid() . "/api/logs");

        // then
        $response->assertNotFound();
    }

    /**
     * Test Ok response.
     */
    public function testOk(): void
    {
        // given
        $account1 = Account::factory()->createOne([
            'name' => 'Account',
            'default_location' => 'Default location',
            'current_sync_status' => AccountSyncStatus::Idle,
        ]);
        $account2 = Account::factory()->createOne();
        $calendar1 = Calendar::factory()->for($account1)->createOne([
            'name' => 'Calendar',
            'url' => 'https://domain.com/ics',
        ]);
        $calendar2 = Calendar::factory()->for($account2)->createOne();
        $now = now();
        $log1 = $this->travelTo(
            $now,
            fn () => Log::factory()->createOne([
                'job_type' => 'Job 1',
                'job_started_at' => today(),
                'status' => LogStatus::Completed,
            ])
        );
        Assert::isInstanceOf($log1, Log::class);
        $log2 = $this->travelTo(
            $now->copy()->subHour(),
            fn () => Log::factory()->for($account1, 'holder')->createOne([
                'job_type' => 'Job 2',
                'job_started_at' => today()->subDay(),
                'status' => LogStatus::Completed,
            ])
        );
        Assert::isInstanceOf($log2, Log::class);
        $log3 = $this->travelTo(
            $now->copy()->addHour(),
            fn () => Log::factory()->for($calendar1, 'holder')->createOne([
                'job_type' => 'Job 3',
                'job_started_at' => today()->addDay(),
                'status' => LogStatus::Completed,
            ])
        );
        Assert::isInstanceOf($log3, Log::class);
        Log::factory()->for($calendar2, 'holder');
        Log::factory()->for($account2, 'holder');

        // when
        $response = $this->getJson("/$account1->id/api/logs");

        // then
        $response->assertOk();
        $response->assertJsonPath('data', [
            [
                'id' => $log3->id,
                'holder' => [
                    'id' => $calendar1->id,
                    'account_id' => $account1->id,
                    'name' => 'Calendar',
                    'url' => 'https://domain.com/ics',
                ],
                'job_type' => 'Job 3',
                'job_started_at' => today()->addDay()->toDateTimeString(),
                'status' => LogStatus::Completed->value,
                'created_at' => $now->copy()->addHour()->toDateTimeString(),
            ],
            [
                'id' => $log1->id,
                'holder' => null,
                'job_type' => 'Job 1',
                'job_started_at' => today()->toDateTimeString(),
                'status' => LogStatus::Completed->value,
                'created_at' => $now->toDateTimeString(),
            ],
            [
                'id' => $log2->id,
                'holder' => [
                    'id' => $account1->id,
                    'name' => 'Account',
                    'default_location' => 'Default location',
                    'current_sync_status' => AccountSyncStatus::Idle->value,
                ],
                'job_type' => 'Job 2',
                'job_started_at' => today()->subDay()->toDateTimeString(),
                'status' => LogStatus::Completed->value,
                'created_at' => $now->copy()->subHour()->toDateTimeString(),
            ],
        ]);
    }
}
