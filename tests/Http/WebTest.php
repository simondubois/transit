<?php

namespace Tests\Http;

use App\Models\Account;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebTest extends TestCase
{
    /**
     * Test root unknown account.
     */
    public function testRootUnknownAccount(): void
    {
        // given

        // when
        $response = $this->get(Str::orderedUuid());

        // then
        $response->assertNotFound();
    }

    /**
     * Test root redirect.
     */
    public function testRootRedirect(): void
    {
        // given
        $account = Account::factory()->createOne();

        // when
        $response = $this->get("/$account->id");

        // then
        $response->assertRedirectToRoute('agenda', ['account' => $account]);
    }

    /**
     * Test agenda unknown account.
     */
    public function testAgendaUnknownAccount(): void
    {
        // given

        // when
        $response = $this->get(Str::orderedUuid() . '/agenda');

        // then
        $response->assertNotFound();
    }

    /**
     * Test agenda view.
     */
    public function testAgendaView(): void
    {
        // given
        $account = Account::factory()->createOne();

        // when
        $response = $this->get("/$account->id/agenda");

        // then
        $response->assertOk();
    }
}
