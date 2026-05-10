<?php

namespace App\Livewire;

use App\DTOs\BetItemDTO;
use App\Models\Bet;
use App\Models\BetTimeSetting;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BetForm extends Component
{
    public string $today = '';

    public string $session = '';

    public string $letter = '';

    public string $position = 'X';

    public int $betMode = 1;  // 1=Normal 2=Head 3=Tail 4=Tail2 5=Head2

    public array $bets = [];

    public ?int $draftTicketId = null;

    public ?string $flashSuccess = null;

    public ?string $flashError = null;

    public function mount(string $today = ''): void
    {
        $this->today = $today;

        // Default to first active session
        $first = BetTimeSetting::active()->first();
        $this->session = $first ? $first->session_key : 'morning';
        $this->letter = ($first->group_type ?? [])[0] ?? '';

        $this->loadDraftBets();
    }

    #[Computed]
    public function availableSessions(): Collection
    {
        return BetTimeSetting::active();
    }

    #[Computed]
    public function letters(): array
    {
        $s = $this->availableSessions->firstWhere('session_key', $this->session);

        return $s ? ($s->group_type ?? []) : [];
    }

    public function updatedSession(): void
    {
        $types = $this->letters();
        if (! in_array($this->letter, $types)) {
            $this->letter = $types[0] ?? '';
        }
        $this->flashError = null;
        $this->loadDraftBets();
    }

    public function addBet(string $number, string $amount): void
    {
        if ($number === '' || $amount === '' || (float) $amount <= 0) {
            return;
        }

        $betType = $this->resolveBetType($this->letter);
        $setting = $this->availableSessions->firstWhere('session_key', $this->session);
        if ($setting && ! $setting->isBetAllowed($this->letter, $betType)) {
            $this->dispatch('bet-closed');

            return;
        }

        if (! $this->draftTicketId) {
            $ticket = Ticket::create([
                'user_id' => auth()->id(),
                'session' => $this->session,
                'bet_date' => today()->toDateString(),
                'total_amount' => 0,
                'invoice_number' => 'DFT-'.uniqid(),
                'status' => 'draft',
                'win_amount' => 0,
            ]);
            $this->draftTicketId = $ticket->id;
        }

        $dto = new BetItemDTO($betType, $this->letter, $this->position, $number, (float) $amount);

        $bet = Bet::create($dto->toModelArray($this->draftTicketId, auth()->id()));
        $this->syncTotalAmount();

        $this->bets[] = [
            'id' => $bet->id,
            'bet_type' => $dto->betType,
            'letter' => $dto->letter,
            'position' => $dto->position,
            'number' => $dto->number,
            'amount' => $dto->amount,
        ];

        $this->dispatch('bet-added');
    }

    public function removeBet(string $id): void
    {
        if (str_starts_with($id, 'range_')) {
            // ID format: range_{start}_{end}_{step}_{padLen}
            $parts = explode('_', $id);
            $start = (int) $parts[1];
            $end = (int) $parts[2];
            $step = (int) ($parts[3] ?? 1);
            $padLen = (int) ($parts[4] ?? 3);

            $numbers = [];
            for ($n = $start; $n <= $end; $n += $step) {
                $numbers[] = str_pad($n, $padLen, '0', STR_PAD_LEFT);
            }
            Bet::where('ticket_id', $this->draftTicketId)
                ->where('user_id', auth()->id())
                ->whereIn('number', $numbers)
                ->delete();
        } else {
            Bet::where('id', (int) $id)->where('user_id', auth()->id())->delete();
        }

        $this->bets = array_values(array_filter($this->bets, fn ($b) => (string) $b['id'] !== $id));

        if (empty($this->bets) && $this->draftTicketId) {
            Ticket::destroy($this->draftTicketId);
            $this->draftTicketId = null;
        } else {
            $this->syncTotalAmount();
        }

        $this->dispatch('bet-removed');
    }

    public function addRangeBet(string $startNumber, string $amount, string $endNumber = ''): void
    {
        if ($startNumber === '' || $amount === '' || (float) $amount <= 0) {
            return;
        }

        $betType = $this->resolveBetType($this->letter);
        $setting = $this->availableSessions->firstWhere('session_key', $this->session);
        if ($setting && ! $setting->isBetAllowed($this->letter, $betType)) {
            $this->dispatch('bet-closed');

            return;
        }

        $start = (int) $startNumber;
        if ($this->betMode === 2) {
            // Head: same units digit, step through decades; end is user-editable
            $maxEnd = 90 + ($start % 10);
            $end = $endNumber !== '' ? min((int) $endNumber, $maxEnd) : $maxEnd;
            $step = 10;
            $padLen = 2;
        } elseif ($this->betMode === 3) {
            // Tail: consecutive within same decade (X0–X9)
            $end = (int) floor($start / 10) * 10 + 9;
            $step = 1;
            $padLen = 2;
        } elseif ($this->betMode === 4) {
            // Tail 2: consecutive within same century (X00–X99)
            $end = (int) floor($start / 100) * 100 + 99;
            $step = 1;
            $padLen = 3;
        } else {
            // Head 2: same units digit, step through 3-digit decades; end is user-editable
            $maxEnd = 990 + ($start % 10);
            $end = $endNumber !== '' ? min((int) $endNumber, $maxEnd) : $maxEnd;
            $step = 10;
            $padLen = 3;
        }

        if (! $this->draftTicketId) {
            $ticket = Ticket::create([
                'user_id' => auth()->id(),
                'session' => $this->session,
                'bet_date' => today()->toDateString(),
                'total_amount' => 0,
                'invoice_number' => 'DFT-'.uniqid(),
                'status' => 'draft',
                'win_amount' => 0,
            ]);
            $this->draftTicketId = $ticket->id;
        }

        $now = now();
        $rows = [];
        $count = 0;
        for ($n = $start; $n <= $end; $n += $step) {
            $rows[] = [
                'ticket_id' => $this->draftTicketId,
                'user_id' => auth()->id(),
                'bet_type' => $betType,
                'letter' => $this->letter,
                'position' => $this->position,
                'number' => str_pad($n, $padLen, '0', STR_PAD_LEFT),
                'amount' => (float) $amount,
                'is_winner' => false,
                'win_amount' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $count++;
        }
        Bet::insert($rows);
        $this->syncTotalAmount();

        // ID encodes step and padLen so removeBet can reconstruct the exact number list
        $this->bets[] = [
            'id' => "range_{$start}_{$end}_{$step}_{$padLen}",
            'bet_type' => $betType,
            'letter' => $this->letter,
            'position' => $this->position,
            'number' => str_pad($start, $padLen, '0', STR_PAD_LEFT).'–'.str_pad($end, $padLen, '0', STR_PAD_LEFT),
            'amount' => (float) $amount * $count,
        ];

        $this->dispatch('bet-added');
    }

    public function submitBets(): void
    {
        if (empty($this->bets) || ! $this->draftTicketId) {
            return;
        }

        // Re-validate cutoff at submit time
        $setting = $this->availableSessions->firstWhere('session_key', $this->session);
        if ($setting) {
            foreach ($this->bets as $bet) {
                if (! $setting->isBetAllowed($bet['letter'], $bet['bet_type'])) {
                    $this->flashError = __('bet_closed');

                    return;
                }
            }
        }

        $totalAmount = collect($this->bets)->sum('amount');
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
        $this->flashSuccess = "Invoice {$invoice} submitted. Total: ".number_format($totalAmount);
        $this->flashError = null;
    }

    public function render(): View
    {
        return view('livewire.bet-form', [
            'letters' => $this->letters(),
            'availableSessions' => $this->availableSessions,
        ]);
    }

    private function resolveBetType(string $letter): string
    {
        return $letter;
    }

    private function loadDraftBets(): void
    {
        $draft = Ticket::where('user_id', auth()->id())
            ->where('session', $this->session)
            ->whereDate('bet_date', today())
            ->where('status', 'draft')
            ->first();

        if (! $draft) {
            $this->draftTicketId = null;
            $this->bets = [];

            return;
        }

        $this->draftTicketId = $draft->id;

        $allBets = $draft->bets()->orderBy('id')->get()->values();
        $total = $allBets->count();
        $this->bets = [];
        $i = 0;

        while ($i < $total) {
            $first = $allBets[$i];
            $j = $i + 1;
            $createdAt = $first->created_at->format('Y-m-d H:i:s');

            // Extend the group: consecutive IDs + same second + same bet attributes
            while (
                $j < $total &&
                $allBets[$j]->id - $allBets[$j - 1]->id === 1 &&
                $allBets[$j]->created_at->format('Y-m-d H:i:s') === $createdAt &&
                $allBets[$j]->bet_type === $first->bet_type &&
                $allBets[$j]->letter === $first->letter &&
                $allBets[$j]->position === $first->position &&
                (float) $allBets[$j]->amount === (float) $first->amount
            ) {
                $j++;
            }

            $group = $allBets->slice($i, $j - $i)->values();

            if ($group->count() > 1) {
                $nums = $group->map(fn ($b) => (int) $b->number)->sort()->values()->toArray();
                $padLen = strlen($first->number);
                $step = $nums[1] - $nums[0];
                $valid = $step > 0;
                for ($k = 1; $valid && $k < count($nums); $k++) {
                    if ($step !== $nums[$k] - $nums[$k - 1]) {
                        $valid = false;
                    }
                }

                if ($valid) {
                    $start = $nums[0];
                    $end = $nums[count($nums) - 1];
                    $count = count($nums);
                    $this->bets[] = [
                        'id' => "range_{$start}_{$end}_{$step}_{$padLen}",
                        'bet_type' => $first->bet_type,
                        'letter' => $first->letter,
                        'position' => $first->position,
                        'number' => str_pad($start, $padLen, '0', STR_PAD_LEFT).'–'.str_pad($end, $padLen, '0', STR_PAD_LEFT),
                        'amount' => (float) $first->amount * $count,
                    ];
                    $i = $j;

                    continue;
                }
            }

            // Single bet or group that doesn't form a clean range — add individually
            for ($k = $i; $k < $j; $k++) {
                $b = $allBets[$k];
                $this->bets[] = [
                    'id' => $b->id,
                    'bet_type' => $b->bet_type,
                    'letter' => $b->letter,
                    'position' => $b->position,
                    'number' => $b->number,
                    'amount' => (float) $b->amount,
                ];
            }
            $i = $j;
        }
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
