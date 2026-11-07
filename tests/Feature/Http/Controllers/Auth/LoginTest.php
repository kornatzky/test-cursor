<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_form_is_available_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Log in');
    }

    public function test_valid_credentials_sign_the_user_in(): void
    {
        $user = User::factory()->create([
            'email' => 'ada@example.com',
            'password' => 'secret-password',
        ]);

        $this->post(route('login'), [
            'email' => 'Ada@Example.com',
            'password' => 'secret-password',
        ])->assertRedirect(route('balances.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_does_not_sign_the_user_in(): void
    {
        User::factory()->create([
            'email' => 'ada@example.com',
            'password' => 'secret-password',
        ]);

        $this->post(route('login'), [
            'email' => 'ada@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors([
            'email' => 'These credentials do not match our records.',
        ]);

        $this->assertGuest();
    }

    public function test_too_many_login_attempts_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'ada@example.com',
            'password' => 'secret-password',
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login'), [
                'email' => 'ada@example.com',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login'), [
            'email' => 'ada@example.com',
            'password' => 'secret-password',
        ])->assertTooManyRequests();

        $this->assertGuest();
    }

    public function test_a_signed_in_user_is_sent_to_the_ledger(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('balances.index'));
    }
}
