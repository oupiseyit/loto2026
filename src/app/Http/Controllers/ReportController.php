<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Defaults
        $from    = $request->from    ?? today()->startOfMonth()->toDateString();
        $to      = $request->to      ?? today()->toDateString();
        $staffId = $request->staff_id ? (int) $request->staff_id : null;
        $session = $request->session;

        // Staff dropdown — admin: all; master: own staff
        $staffList = User::where('role', 'staff')
            ->when($user->role === 'master', fn ($q) => $q->where('created_by', $user->id))
            ->orderBy('name')
            ->get(['id', 'name']);

        // Base scoping closure
        $scope = function ($q) use ($user, $from, $to, $staffId, $session) {
            $q->whereBetween('bet_date', [$from, $to])
              ->when($staffId, fn ($q2) => $q2->where('tickets.user_id', $staffId))
              ->when($session, fn ($q2) => $q2->where('session', $session))
              ->when(
                  $user->role === 'master',
                  fn ($q2) => $q2->whereExists(function ($sub) use ($user) {
                      $sub->select(DB::raw(1))
                          ->from('users')
                          ->whereColumn('users.id', 'tickets.user_id')
                          ->where('users.created_by', $user->id);
                  })
              );
        };

        // Summary totals
        $totals = DB::table('tickets')
            ->tap($scope)
            ->selectRaw(
                'COUNT(*) as total_tickets,
                 COALESCE(SUM(total_amount), 0) as total_amount,
                 COALESCE(SUM(win_amount), 0)   as total_win,
                 COALESCE(SUM(total_amount) - SUM(win_amount), 0) as net'
            )
            ->first();

        // Table — grouped by staff + session
        $rows = DB::table('tickets')
            ->join('users', 'users.id', '=', 'tickets.user_id')
            ->tap($scope)
            ->selectRaw(
                'users.id as user_id,
                 users.name as user_name,
                 tickets.session,
                 COUNT(*) as ticket_count,
                 COALESCE(SUM(tickets.total_amount), 0) as total_amount,
                 COALESCE(SUM(tickets.win_amount), 0)   as total_win,
                 COALESCE(SUM(tickets.total_amount) - SUM(tickets.win_amount), 0) as net'
            )
            ->groupBy('users.id', 'users.name', 'tickets.session')
            ->orderBy('users.name')
            ->orderBy('tickets.session')
            ->paginate(20)
            ->withQueryString();

        $view = auth()->user()->isAdmin() ? 'admin.report' : 'report';

        return view($view, [
            'staff_list' => $staffList,
            'rows'       => $rows,
            'totals'     => $totals,
            'filters'    => compact('from', 'to', 'staffId', 'session'),
        ]);
    }
}
