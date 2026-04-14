<?php

namespace App\Http\Controllers;

use App\Http\Requests\BetRequest;
use App\Models\Bet;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Home', [
            'today' => Carbon::today()->format('d-M-Y'),
        ]);
    }

    public function store(BetRequest $request): RedirectResponse
    {
        $bets = $request->validated('bets');
        $session = $request->validated('session');
        $betDate = $request->validated('bet_date');

        // Generate invoice number
        $invoice = 'INV-' . now()->format('Ymd') . '-' . str_pad(
            Ticket::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT
        );

        $totalAmount = collect($bets)->sum('amount');

        $ticket = Ticket::create([
            'user_id'        => auth()->id(),
            'session'        => $session,
            'bet_date'       => $betDate,
            'total_amount'   => $totalAmount,
            'invoice_number' => $invoice,
            'status'         => 'pending',
            'win_amount'     => 0,
        ]);

        foreach ($bets as $bet) {
            Bet::create([
                'ticket_id' => $ticket->id,
                'user_id'   => auth()->id(),
                'bet_type'  => $bet['bet_type'],
                'letter'    => $bet['letter'],
                'position'  => $bet['position'],
                'number'    => $bet['number'],
                'amount'    => $bet['amount'],
            ]);
        }

        return back()->with('success', "Invoice {$invoice} submitted. Total: {$totalAmount}");
    }
}
