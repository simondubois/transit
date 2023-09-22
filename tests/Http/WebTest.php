<?php

namespace Tests\Http;

use App\Models\Account;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebTest extends TestCase
{
    /**
     * Test unknown account.
     */
    public function testUnknownAccount(): void
    {
        // given

        // when
        $response = $this->get(Str::orderedUuid());

        // then
        $response->assertNotFound();
    }

    /**
     * Test Ok response.
     */
    public function testOk(): void
    {
        // given
        $account = Account::factory()->createOne();

        // when
        $response = $this->get("/$account->id");

        // then
        $response->assertOk();
    }
}
