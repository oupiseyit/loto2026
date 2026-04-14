<?php

// Screen: record | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecordResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    /**
     * GET /api/v1/records
     * Query params: ?date=YYYY-MM-DD&session=morning|noon|evening&page=1
     */
    public function index(Request $request): JsonResponse
    {
        $user    = auth()->user();
        $date    = $request->date    ?? today()->toDateString();
        $session = $request->session;

        $query = Ticket::with('user')
            ->withCount('bets')
            ->whereDate('bet_date', $date);

        // Role scoping
        match ($user->role) {
            'staff'  => $query->where('user_id', $user->id),
            'master' => $query->whereHas('user', fn ($q) => $q->where('created_by', $user->id)),
            default  => null,
        };

        if ($session) {
            $query->where('session', $session);
        }

        $tickets = $query->latest()->paginate(20);

        $totals = Ticket::whereDate('bet_date', $date)
            ->when($user->role === 'staff',  fn ($q) => $q->where('user_id', $user->id))
            ->when($user->role === 'master', fn ($q) => $q->whereHas('user', fn ($u) => $u->where('created_by', $user->id)))
            ->when($session, fn ($q) => $q->where('session', $session))
            ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(total_amount),0) as total_amount, COALESCE(SUM(win_amount),0) as total_win')
            ->first();

        return response()->json([
            'success' => true,
            'data'    => RecordResource::collection($tickets->items()),
            'totals'  => $totals,
            'meta'    => [
                'current_page' => $tickets->currentPage(),
                'last_page'    => $tickets->lastPage(),
                'total'        => $tickets->total(),
            ],
            'message' => 'OK',
        ]);
    }

    /**
     * GET /api/v1/records/winning
     * Winning tickets only.
     */
    public function winning(Request $request): JsonResponse
    {
        $user = auth()->user();
        $date = $request->date ?? today()->toDateString();

        $query = Ticket::with('user')
            ->withCount('bets')
            ->whereDate('bet_date', $date)
            ->where('win_amount', '>', 0);

        match ($user->role) {
            'staff'  => $query->where('user_id', $user->id),
            'master' => $query->whereHas('user', fn ($q) => $q->where('created_by', $user->id)),
            default  => null,
        };

        $tickets = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => RecordResource::collection($tickets->items()),
            'meta'    => [
                'current_page' => $tickets->currentPage(),
                'last_page'    => $tickets->lastPage(),
                'total'        => $tickets->total(),
            ],
            'message' => 'OK',
        ]);
    }
}
