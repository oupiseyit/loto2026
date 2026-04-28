<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data = ['today' => Carbon::today()->format('d-M-Y')];

        if ($user->isMaster()) {
            $data['staffStats'] = User::where('created_by', $user->id)
                ->where('role', 'staff')
                ->withCount(['tickets as tickets_today' => fn ($q) => $q
                    ->whereDate('bet_date', today())
                    ->where('status', '!=', 'draft')])
                ->withSum(['tickets as amount_today' => fn ($q) => $q
                    ->whereDate('bet_date', today())
                    ->where('status', '!=', 'draft')], 'total_amount')
                ->get();
        }

        if ($user->isAdmin()) {
            $last7Days = collect(range(6, 0))
                ->map(fn ($i) => today()->subDays($i)->toDateString());

            $data['chartLabels'] = $last7Days
                ->map(fn ($d) => Carbon::parse($d)->format('d M'))
                ->values()
                ->toArray();

            $data['masterStats'] = User::where('role', 'master')
                ->with(['staff:id,created_by'])
                ->get()
                ->map(function ($master) use ($last7Days) {
                    $staffIds = $master->staff->pluck('id');

                    $totals = Ticket::whereIn('user_id', $staffIds)
                        ->whereDate('bet_date', today())
                        ->where('status', '!=', 'draft')
                        ->selectRaw('COUNT(*) as tickets_today, COALESCE(SUM(total_amount),0) as amount_today')
                        ->first();

                    $daily = Ticket::whereIn('user_id', $staffIds)
                        ->where('status', '!=', 'draft')
                        ->whereBetween('bet_date', [$last7Days->first(), $last7Days->last()])
                        ->selectRaw('DATE(bet_date) as date, COALESCE(SUM(total_amount),0) as total')
                        ->groupBy('date')
                        ->pluck('total', 'date');

                    $master->staff_count   = $staffIds->count();
                    $master->tickets_today = $totals->tickets_today ?? 0;
                    $master->amount_today  = $totals->amount_today  ?? 0;
                    $master->chart_data    = $last7Days
                        ->map(fn ($d) => (float) ($daily[$d] ?? 0))
                        ->values()
                        ->toArray();

                    return $master;
                });

            // Dashboard stat cards
            $data['totalMasters'] = User::where('role', 'master')->count();
            $data['totalStaff']   = User::where('role', 'staff')->count();

            $todayTotals = Ticket::where('status', '!=', 'draft')
                ->whereDate('bet_date', today())
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as amount')
                ->first();

            $data['todayTickets'] = $todayTotals->count  ?? 0;
            $data['todayAmount']  = $todayTotals->amount ?? 0;

            return view('admin.home', $data);
        }

        return view('home', $data);
    }
}
