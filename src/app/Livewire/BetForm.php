<?php

namespace App\Livewire;

use App\DTOs\BetItemDTO;
use App\Models\Bet;
use App\Models\Ticket;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BetForm extends Component
{
    public string $today   = '';
    public string $session = 'morning';
    public string $betType = 'ABCD';
    public string $letter  = 'A';
    public string $position = 'X';

    public array  $bets          = [];
    public ?int   $draftTicketId = null;
    public ?string $flashSuccess = null;

    public function mount(string $today = ''): void
    {
        $this->today = $today;
        $this->loadDraftBets();
    }

    #[Computed]
    public function letters(): array
    {
        return $this->session === 'noon'
            ? ['A', 'B', 'C', 'D', 'F', 'I', 'N']
            : ['A', 'B', 'C', 'D'];
    }

    public function updatedSession(): void
    {
        if ($this->session !== 'noon' && ! in_array($this->letter, ['A', 'B', 'C', 'D'])) {
            $this->letter = 'A';
        }
        $this->loadDraftBets();
    }

    public function addBet(string $number, string $amount): void
    {
        if ($number === '' || $amount === '' || (float) $amount <= 0) {
            return;
        }

        // Create draft ticket on first bet of this session
        if (! $this->draftTicketId) {
            $ticket = Ticket::create([
                'user_id'        => auth()->id(),
                'session'        => $this->session,
                'bet_date'       => today()->toDateString(),
                'total_amount'   => 0,
                'invoice_number' => 'DFT-' . uniqid(),
                'status'         => 'draft',
                'win_amount'     => 0,
            ]);
            $this->draftTicketId = $ticket->id;
        }

        $dto = new BetItemDTO(
            betType:  $this->betType,
            letter:   $this->letter,
            position: $this->position,
            number:   $number,
            amount:   (float) $amount,
        );

        $bet = Bet::create($dto->toModelArray($this->draftTicketId, auth()->id()));

        $this->syncTotalAmount();

        $this->bets[] = [
            'id'       => $bet->id,
            'bet_type' => $dto->betType,
            'letter'   => $dto->letter,
            'position' => $dto->position,
            'number'   => $dto->number,
            'amount'   => $dto->amount,
        ];
    }

    public function removeBet(int $id): void
    {
        Bet::where('id', $id)->where('user_id', auth()->id())->delete();
        $this->bets = array_values(array_filter($this->bets, fn ($b) => $b['id'] !== $id));

        if (empty($this->bets) && $this->draftTicketId) {
            Ticket::destroy($this->draftTicketId);
            $this->draftTicketId = null;
        } else {
            $this->syncTotalAmount();
        }
    }

    public function submitBets(): void
    {
        if (empty($this->bets) || ! $this->draftTicketId) {
            return;
        }

        $totalAmount = collect($this->bets)->sum('amount');

        $invoice = 'INV-' . now()->format('Ymd') . '-' . str_pad(
            Ticket::whereDate('created_at', today())->where('status', '!=', 'draft')->count() + 1,
            4, '0', STR_PAD_LEFT
        );

        Ticket::where('id', $this->draftTicketId)->update([
            'status'         => 'pending',
            'total_amount'   => $totalAmount,
            'invoice_number' => $invoice,
        ]);

        $this->bets          = [];
        $this->draftTicketId = null;
        $this->flashSuccess  = "Invoice {$invoice} submitted. Total: " . number_format($totalAmount);
    }

    public function render()
    {
        return view('livewire.bet-form', [
            'letters' => $this->letters(),
        ]);
    }

    // ── Private helpers ──────────────────────────────────────────

    private function loadDraftBets(): void
    {
        $draft = Ticket::where('user_id', auth()->id())
            ->where('session', $this->session)
            ->whereDate('bet_date', today())
            ->where('status', 'draft')
            ->first();

        if ($draft) {
            $this->draftTicketId = $draft->id;
            $this->bets = $draft->bets()
                ->get()
                ->map(fn ($b) => [
                    'id'       => $b->id,
                    'bet_type' => $b->bet_type,
                    'letter'   => $b->letter,
                    'position' => $b->position,
                    'number'   => $b->number,
                    'amount'   => (float) $b->amount,
                ])
                ->toArray();
        } else {
            $this->draftTicketId = null;
            $this->bets          = [];
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
