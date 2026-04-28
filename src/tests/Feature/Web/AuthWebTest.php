<?php

namespace Tests\Feature\Web;

use Tests\Feature\BaseTestCase;

class AuthWebTest extends BaseTestCase
{
    public function test_login_page_renders(): void
    {
        $this->get(route('login'))
             ->assertOk()
             ->assertViewIs('auth.login');
    }

    public function test_valid_credentials_redirect_to_home(): void
    {
        $this->post(route('login'), [
            'username' => $this->staff->username,
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($this->staff);
    }

    public function test_invalid_credentials_return_error(): void
    {
        $this->post(route('login'), [
            'username' => 'nobody',
            'password' => 'wrongpass',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $inactive = \App\Models\User::factory()->staff()->inactive()->create([
            'created_by' => $this->master->id,
        ]);

        $this->post(route('login'), [
            'username' => $inactive->username,
            'password' => 'password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_guest_is_redirected_from_protected_routes(): void
    {
        foreach (['home', 'record', 'result', 'account'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_logout_clears_session_and_redirects(): void
    {
        $this->actingAsStaff()
             ->post(route('logout'))
             ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $this->actingAsStaff()
             ->get(route('login'))
             ->assertRedirect(route('home'));
    }
}
