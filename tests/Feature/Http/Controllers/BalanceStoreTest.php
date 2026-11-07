<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Balance;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BalanceStoreTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->post(route('balances.store'), [
            'currency' => 'EUR',
            'amount' => '10.00',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('balances', 0);
    }

    public function test_saves_a_balance_for_the_signed_in_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($user)->post(route('balances.store'), [
            'currency' => 'eur',
            'amount' => '1,250.50',
            'user_id' => $other->id,
        ]);

        $response->assertRedirect(route('balances.index'));
        $response->assertSessionHas('status', 'Saved EUR balance.');

        $balance = Balance::query()->sole();
        $this->assertModelExists($balance);
        $this->assertSame($user->id, $balance->user_id);
        $this->assertSame('EUR', $balance->currency);
        $this->assertSame('1250.50', $balance->amount);
    }

    public function test_replaces_an_existing_currency_balance(): void
    {
        $user = User::factory()->create();
        Balance::factory()->for($user)->create(['currency' => 'EUR', 'amount' => '10.00']);

        $this->actingAs($user)
            ->post(route('balances.store'), [
                'currency' => 'EUR',
                'amount' => '12.50',
            ])
            ->assertRedirect(route('balances.index'));

        $this->assertSame(1, $user->balances()->count());
        $this->assertSame('12.50', $user->balances()->first()->amount);
    }

    public function test_saves_a_negative_balance(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('balances.store'), [
            'currency' => 'USD',
            'amount' => '-5.25',
        ])->assertRedirect(route('balances.index'));

        $this->assertSame('-5.25', $user->balances()->first()->amount);
    }

    public function test_requires_a_currency_and_an_amount(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('balances.store'), [])
            ->assertSessionHasErrors([
                'currency' => 'Choose a currency.',
                'amount' => 'Enter an amount.',
            ]);

        $this->assertDatabaseCount('balances', 0);
    }

    #[DataProvider('rejectedCurrencyCodes')]
    public function test_rejects_wise_because_it_is_not_a_currency(string $currency): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('balances.store'), [
                'currency' => $currency,
                'amount' => '50.00',
            ])
            ->assertSessionHasErrors([
                'currency' => 'WISE is not a currency.',
            ]);

        $this->assertDatabaseCount('balances', 0);
    }

    public function test_rejects_an_unknown_currency(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('balances.store'), [
                'currency' => 'BTC',
                'amount' => '1.00',
            ])
            ->assertSessionHasErrors([
                'currency' => 'Choose a valid currency.',
            ]);

        $this->assertDatabaseCount('balances', 0);
    }

    public function test_rejects_an_amount_with_more_than_two_decimal_places(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('balances.store'), [
                'currency' => 'EUR',
                'amount' => '1.234',
            ])
            ->assertSessionHasErrors([
                'amount' => 'Enter an amount with up to 2 decimal places.',
            ]);

        $this->assertDatabaseCount('balances', 0);
    }

    public function test_rejects_an_amount_that_is_not_a_number(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('balances.store'), [
                'currency' => 'EUR',
                'amount' => 'ten',
            ])
            ->assertSessionHasErrors([
                'amount' => 'Enter an amount.',
            ]);

        $this->assertDatabaseCount('balances', 0);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rejectedCurrencyCodes(): array
    {
        return [
            'uppercase' => ['WISE'],
            'lowercase' => ['wise'],
        ];
    }
}
