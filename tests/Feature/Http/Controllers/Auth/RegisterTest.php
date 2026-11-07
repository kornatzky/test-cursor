<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_an_account_and_signs_the_user_in(): void
    {
        $this->post(route('register'), [
            'name' => 'Ada Lovelace',
            'email' => 'Ada@Example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirect(route('balances.index'));

        $user = User::query()->where('email', 'ada@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('Ada Lovelace', $user->name);
        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertNotSame('secret-password', $user->password);
        $this->assertAuthenticatedAs($user);
    }

    public function test_requires_a_name_email_and_password(): void
    {
        $this->post(route('register'), [])
            ->assertSessionHasErrors([
                'name' => 'Enter your name.',
                'email' => 'Enter your email address.',
                'password' => 'Enter a password.',
            ]);

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_rejects_a_short_password(): void
    {
        $this->post(route('register'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors([
            'password' => 'Use at least 8 characters.',
        ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_rejects_a_password_confirmation_that_does_not_match(): void
    {
        $this->post(route('register'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'other-password',
        ])->assertSessionHasErrors([
            'password' => 'The password confirmation does not match.',
        ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_rejects_an_email_that_is_already_registered(): void
    {
        User::factory()->create(['email' => 'ada@example.com']);

        $this->post(route('register'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertSessionHasErrors([
            'email' => 'An account with this email already exists.',
        ]);

        $this->assertSame(1, User::query()->count());
        $this->assertGuest();
    }
}
