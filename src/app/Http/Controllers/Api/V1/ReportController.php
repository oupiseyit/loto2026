<?php

// Screen: report | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * GET /api/v1/reports/summary
     * Query params: ?from=&to=&staff_id=&session=
     */
    public function summary(Request $request): JsonResponse
    {
        $user    = auth()->user();
        $from    = $request->from    ?? today()->startOfMonth()->toDateString();
        $to      = $request->to      ?? today()->toDateString();
        $staffId = $request->staff_id;
        $session = $request->session;

        $query = DB::table('tickets')
            ->whereBetween('bet_date', [$from, $to])
            ->when($staffId, fn ($q) => $q->where('user_id', $staffId))
            ->when($session,  fn ($q) => $q->where('session', $session))
            ->when(
                $user->role === 'master',
                fn ($q) => $q->whereExists(fn ($s) =>
                    $s->select(DB::raw(1))->from('users')
                      ->whereColumn('users.id', 'tickets.user_id')
                      ->where('users.created_by', $user->id)
                )
            );

        $totals = $query->selectRaw(
            'COUNT(*) as total_bets,
             COALESCE(SUM(total_amount), 0) as total_amount,
             COALESCE(SUM(win_amount), 0) as total_win,
             COALESCE(SUM(total_amount) - SUM(win_amount), 0) as net'
        )->first();

        // By session
        $bySession = DB::table('tickets')
            ->whereBetween('bet_date', [$from, $to])
            ->when($staffId, fn ($q) => $q->where('user_id', $staffId))
            ->when(
                $user->role === 'master',
                fn ($q) => $q->whereExists(fn ($s) =>
                    $s->select(DB::raw(1))->from('users')
                      ->whereColumn('users.id', 'tickets.user_id')
                      ->where('users.created_by', $user->id)
                )
            )
            ->selectRaw('session, COUNT(*) as bets, COALESCE(SUM(total_amount),0) as amount')
            ->groupBy('session')
            ->get()
            ->keyBy('session');

        return response()->json([
            'success' => true,
            'data'    => [
                'total_bets'   => $totals->total_bets,
                'total_amount' => (float) $totals->total_amount,
                'total_win'    => (float) $totals->total_win,
                'net'          => (float) $totals->net,
                'by_session'   => $bySession,
            ],
            'message' => 'OK',
        ]);
    }

    /**
     * GET /api/v1/reports/daily
     */
    public function daily(Request $request): JsonResponse
    {
        $user = auth()->user();
        $from = $request->from ?? today()->subDays(30)->toDateString();
        $to   = $request->to   ?? today()->toDateString();

        $rows = DB::table('tickets')
            ->when(
                $user->role === 'master',
                fn ($q) => $q->whereExists(fn ($s) =>
                    $s->select(DB::raw(1))->from('users')
                      ->whereColumn('users.id', 'tickets.user_id')
                      ->where('users.created_by', $user->id)
                )
            )
            ->whereBetween('bet_date', [$from, $to])
            ->selectRaw(
                'DATE(bet_date) as date,
                 COUNT(*) as bets,
                 COALESCE(SUM(total_amount),0) as amount,
                 COALESCE(SUM(win_amount),0) as win'
            )
            ->groupBy('date')
            ->orderByDesc('date')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $rows,
            'message' => 'OK',
        ]);
    }

    /**
     * GET /api/v1/reports/staff
     */
    public function staff(Request $request): JsonResponse
    {
        $user = auth()->user();
        $from = $request->from ?? today()->startOfMonth()->toDateString();
        $to   = $request->to   ?? today()->toDateString();

        $rows = DB::table('tickets')
            ->join('users', 'users.id', '=', 'tickets.user_id')
            ->when(
                $user->role === 'master',
                fn ($q) => $q->where('users.created_by', $user->id)
            )
            ->whereBetween('bet_date', [$from, $to])
            ->selectRaw(
                'users.id, users.name,
                 COUNT(*) as bets,
                 COALESCE(SUM(tickets.total_amount),0) as amount,
                 COALESCE(SUM(tickets.win_amount),0) as win,
                 COALESCE(SUM(tickets.total_amount) - SUM(tickets.win_amount),0) as net'
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('amount')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $rows,
            'message' => 'OK',
        ]);
    }

    /**
     * GET /api/v1/reports/export
     * Returns JSON for mobile to render PDF.
     */
    public function export(Request $request): JsonResponse
    {
        return $this->summary($request);
    }
}
