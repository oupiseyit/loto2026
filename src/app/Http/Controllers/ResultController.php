<?php

// Screen: result | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

namespace App\Http\Controllers;

use App\Http\Requests\ResultRequest;
use App\Models\Result;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ResultController extends Controller
{
    // Positions per session
    private const POSITIONS = [
        'morning' => ['A', 'B', 'C', 'D'],
        'noon'    => ['A', 'B', 'C', 'D', 'F', 'I', 'N'],
        'evening' => ['A', 'B', 'C', 'D'],
    ];

    public function index(Request $request)
    {
        $selectedDate    = $request->date    ?? today()->toDateString();
        $selectedSession = $request->session ?? 'morning';

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
        $positions = self::POSITIONS[$selectedSession] ?? self::POSITIONS['morning'];
        $grid = collect($positions)->map(fn ($pos) => [
            'position' => $pos,
            'number'   => $results->get($pos)?->number,
            'id'       => $results->get($pos)?->id,
        ]);

        return Inertia::render('Result', [
            'dates'           => $dates,
            'grid'            => $grid,
            'filters' => [
                'date'    => $selectedDate,
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
        $date    = $request->result_date;
        $session = $request->session;

        DB::transaction(function () use ($request, $date, $session) {
            foreach ($request->results as $row) {
                Result::updateOrCreate(
                    [
                        'result_date' => $date,
                        'session'     => $session,
                        'position'    => $row['position'],
                    ],
                    [
                        'number'     => $row['number'],
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
            'number'     => $request->results[0]['number'] ?? $request->number,
            'entered_by' => auth()->id(),
        ]);

        return back()->with('success', 'Result updated.');
    }
}
