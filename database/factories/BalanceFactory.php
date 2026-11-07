<?php

namespace Database\Factories;

use App\Models\Balance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Balance>
 */
class BalanceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'currency' => 'USD',
            'amount' => '100.00',
        ];
    }
}
