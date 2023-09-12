<?php

namespace Tests\Http;

use App\Enums\CalendarSyncStatus;
use App\Models\Account;
use App\Models\Calendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class CalendarStoreTest extends TestCase
{
    /**
     * Test unknown account.
     */
    public function testUnknownAccount(): void
    {
        // given

        // when
        $response = $this->postJson('/' . Str::orderedUuid() . "/api/calendars", [
            'name' => 'Calendar',
            'url' => 'https://domain.com/ics',
        ]);

        // then
        $response->assertNotFound();
        $this->assertDatabaseEmpty('calendars');
    }

    /**
     * Test missing name.
     */
    public function testMissingName(): void
    {
        // given
        $account = Account::factory()->createOne();

        // when
        $response = $this->postJson("/$account->id/api/calendars", [
            'url' => 'https://domain.com/ics',
        ]);

        // then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'name' => trans('validation.required', ['attribute' => 'name']),
        ]);
        $this->assertDatabaseEmpty('calendars');
    }

    /**
     * Test used name.
     */
    public function testUsedName(): void
    {
        // given
        $account = Account::factory()->createOne();
        Calendar::factory()->for($account)->createOne([
            'name' => 'Calendar',
        ]);

        // when
        $response = $this->postJson("/$account->id/api/calendars", [
            'name' => 'Calendar',
            'url' => 'https://domain.com/ics',
        ]);

        // then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'name' => trans('validation.unique', ['attribute' => 'name']),
        ]);
        $this->assertDatabaseCount('calendars', 1);
    }

    /**
     * Test missing url.
     */
    public function testMissingUrl(): void
    {
        // given
        $account = Account::factory()->createOne();

        // when
        $response = $this->postJson("/$account->id/api/calendars", [
            'name' => 'Calendar',
        ]);

        // then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'url' => trans('validation.required', ['attribute' => 'url']),
        ]);
        $this->assertDatabaseEmpty('calendars');
    }

    /**
     * Test used url.
     */
    public function testUsedUrl(): void
    {
        // given
        $account = Account::factory()->createOne();
        Calendar::factory()->for($account)->createOne([
            'url' => 'https://domain.com/ics',
        ]);

        // when
        $response = $this->postJson("/$account->id/api/calendars", [
            'name' => 'Calendar',
            'url' => 'https://domain.com/ics',
        ]);

        // then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'url' => trans('validation.unique', ['attribute' => 'url']),
        ]);
        $this->assertDatabaseCount('calendars', 1);
    }

    /**
     * Test Ok response.
     */
    public function testOk(): void
    {
        // given
        $account = Account::factory()->createOne();

        // when
        $response = $this->postJson("/$account->id/api/calendars", [
            'name' => 'Calendar',
            'url' => 'https://domain.com/ics',
        ]);

        // then
        $response->assertCreated();
        $response->assertJsonPath('data', [
            'id' => Calendar::value('id'),
            'account_id' => $account->id,
            'name' => 'Calendar',
            'url' => 'https://domain.com/ics',
        ]);
        $this->assertDatabaseHas('calendars', [
            'id' => Calendar::value('id'),
            'account_id' => $account->id,
            'name' => 'Calendar',
            'url' => 'https://domain.com/ics',
        ]);
    }
}
