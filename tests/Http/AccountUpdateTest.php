<?php

namespace Tests\Http;

use App\Enums\AccountSyncStatus;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountUpdateTest extends TestCase
{
    /**
     * Test unknown account.
     */
    public function testUnknownAccount(): void
    {
        // given
        $account = Account::factory()->createOne([
            'name' => 'Account 1',
            'default_location' => 'Default location 1',
        ]);

        // when
        $response = $this->putJson(Str::orderedUuid() . "/api/account", [
            'name' => 'Account 2',
            'default_location' => 'Default location 2',
        ]);

        // then
        $response->assertNotFound();
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Account 1',
            'default_location' => 'Default location 1',
        ]);
    }

    /**
     * Test missing name.
     */
    public function testMissingName(): void
    {
        // given
        $account = Account::factory()->createOne([
            'name' => 'Account 1',
            'default_location' => 'Default location 1',
        ]);

        // when
        $response = $this->putJson("/$account->id/api/account", [
            'default_location' => 'Default location 2',
        ]);

        // then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'name' => trans('validation.required', ['attribute' => 'name']),
        ]);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Account 1',
            'default_location' => 'Default location 1',
        ]);
    }

    /**
     * Test used name.
     */
    public function testUsedName(): void
    {
        // given
        $account1 = Account::factory()->createOne([
            'name' => 'Account 1',
            'default_location' => 'Default location 1',
        ]);
        $account2 = Account::factory()->createOne(['name' => 'Account 2']);

        // when
        $response = $this->putJson("/$account1->id/api/account", [
            'name' => 'Account 2',
            'default_location' => 'Default location 2',
        ]);

        // then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'name' => trans('validation.unique', ['attribute' => 'name']),
        ]);
        $this->assertDatabaseHas('accounts', [
            'id' => $account1->id,
            'name' => 'Account 1',
            'default_location' => 'Default location 1',
        ]);
        $this->assertDatabaseHas('accounts', [
            'id' => $account2->id,
            'name' => 'Account 2',
        ]);
    }

    /**
     * Test missing default_location.
     */
    public function testMissingDefaultLocation(): void
    {
        // given
        $account = Account::factory()->createOne([
            'name' => 'Account 1',
            'default_location' => 'Default location 1',
        ]);

        // when
        $response = $this->putJson("/$account->id/api/account", [
            'name' => 'Account 2',
        ]);

        // then
        $response->assertUnprocessable()->assertJsonValidationErrors([
            'default_location' => trans('validation.required', ['attribute' => 'default location']),
        ]);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Account 1',
            'default_location' => 'Default location 1',
        ]);
    }

    /**
     * Test Ok response.
     */
    public function testOk(): void
    {
        // given
        $account = Account::factory()->createOne([
            'name' => 'Account 1',
            'current_sync_status' => AccountSyncStatus::Running,
        ]);

        // when
        $response = $this->putJson("/$account->id/api/account", [
            'name' => 'Account 2',
            'default_location' => 'Default location 2',
        ]);

        // then
        $response->assertOk();
        $response->assertJsonPath('data', [
            'id' => $account->id,
            'name' => 'Account 2',
            'default_location' => 'Default location 2',
            'current_sync_status' => AccountSyncStatus::Running->value,
        ]);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Account 2',
            'default_location' => 'Default location 2',
        ]);
    }
}
