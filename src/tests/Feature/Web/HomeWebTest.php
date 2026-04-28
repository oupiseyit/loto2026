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

    public function test_home_page_contains_session_buttons(): void
    {
        $this->actingAsStaff()
             ->get(route('home'))
             ->assertOk()
             ->assertSee('morning', false)
             ->assertSee('noon', false)
             ->assertSee('evening', false);
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

    public function test_staff_can_submit_ticket_via_livewire(): void
    {
        $this->actingAsStaff();

        \Livewire\Livewire::test(\App\Livewire\BetForm::class, ['today' => today()->toDateString()])
            ->call('addBet', '25', '5000')
            ->assertSet('bets', function ($bets) {
                return count($bets) === 1
                    && $bets[0]['number'] === '25'
                    && $bets[0]['amount'] == 5000;
            });
    }

    public function test_bet_form_submit_creates_ticket(): void
    {
        $this->actingAsStaff();

        \Livewire\Livewire::test(\App\Livewire\BetForm::class, ['today' => today()->toDateString()])
            ->call('addBet', '12', '3000')
            ->call('submitBets')
            ->assertSet('bets', []);

        $this->assertDatabaseHas('tickets', [
            'user_id'      => $this->staff->id,
            'total_amount' => 3000,
        ]);
    }

    public function test_bet_form_ignores_zero_amount(): void
    {
        $this->actingAsStaff();

        \Livewire\Livewire::test(\App\Livewire\BetForm::class, ['today' => today()->toDateString()])
            ->call('addBet', '25', '0')
            ->assertSet('bets', []);
    }

    public function test_bet_form_remove_bet_works(): void
    {
        $this->actingAsStaff();

        $component = \Livewire\Livewire::test(\App\Livewire\BetForm::class, ['today' => today()->toDateString()])
            ->call('addBet', '25', '5000');

        $bets = $component->get('bets');
        $id   = $bets[0]['id'];

        $component->call('removeBet', $id)
                  ->assertSet('bets', []);
    }

    public function test_session_change_resets_invalid_letter(): void
    {
        $this->actingAsStaff();

        // Setting 'session' via Livewire triggers the updatedSession lifecycle hook automatically
        \Livewire\Livewire::test(\App\Livewire\BetForm::class, ['today' => today()->toDateString()])
            ->set('session', 'noon')
            ->set('letter', 'F')
            ->set('session', 'morning')  // triggers updatedSession()
            ->assertSet('letter', 'A');
    }
}
