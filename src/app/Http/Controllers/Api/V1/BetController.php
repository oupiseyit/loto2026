<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BetRequest;
use App\Models\Bet;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Ticket::with('bets')->where('user_id', $user->id);

        if ($request->filled('date')) {
            $query->whereDate('bet_date', $request->date);
        }
        if ($request->filled('session')) {
            $query->where('session', $request->session);
        }

        $tickets = $query->latest()->paginate(20);

        return response()->json(['success' => true, 'data' => $tickets]);
    }

    public function store(BetRequest $request): JsonResponse
    {
        $bets    = $request->validated('bets');
        $session = $request->validated('session');
        $betDate = $request->validated('bet_date');

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

        return response()->json([
            'success' => true,
            'message' => 'Bets submitted successfully.',
            'data'    => ['invoice_number' => $invoice, 'total_amount' => $totalAmount],
        ], 201);
    }

    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        return response()->json(['success' => true, 'data' => $ticket->load('bets')]);
    }
}
