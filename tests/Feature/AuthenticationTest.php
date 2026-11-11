<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_sent_to_the_login_page_and_cannot_open_dashboard(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Sign in to your account');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in to your account');

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_register_and_reach_dashboard(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Braviel Group',
            'email' => 'hello@bravielgroup.com',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'hello@bravielgroup.com']);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Braviel Group');
    }

    public function test_user_can_sign_in_and_sign_out(): void
    {
        $user = User::factory()->create(['password' => 'secure-password']);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'secure-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_from_guest_pages_until_signing_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get('/')
            ->assertRedirect(route('dashboard'));
        $this->get(route('login'))
            ->assertRedirect(route('dashboard'));
        $this->get(route('password.request'))
            ->assertRedirect(route('dashboard'));
        $this->get(route('password.reset', 'example-token'))
            ->assertRedirect(route('dashboard'));

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->get(route('login'))->assertOk();
    }

    public function test_password_reset_link_can_be_requested_and_used(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
        $notification = Notification::sent($user, ResetPassword::class)->first();

        $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('login'));

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'new-secure-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }
}
