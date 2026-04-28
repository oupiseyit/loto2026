<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\Ticket;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    public function index(Request $request)
    {
        $user         = auth()->user();
        $selectedDate = $request->date ?? today()->toDateString();
        $tab          = $request->tab  ?? 'all'; // all | morning | noon | evening | winning

        // --- Available dates (scoped by role) ---
        $datesQuery = Ticket::query();
        $this->applyRoleScope($datesQuery, $user);

        $dates = $datesQuery
            ->where('status', '!=', 'draft')
            ->selectRaw('DATE(bet_date) as date')
            ->distinct()
            ->orderByDesc('date')
            ->limit(60)
            ->pluck('date');

        // --- Fetch results for the selected date to calculate wins ---
        $resultsMap = Result::where('result_date', $selectedDate)
            ->get()
            ->groupBy('session')
            ->mapWithKeys(fn ($sessions) => [
                $sessions->first()->session => $sessions->pluck('number', 'position')->toArray()
            ])
            ->toArray();

        // --- Tickets for selected date + tab ---
        $ticketsQuery = Ticket::with(['user', 'bets'])
            ->withCount('bets')
            ->where('status', '!=', 'draft')
            ->whereDate('bet_date', $selectedDate);

        $this->applyRoleScope($ticketsQuery, $user);
        $this->applyTabFilter($ticketsQuery, $tab);

        // Aggregate totals (separate query for accuracy)
        $totalsQuery = Ticket::whereDate('bet_date', $selectedDate)
            ->where('status', '!=', 'draft');
        $this->applyRoleScope($totalsQuery, $user);
        $this->applyTabFilter($totalsQuery, $tab);

        $totals = $totalsQuery->selectRaw(
            'COUNT(*) as total_count,
             COALESCE(SUM(total_amount), 0) as total_amount,
             COALESCE(SUM(win_amount), 0)   as total_win'
        )->first();

        $tickets = $ticketsQuery->latest()->paginate(20)->withQueryString();

        // --- Calculate wins if results exist ---
        if (!empty($resultsMap)) {
            $tickets->getCollection()->each(function ($ticket) use ($resultsMap) {
                $this->calculateTicketWins($ticket, $resultsMap);
            });
        }

        $view = auth()->user()->isAdmin() ? 'admin.record' : 'record';

        return view($view, [
            'dates'   => $dates,
            'tickets' => $tickets,
            'totals'  => $totals,
            'filters' => [
                'date' => $selectedDate,
                'tab'  => $tab,
            ],
        ]);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    /**
     * Calculate win amounts for a ticket by comparing bets with results.
     */
    private function calculateTicketWins($ticket, array $resultsMap): void
    {
        // If no results for this session, skip
        $sessionResults = $resultsMap[$ticket->session] ?? null;
        if (!$sessionResults) {
            return;
        }

        $totalWin = 0;
        $allBets  = $ticket->bets;

        foreach ($allBets as $bet) {
            // Check if bet number matches any result number in any position
            $isWinner = false;
            foreach ($sessionResults as $position => $resultNumber) {
                // Match bet number with result number
                if ((string)$bet->number === (string)$resultNumber) {
                    $isWinner = true;
                    break;
                }
            }

            if ($isWinner) {
                $bet->is_winner = true;
                // Calculate win amount: bet amount × 2 (for now, simple 2x payout)
                // This can be enhanced with PayoutRecord lookup
                $bet->win_amount = $bet->amount * 2;
                $totalWin += $bet->win_amount;
            } else {
                $bet->is_winner = false;
                $bet->win_amount = 0;
            }
        }

        // Update ticket with calculated win amount
        $ticket->win_amount = $totalWin;
        $ticket->status = $totalWin > 0 ? 'won' : 'lost';
    }

    private function applyRoleScope($query, $user): void
    {
        if ($user->role === 'staff') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'master') {
            $query->whereHas('user', fn ($q) => $q->where('created_by', $user->id));
        }
    }

    private function applyTabFilter($query, string $tab): void
    {
        if ($tab === 'morning') {
            $query->where('session', 'morning');
        } elseif ($tab === 'noon') {
            $query->where('session', 'noon');
        } elseif ($tab === 'evening') {
            $query->where('session', 'evening');
        } elseif ($tab === 'winning') {
            $query->where('win_amount', '>', 0);
        }
    }
}
