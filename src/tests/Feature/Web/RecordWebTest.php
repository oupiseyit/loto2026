<?php

namespace Tests\Feature\Web;

use App\Models\Bet;
use App\Models\Result;
use App\Models\Ticket;
use Tests\Feature\BaseTestCase;

class RecordWebTest extends BaseTestCase
{
    public function test_record_page_renders_for_all_roles(): void
    {
           $this->actingAsAdmin()
               ->get(route('record'))
               ->assertOk()
               ->assertViewIs('admin.record');

           $this->actingAsMaster()
               ->get(route('record'))
               ->assertOk()
               ->assertViewIs('record');

           $this->actingAsStaff()
               ->get(route('record'))
               ->assertOk()
               ->assertViewIs('record');
    }

    public function test_guest_cannot_access_record(): void
    {
        $this->get(route('record'))->assertRedirect(route('login'));
    }

    public function test_staff_only_sees_own_tickets(): void
    {
        $ownTicket   = Ticket::factory()->forUser($this->staff->id)->onDate(today()->toDateString())->create();
        $otherTicket = Ticket::factory()->forUser($this->admin->id)->onDate(today()->toDateString())->create();

        $response = $this->actingAsStaff()
                         ->get(route('record', ['date' => today()->toDateString()]))
                         ->assertOk();

        $response->assertSee($ownTicket->invoice_number);
        $response->assertDontSee($otherTicket->invoice_number);
    }

    public function test_master_only_sees_own_staff_tickets(): void
    {
        $staffTicket = Ticket::factory()->forUser($this->staff->id)->onDate(today()->toDateString())->create();

        $otherStaff  = \App\Models\User::factory()->staff()->create(['created_by' => $this->admin->id]);
        $otherTicket = Ticket::factory()->forUser($otherStaff->id)->onDate(today()->toDateString())->create();

        $response = $this->actingAsMaster()
                         ->get(route('record', ['date' => today()->toDateString()]))
                         ->assertOk();

        $response->assertSee($staffTicket->invoice_number);
        $response->assertDontSee($otherTicket->invoice_number);
    }

    public function test_admin_sees_all_tickets(): void
    {
        $t1 = Ticket::factory()->forUser($this->staff->id)->onDate(today()->toDateString())->create();
        $t2 = Ticket::factory()->forUser($this->master->id)->onDate(today()->toDateString())->create();

        $response = $this->actingAsAdmin()
                         ->get(route('record', ['date' => today()->toDateString()]))
                         ->assertOk();

        $response->assertSee($t1->invoice_number);
        $response->assertSee($t2->invoice_number);
    }

    public function test_session_tab_filter_shows_only_morning(): void
    {
        $morning = Ticket::factory()->morning()->forUser($this->staff->id)->onDate(today()->toDateString())->create();
        $evening = Ticket::factory()->evening()->forUser($this->staff->id)->onDate(today()->toDateString())->create();

        $response = $this->actingAsStaff()
                         ->get(route('record', ['date' => today()->toDateString(), 'tab' => 'morning']))
                         ->assertOk();

        $response->assertSee($morning->invoice_number);
        $response->assertDontSee($evening->invoice_number);
    }

    public function test_winning_tab_shows_only_won_tickets(): void
    {
        $won  = Ticket::factory()->won()->forUser($this->staff->id)->onDate(today()->toDateString())->create();
        $lost = Ticket::factory()->lost()->forUser($this->staff->id)->onDate(today()->toDateString())->create();

        $response = $this->actingAsStaff()
                         ->get(route('record', ['date' => today()->toDateString(), 'tab' => 'winning']))
                         ->assertOk();

        $response->assertSee($won->invoice_number);
        $response->assertDontSee($lost->invoice_number);
    }

    public function test_record_page_shows_totals_footer(): void
    {
        Ticket::factory()->forUser($this->staff->id)
              ->onDate(today()->toDateString())
              ->create(['total_amount' => 10000]);

        $this->actingAsStaff()
             ->get(route('record', ['date' => today()->toDateString()]))
             ->assertOk()
             ->assertSee('10,000');
    }

    public function test_record_page_calculates_ticket_wins_from_results(): void
    {
        $date = today()->toDateString();

        $ticket = Ticket::factory()
            ->morning()
            ->forUser($this->staff->id)
            ->onDate($date)
            ->create(['total_amount' => 3000]);

        Bet::factory()->forTicket($ticket)->create([
            'number'   => '25',
            'amount'   => 1000,
            'position' => 'X',
            'letter'   => 'A',
        ]);

        Bet::factory()->forTicket($ticket)->create([
            'number'   => '99',
            'amount'   => 2000,
            'position' => 'W',
            'letter'   => 'B',
        ]);

        Result::factory()->create([
            'result_date' => $date,
            'session'     => 'morning',
            'position'    => 'A',
            'number'      => '25',
            'entered_by'  => $this->admin->id,
        ]);

        Result::factory()->create([
            'result_date' => $date,
            'session'     => 'morning',
            'position'    => 'B',
            'number'      => '77',
            'entered_by'  => $this->admin->id,
        ]);

        $this->actingAsStaff()
            ->get(route('record', ['date' => $date]))
            ->assertOk()
            ->assertViewHas('tickets', function ($tickets) use ($ticket) {
                $row = $tickets->getCollection()->firstWhere('id', $ticket->id);

                if (!$row) {
                    return false;
                }

                $winningBet = $row->bets->firstWhere('number', '25');
                $losingBet  = $row->bets->firstWhere('number', '99');

                return (float) $row->win_amount === 2000.0
                    && $row->status === 'won'
                    && $winningBet !== null
                    && (bool) $winningBet->is_winner === true
                    && (float) $winningBet->win_amount === 2000.0
                    && $losingBet !== null
                    && (bool) $losingBet->is_winner === false
                    && (float) $losingBet->win_amount === 0.0;
            });
    }
}
