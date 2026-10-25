<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResultRequest;
use App\Models\BetTimeSetting;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->date ?? today()->toDateString();
        $selectedSession = $request->session ?? (BetTimeSetting::active()->first()->session_key ?? '');
        $canEdit = auth()->user()->isAdmin();

        // Available dates (from tickets or results, last 60 days)
        $dates = DB::table('results')
            ->selectRaw('DATE(result_date) as date')
            ->union(
                DB::table('tickets')->selectRaw('DATE(bet_date) as date')
            )
            ->orderByDesc('date')
            ->limit(60)
            ->pluck('date')
            ->unique()
            ->values();

        // Existing results for selected date + session
        $results = Result::where('result_date', $selectedDate)
            ->where('session', $selectedSession)
            ->orderBy('position')
            ->get()
            ->keyBy('position');

        // Build the full position grid (fill blanks with null)
        $positions = BetTimeSetting::active()->firstWhere('session_key', $selectedSession)?->letters() ?? [];
        $grid = collect($positions)->map(fn ($pos) => [
            'position' => $pos,
            'number' => $results->get($pos)?->number,
            'id' => $results->get($pos)?->id,
        ]);

        $view = $canEdit ? 'admin.result' : 'result';

        return view($view, [
            'dates' => $dates,
            'grid' => $grid,
            'canEdit' => $canEdit,
            'filters' => [
                'date' => $selectedDate,
                'session' => $selectedSession,
            ],
        ]);
    }

    /**
     * POST /result — admin only
     * Upsert full session results (all positions at once).
     */
    public function store(ResultRequest $request)
    {
        $date = $request->result_date;
        $session = $request->session;

        DB::transaction(function () use ($request, $date, $session) {
            foreach ($request->results as $row) {
                Result::updateOrCreate(
                    [
                        'result_date' => $date,
                        'session' => $session,
                        'position' => $row['position'],
                    ],
                    [
                        'number' => $row['number'],
                        'entered_by' => auth()->id(),
                    ]
                );
            }
        });

        return back()->with('success', 'Results saved successfully.');
    }

    /**
     * PUT /result/{result} — admin only
     * Update a single position.
     */
    public function update(ResultRequest $request, Result $result)
    {
        $result->update([
            'number' => $request->results[0]['number'] ?? $request->number,
            'entered_by' => auth()->id(),
        ]);

        return back()->with('success', 'Result updated.');
    }
}
