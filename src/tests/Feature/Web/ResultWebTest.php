<?php

namespace Tests\Feature\Web;

use App\Models\Result;
use Tests\Feature\BaseTestCase;

class ResultWebTest extends BaseTestCase
{
    public function test_result_page_renders_for_all_roles(): void
    {
           $this->actingAsAdmin()
               ->get(route('result'))
               ->assertOk()
               ->assertViewIs('admin.result');

           $this->actingAsMaster()
               ->get(route('result'))
               ->assertOk()
               ->assertViewIs('result');

           $this->actingAsStaff()
               ->get(route('result'))
               ->assertOk()
               ->assertViewIs('result');
    }

    public function test_guest_cannot_access_result(): void
    {
        $this->get(route('result'))->assertRedirect(route('login'));
    }

    public function test_result_page_shows_session_filter(): void
    {
        $this->actingAsAdmin()
             ->get(route('result'))
             ->assertOk()
             ->assertSee('morning')
             ->assertSee('noon')
             ->assertSee('evening');
    }

    public function test_admin_can_save_results(): void
    {
        $this->actingAsAdmin()
             ->post(route('result.store'), [
                 'result_date' => today()->toDateString(),
                 'session'     => 'morning',
                 'results'     => [
                     ['position' => 'A', 'number' => '25'],
                     ['position' => 'B', 'number' => '07'],
                 ],
             ])->assertRedirect();

        $this->assertTrue(
            \App\Models\Result::where('session', 'morning')
                ->where('position', 'A')
                ->where('number', '25')
                ->whereDate('result_date', today())
                ->exists()
        );
    }

    public function test_master_cannot_save_results(): void
    {
        $this->actingAsMaster()
             ->post(route('result.store'), [
                 'result_date' => today()->toDateString(),
                 'session'     => 'morning',
                 'results'     => [['position' => 'A', 'number' => '25']],
             ])->assertForbidden();
    }

    public function test_staff_cannot_save_results(): void
    {
        $this->actingAsStaff()
             ->post(route('result.store'), [
                 'result_date' => today()->toDateString(),
                 'session'     => 'morning',
                 'results'     => [['position' => 'A', 'number' => '25']],
             ])->assertForbidden();
    }

    public function test_existing_results_are_shown_in_grid(): void
    {
        $result = Result::factory()->create([
            'result_date' => today()->toDateString(),
            'session'     => 'morning',
            'position'    => 'A',
            'number'      => '42',
        ]);

        $this->actingAsAdmin()
             ->get(route('result', [
                 'date'    => today()->toDateString(),
                 'session' => 'morning',
             ]))
             ->assertOk()
             ->assertSee('42');
    }

    public function test_admin_can_update_a_result(): void
    {
        $result = Result::factory()->create([
            'result_date' => today()->toDateString(),
            'session'     => 'morning',
            'position'    => 'A',
            'number'      => '10',
        ]);

        $this->actingAsAdmin()
             ->put(route('result.update', $result), [
                 'result_date' => today()->toDateString(),
                 'session'     => 'morning',
                 'results'     => [['position' => 'A', 'number' => '99']],
             ])->assertRedirect();

        $this->assertDatabaseHas('results', ['id' => $result->id, 'number' => '99']);
    }
}
