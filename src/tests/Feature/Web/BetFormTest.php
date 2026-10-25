<?php

namespace Tests\Feature\Web;

use App\Livewire\BetForm;
use App\Models\Bet;
use App\Models\BetTimeSetting;
use App\Models\Ticket;
use App\Support\BetNumbers;
use Database\Seeders\BetCategorySeeder;
use Database\Seeders\BetTimeSettingSeeder;
use Livewire\Livewire;
use Tests\Feature\BaseTestCase;

class BetFormTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([BetCategorySeeder::class, BetTimeSettingSeeder::class]);
        BetTimeSetting::bustCache();
        $this->travelTo(today()->setTime(9, 0));
        $this->actingAsStaff();
    }

    private function form(): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::test(BetForm::class, ['today' => today()->toDateString()]);
    }

    public function test_home_shows_lottery_types_and_money_header(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('KH5D')->assertSee('VN5D')->assertSee('VIP5D')->assertSee('KHR');
    }

    public function test_pair_mode_on_two_posts_saves_matching_total(): void
    {
        $form = $this->form()
            ->set('lotteryType', 'KH5D')
            ->set('session', 'kh5d_1035')
            ->set('posts', ['A', 'B'])
            ->call('addBets', '', '100', '', BetNumbers::PAIR);

        $bets = $form->get('bets');
        $this->assertCount(2, $bets);
        $this->assertSame([10, 10], array_column($bets, 'count'));
        $this->assertSame(20, Bet::count());

        $form->call('submitBets')->assertSet('bets', []);
        $this->assertDatabaseHas('tickets', ['user_id' => $this->staff->id, 'session' => 'kh5d_1035', 'status' => 'pending', 'total_amount' => 2000]);
    }

    public function test_remove_deletes_only_that_rows_bets(): void
    {
        $form = $this->form()
            ->set('session', 'kh5d_1035')
            ->set('posts', ['A', 'B'])
            ->call('addBets', '5', '200', '', BetNumbers::ROLL)
            ->call('removeBet', 0);

        $this->assertSame(['B'], array_column($form->get('bets'), 'letter'));
        $this->assertSame(19, Bet::where('letter', 'B')->count());
        $this->assertSame(0, Bet::where('letter', 'A')->count());
        $this->assertEquals(3800, Ticket::first()->total_amount);
    }

    public function test_draft_rows_rebuild_after_reload(): void
    {
        $this->form()
            ->set('session', 'kh5d_1035')
            ->set('posts', ['4K', 'C'])
            ->call('addBets', '10', '100', '19', BetNumbers::RANGE);

        $bets = $this->form()->set('session', 'kh5d_1035')->get('bets');
        $this->assertSame(['4K', 'C'], array_column($bets, 'letter'));
        $this->assertSame([10, 10], array_column($bets, 'count'));
    }

    public function test_invalid_amount_or_post_adds_nothing(): void
    {
        $this->form()->set('session', 'kh5d_1035')->set('posts', ['A'])->call('addBets', '25', '150');
        $this->form()->set('session', 'kh5d_1035')->set('posts', ['ZZ'])->call('addBets', '25', '100');

        $this->assertSame(0, Bet::count());
    }

    public function test_lo_post_closes_before_other_posts(): void
    {
        $this->travelTo(today()->setTime(10, 20)); // VN5D 10:35 — Lo cutoff 10:15, others 10:25

        $this->form()->set('lotteryType', 'VN5D')->set('session', 'vn5d_1035')
            ->set('posts', ['Lo'])->call('addBets', '25', '100')->assertDispatched('bet-closed');
        $this->form()->set('lotteryType', 'VN5D')->set('session', 'vn5d_1035')
            ->set('posts', ['A'])->call('addBets', '25', '100')->assertDispatched('bet-added');
    }

    public function test_switching_type_selects_its_draw_and_valid_posts(): void
    {
        $this->form()
            ->set('lotteryType', 'VIP5D')
            ->assertSet('session', 'vip5d_1630')
            ->assertSet('posts', ['4P']);
    }
}
