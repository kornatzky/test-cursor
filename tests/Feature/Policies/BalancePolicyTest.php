<?php

namespace Tests\Feature\Policies;

use App\Models\Balance;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class BalancePolicyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_a_user_can_view_and_create_balances(): void
    {
        $user = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Balance::class));
        $this->assertTrue(Gate::forUser($user)->allows('create', Balance::class));
    }

    public function test_only_the_owner_can_update_or_delete_a_balance(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $balance = Balance::factory()->for($owner)->create();

        $this->assertTrue(Gate::forUser($owner)->allows('update', $balance));
        $this->assertTrue(Gate::forUser($owner)->allows('delete', $balance));
        $this->assertFalse(Gate::forUser($other)->allows('update', $balance));
        $this->assertFalse(Gate::forUser($other)->allows('delete', $balance));
    }
}
