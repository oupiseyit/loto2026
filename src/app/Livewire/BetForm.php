<?php

namespace App\Livewire;

use App\Models\Bet;
use App\Models\BetTimeSetting;
use App\Models\Ticket;
use App\Support\BetNumbers;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BetForm extends Component
{
    public string $today = '';

    public string $lotteryType = '';

    public string $session = '';

    /** @var list<string> selected posts (ប៉ុស្តិ៍) */
    public array $posts = [];

    public string $position = 'X';

    /** @var list<array{ids: list<int>, letter: string, number: string, amount: float, count: int}> */
    public array $bets = [];

    public ?int $draftTicketId = null;

    public ?string $flashSuccess = null;

    public ?string $flashError = null;

    public function mount(string $today = ''): void
    {
        $this->today = $today;

        // Auto-select the first draw that is still open for betting
        $sessions = BetTimeSetting::active();
        $first = $sessions->first(fn ($s) => ! in_array($s->sessionStatus(), ['closed', 'done']))
            ?? $sessions->first();
        $this->lotteryType = $first->lottery_type ?? '';
        $this->session = $first->session_key ?? '';
        $this->posts = array_slice($first->group_type ?? [], 0, 1);

        $this->loadDraftBets();
    }

    #[Computed]
    public function availableSessions(): Collection
    {
        return BetTimeSetting::active()->where('lottery_type', $this->lotteryType)->values();
    }

    #[Computed]
    public function letters(): array
    {
        $s = BetTimeSetting::active()->firstWhere('session_key', $this->session);

        return $s ? ($s->group_type ?? []) : [];
    }

    public function updatedLotteryType(): void
    {
        unset($this->availableSessions);
        $sessions = $this->availableSessions;
        $first = $sessions->first(fn ($s) => ! in_array($s->sessionStatus(), ['closed', 'done'])) ?? $sessions->first();
        $this->session = $first->session_key ?? '';
        $this->updatedSession();
    }

    public function updatedSession(): void
    {
        unset($this->letters);
        $this->posts = array_values(array_intersect($this->posts, $this->letters)) ?: array_slice($this->letters, 0, 1);
        $this->flashError = null;
        $this->loadDraftBets();
    }

    public function togglePost(string $post): void
    {
        if (in_array($post, $this->posts, true)) {
            $this->posts = array_values(array_diff($this->posts, [$post]));
        } elseif (in_array($post, $this->letters, true)) {
            $this->posts[] = $post;
        }
    }

    public function addBets(string $number, string $amount, string $endNumber = '', int $mode = BetNumbers::NORMAL): void
    {
        $amt = (int) $amount;
        $numbers = BetNumbers::expand($mode, $number, $endNumber);
        $posts = array_values(array_intersect($this->posts, $this->letters));
        if (! $numbers || $amt < 100 || $amt % 100 !== 0 || ! $posts) {
            return;
        }

        $setting = BetTimeSetting::active()->firstWhere('session_key', $this->session);
        foreach ($posts as $post) {
            if ($setting && ! $setting->isBetAllowed($post, $post)) {
                $this->dispatch('bet-closed');

                return;
            }
        }

        $this->draftTicketId ??= Ticket::create([
            'user_id' => auth()->id(),
            'session' => $this->session,
            'bet_date' => today()->toDateString(),
            'total_amount' => 0,
            'invoice_number' => 'DFT-'.uniqid(),
            'status' => 'draft',
            'win_amount' => 0,
        ])->id;

        $label = match ($mode) {
            BetNumbers::MULTIPLY => $number.' X'.count($numbers),
            BetNumbers::RANGE => $number.'–'.$endNumber,
            BetNumbers::PAIR => 'K '.reset($numbers).'…'.end($numbers),
            BetNumbers::ROLL => 'W '.$number,
            default => $number,
        };

        $now = now();
        foreach ($posts as $post) {
            $lastId = (int) Bet::where('ticket_id', $this->draftTicketId)->max('id');
            $rows = array_map(fn (string $n): array => [
                'ticket_id' => $this->draftTicketId,
                'user_id' => auth()->id(),
                'bet_type' => $post,
                'letter' => $post,
                'position' => $this->position,
                'number' => $n,
                'amount' => $amt,
                'is_winner' => false,
                'win_amount' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ], $numbers);
            foreach (array_chunk($rows, 500) as $chunk) {
                Bet::insert($chunk);
            }

            // Draft ticket is private to this user, so every id above $lastId is from this insert
            $this->bets[] = [
                'ids' => Bet::where('ticket_id', $this->draftTicketId)->where('id', '>', $lastId)->pluck('id')->all(),
                'letter' => $post,
                'number' => $label,
                'amount' => (float) $amt,
                'count' => count($numbers),
            ];
        }

        $this->syncTotalAmount();
        $this->dispatch('bet-added');
    }

    public function removeBet(int $index): void
    {
        if (! isset($this->bets[$index])) {
            return;
        }

        Bet::whereIn('id', $this->bets[$index]['ids'])
            ->where('ticket_id', $this->draftTicketId)
            ->where('user_id', auth()->id())
            ->delete();

        array_splice($this->bets, $index, 1);

        if (empty($this->bets) && $this->draftTicketId) {
            Ticket::destroy($this->draftTicketId);
            $this->draftTicketId = null;
        } else {
            $this->syncTotalAmount();
        }

        $this->dispatch('bet-removed');
    }

    public function submitBets(): void
    {
        if (empty($this->bets) || ! $this->draftTicketId) {
            return;
        }

        // Re-validate cutoff at submit time
        $setting = BetTimeSetting::active()->firstWhere('session_key', $this->session);
        if ($setting) {
            foreach ($this->bets as $bet) {
                if (! $setting->isBetAllowed($bet['letter'], $bet['letter'])) {
                    $this->flashError = __('bet_closed');

                    return;
                }
            }
        }

        $totalAmount = (float) Bet::where('ticket_id', $this->draftTicketId)->sum('amount');
        $invoice = 'INV-'.now()->format('Ymd').'-'.str_pad(
            Ticket::whereDate('created_at', today())->where('status', '!=', 'draft')->count() + 1,
            4, '0', STR_PAD_LEFT
        );

        Ticket::where('id', $this->draftTicketId)->update([
            'status' => 'pending',
            'total_amount' => $totalAmount,
            'invoice_number' => $invoice,
        ]);

        $this->bets = [];
        $this->draftTicketId = null;
        $this->flashSuccess = "Invoice {$invoice} submitted. Total: ".number_format($totalAmount).' KHR';
        $this->flashError = null;
    }

    public function render(): View
    {
        return view('livewire.bet-form', [
            'letters' => $this->letters,
            'availableSessions' => $this->availableSessions,
            'lotteryTypes' => BetTimeSetting::lotteryTypes(),
            'todaySales' => (float) Ticket::where('user_id', auth()->id())
                ->whereDate('bet_date', today())
                ->where('status', '!=', 'draft')
                ->sum('total_amount'),
        ]);
    }

    private function loadDraftBets(): void
    {
        $draft = Ticket::where('user_id', auth()->id())
            ->where('session', $this->session)
            ->whereDate('bet_date', today())
            ->where('status', 'draft')
            ->first();

        $this->draftTicketId = $draft?->id;
        $this->bets = [];
        if (! $draft) {
            return;
        }

        // Rebuild list rows: consecutive ids inserted in the same second for the same post + amount
        $group = [];
        foreach ($draft->bets()->orderBy('id')->get() as $b) {
            $prev = end($group) ?: null;
            if ($prev && ($b->id !== $prev->id + 1 || $b->letter !== $prev->letter
                || (float) $b->amount !== (float) $prev->amount || ! $b->created_at->eq($prev->created_at))) {
                $this->pushGroup($group);
                $group = [];
            }
            $group[] = $b;
        }
        $this->pushGroup($group);
    }

    /** @param list<Bet> $group */
    private function pushGroup(array $group): void
    {
        if (! $group) {
            return;
        }
        $count = count($group);
        $this->bets[] = [
            'ids' => array_map(fn (Bet $b): int => $b->id, $group),
            'letter' => $group[0]->letter,
            'number' => $count > 1 ? $group[0]->number.'…'.$group[$count - 1]->number : $group[0]->number,
            'amount' => (float) $group[0]->amount,
            'count' => $count,
        ];
    }

    private function syncTotalAmount(): void
    {
        if (! $this->draftTicketId) {
            return;
        }
        Ticket::where('id', $this->draftTicketId)
            ->update(['total_amount' => Bet::where('ticket_id', $this->draftTicketId)->sum('amount')]);
    }
}
