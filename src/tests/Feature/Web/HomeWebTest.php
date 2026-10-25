<?php

namespace Tests\Feature\Web;

use App\Models\Ticket;
use Tests\Feature\BaseTestCase;

class HomeWebTest extends BaseTestCase
{
    public function test_home_page_renders_for_all_roles(): void
    {
        foreach ([$this->admin, $this->master, $this->staff] as $user) {
            $this->actingAs($user)
                 ->get(route('home'))
                 ->assertOk()
                 ->assertViewIs('home');
        }
    }

    public function test_guest_cannot_access_home(): void
    {
        $this->get(route('home'))->assertRedirect(route('login'));
    }

    public function test_home_shows_today_date(): void
    {
        $this->actingAsStaff()
             ->get(route('home'))
             ->assertOk()
             ->assertSee(today()->format('d'));
    }
}
