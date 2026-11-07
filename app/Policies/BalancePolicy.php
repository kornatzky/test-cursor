<?php

namespace App\Policies;

use App\Models\Balance;
use App\Models\User;

class BalancePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Balance $balance): bool
    {
        return $balance->user_id === $user->id;
    }

    public function delete(User $user, Balance $balance): bool
    {
        return $balance->user_id === $user->id;
    }
}
