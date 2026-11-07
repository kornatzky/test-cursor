<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Balance;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BalanceDestroyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $balance = Balance::factory()->create();

        $this->delete(route('balances.destroy', $balance))
            ->assertRedirect(route('login'));

        $this->assertModelExists($balance);
    }

    public function test_removes_the_signed_in_users_balance(): void
    {
        $user = User::factory()->create();
        $balance = Balance::factory()->for($user)->create([
            'currency' => 'EUR',
            'amount' => '10.00',
        ]);

        $this->actingAs($user)
            ->delete(route('balances.destroy', $balance))
            ->assertRedirect(route('balances.index'))
            ->assertSessionHas('status', 'Removed EUR.');

        $this->assertModelMissing($balance);
    }

    public function test_another_users_balance_is_not_found(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $balance = Balance::factory()->for($other)->create([
            'currency' => 'USD',
            'amount' => '80.00',
        ]);

        $this->actingAs($user)
            ->delete(route('balances.destroy', $balance))
            ->assertNotFound();

        $this->assertModelExists($balance);
    }
}
