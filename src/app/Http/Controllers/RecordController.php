<?php

// Screen: record | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
            ->selectRaw('DATE(bet_date) as date')
            ->distinct()
            ->orderByDesc('date')
            ->limit(60)
            ->pluck('date');

        // --- Tickets for selected date + tab ---
        $ticketsQuery = Ticket::with(['user'])
            ->withCount('bets')
            ->whereDate('bet_date', $selectedDate);

        $this->applyRoleScope($ticketsQuery, $user);
        $this->applyTabFilter($ticketsQuery, $tab);

        // Aggregate totals (separate query for accuracy)
        $totalsQuery = Ticket::whereDate('bet_date', $selectedDate);
        $this->applyRoleScope($totalsQuery, $user);
        $this->applyTabFilter($totalsQuery, $tab);

        $totals = $totalsQuery->selectRaw(
            'COUNT(*) as total_count,
             COALESCE(SUM(total_amount), 0) as total_amount,
             COALESCE(SUM(win_amount), 0)   as total_win'
        )->first();

        $tickets = $ticketsQuery->latest()->paginate(20)->withQueryString();

        return Inertia::render('Record', [
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

    private function applyRoleScope($query, $user): void
    {
        match ($user->role) {
            'staff'  => $query->where('user_id', $user->id),
            'master' => $query->whereHas('user', fn ($q) => $q->where('created_by', $user->id)),
            default  => null,
        };
    }

    private function applyTabFilter($query, string $tab): void
    {
        match ($tab) {
            'morning' => $query->where('session', 'morning'),
            'noon'    => $query->where('session', 'noon'),
            'evening' => $query->where('session', 'evening'),
            'winning' => $query->where('win_amount', '>', 0),
            default   => null,
        };
    }
}
