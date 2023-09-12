<?php

namespace Tests\Http;

use App\Enums\CalendarSyncStatus;
use App\Models\Account;
use App\Models\Calendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class CalendarUpdateTest extends TestCase
{
    /**
     * Test unknown account.
     */
    public function testUnknownAccount(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne([
            'name' => 'Calendar 1',
            'url' => 'https://domain.com/ics1',
        ]);

        // when
        $response = $this->putJson('/' . Str::orderedUuid() . "/api/calendars/$calendar->id", [
            'name' => 'Calendar 2',
            'url' => 'https://domain.com/ics2',
        ]);

        // then
        $response->assertNotFound();
        $this->assertDatabaseHas('calendars', [
            'id' => $calendar->id,
            'name' => 'Calendar 1',
            'url' => 'https://domain.com/ics1',
        ]);
    }

    /**
     * Test different account.
     */
    public function testDifferentAccount(): void
    {
        // given
        $account1 = Account::factory()->createOne();
        $account2 = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account1)->createOne([
            'name' => 'Calendar 1',
            'url' => 'https://domain.com/ics1',
        ]);

        // when
        $response = $this->putJson("/$account2->id/api/calendars/$calendar->id", [
            'name' => 'Calendar 2',
            'url' => 'https://domain.com/ics2',
        ]);

        // then
        $response->assertNotFound();
        $this->assertDatabaseHas('calendars', [
            'id' => $calendar->id,
            'name' => 'Calendar 1',
            'url' => 'https://domain.com/ics1',
        ]);
    }

    /**
     * Test missing name.
     */
    public function testMissingName(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne([
            'name' => 'Calendar 1',
            'url' => 'https://domain.com/ics1',
        ]);

        // when
        $response = $this->putJson("/$account->id/api/calendars/$calendar->id", [
            'url' => 'https://domain.com/ics2',
        ]);

        // then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'name' => trans('validation.required', ['attribute' => 'name']),
        ]);
        $this->assertDatabaseHas('calendars', [
            'id' => $calendar->id,
            'name' => 'Calendar 1',
            'url' => 'https://domain.com/ics1',
        ]);
    }

    /**
     * Test used name.
     */
    public function testUsedName(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar1 = Calendar::factory()->for($account)->createOne([
            'name' => 'Calendar 1',
            'url' => 'https://domain.com/ics1',
        ]);
        Calendar::factory()->for($account)->createOne([
            'name' => 'Calendar 2',
        ]);

        // when
        $response = $this->putJson("/$account->id/api/calendars/$calendar1->id", [
            'name' => 'Calendar 2',
            'url' => 'https://domain.com/ics2',
        ]);

        // then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'name' => trans('validation.unique', ['attribute' => 'name']),
        ]);
        $this->assertDatabaseHas('calendars', [
            'id' => $calendar1->id,
            'name' => 'Calendar 1',
            'url' => 'https://domain.com/ics1',
        ]);
    }

    /**
     * Test missing url.
     */
    public function testMissingUrl(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne([
            'name' => 'Calendar 1',
            'url' => 'https://domain.com/ics1',
        ]);

        // when
        $response = $this->putJson("/$account->id/api/calendars/$calendar->id", [
            'name' => 'Calendar 2',
        ]);

        // then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'url' => trans('validation.required', ['attribute' => 'url']),
        ]);
        $this->assertDatabaseHas('calendars', [
            'id' => $calendar->id,
            'name' => 'Calendar 1',
            'url' => 'https://domain.com/ics1',
        ]);
    }

    /**
     * Test used url.
     */
    public function testUsedUrl(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar1 = Calendar::factory()->for($account)->createOne([
            'name' => 'Calendar 1',
            'url' => 'https://domain.com/ics1',
        ]);
        Calendar::factory()->for($account)->createOne([
            'url' => 'https://domain.com/ics2',
        ]);

        // when
        $response = $this->putJson("/$account->id/api/calendars/$calendar1->id", [
            'name' => 'Calendar 2',
            'url' => 'https://domain.com/ics2',
        ]);

        // then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'url' => trans('validation.unique', ['attribute' => 'url']),
        ]);
        $this->assertDatabaseHas('calendars', [
            'id' => $calendar1->id,
            'name' => 'Calendar 1',
            'url' => 'https://domain.com/ics1',
        ]);
    }

    /**
     * Test Ok response.
     */
    public function testOk(): void
    {
        // given
        $account = Account::factory()->createOne();
        $calendar = Calendar::factory()->for($account)->createOne([
            'name' => 'Calendar 1',
            'url' => 'https://domain.com/ics1',
        ]);

        // when
        $response = $this->putJson("/$account->id/api/calendars/$calendar->id", [
            'name' => 'Calendar 2',
            'url' => 'https://domain.com/ics2',
        ]);

        // then
        $response->assertOk();
        $response->assertJsonPath('data', [
            'id' => $calendar->id,
            'account_id' => $account->id,
            'name' => 'Calendar 2',
            'url' => 'https://domain.com/ics2',
        ]);
        $this->assertDatabaseHas('calendars', [
            'id' => $calendar->id,
            'account_id' => $account->id,
            'name' => 'Calendar 2',
            'url' => 'https://domain.com/ics2',
        ]);
    }
}
