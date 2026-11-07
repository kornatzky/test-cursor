<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Balance;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BalanceIndexTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('balances.index'))
            ->assertRedirect(route('login'));
    }

    public function test_shows_the_usd_total_for_the_signed_in_user(): void
    {
        $this->fakeRates();

        $user = User::factory()->create(['name' => 'Ada Lovelace']);
        Balance::factory()->for($user)->create(['currency' => 'USD', 'amount' => '5.00']);
        Balance::factory()->for($user)->create(['currency' => 'EUR', 'amount' => '10.00']);

        $other = User::factory()->create();
        Balance::factory()->for($other)->create(['currency' => 'GBP', 'amount' => '98765.43']);

        $response = $this->actingAs($user)->get(route('balances.index'));

        $response->assertOk();
        $response->assertSee('Ada Lovelace');
        $response->assertSee('$25.00');
        $response->assertSee('$20.00');
        $response->assertSee('$5.00');
        $response->assertSee('€10.00');
        $response->assertSee('2.0000');
        $response->assertSee('European Central Bank rates for 2026-11-06.');
        $response->assertDontSee('WISE');
        $response->assertDontSee('98,765.43');
        $response->assertDontSee('98765.43');
    }

    public function test_excludes_a_balance_without_a_usd_rate_from_the_total(): void
    {
        $this->fakeRates();

        $user = User::factory()->create();
        Balance::factory()->for($user)->create(['currency' => 'USD', 'amount' => '5.00']);
        Balance::factory()->for($user)->create(['currency' => 'EUR', 'amount' => '10.00']);
        Balance::factory()->for($user)->create(['currency' => 'JPY', 'amount' => '100.00']);

        $response = $this->actingAs($user)->get(route('balances.index'));

        $response->assertOk();
        $response->assertSee('$25.00');
        $response->assertSee('$20.00');
        $response->assertSee('Unavailable');
        $response->assertSee('A balance without a USD rate is excluded from the total.');
    }

    public function test_shows_saved_rates_when_the_rate_service_is_unavailable(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.frankfurter.app/*' => Http::failedConnection(),
        ]);

        $user = User::factory()->create();
        Balance::factory()->for($user)->create(['currency' => 'EUR', 'amount' => '100.00']);

        $response = $this->actingAs($user)->get(route('balances.index'));

        $response->assertOk();
        $response->assertSee('Saved European Central Bank rates for 2026-11-06.');
        $response->assertSee('$111.65');
    }

    public function test_escapes_a_name_that_contains_html(): void
    {
        $this->fakeRates();

        $user = User::factory()->create([
            'name' => "O'Reilly <script>alert('xss')</script>",
        ]);

        $response = $this->actingAs($user)->get(route('balances.index'));

        $response->assertOk();
        $response->assertSee('<script>');
        $response->assertDontSee("<script>alert('xss')</script>", false);
    }

    private function fakeRates(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.frankfurter.app/*' => Http::response([
                'base' => 'USD',
                'date' => '2026-11-06',
                'rates' => [
                    'EUR' => 0.5,
                    'GBP' => 0.8,
                ],
            ]),
        ]);
    }
}
