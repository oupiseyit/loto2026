<?php

// Screen: result | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResultResource;
use App\Models\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ResultController extends Controller
{
    private const POSITIONS = [
        'morning' => ['A', 'B', 'C', 'D'],
        'noon'    => ['A', 'B', 'C', 'D', 'F', 'I', 'N'],
        'evening' => ['A', 'B', 'C', 'D'],
    ];

    /**
     * GET /api/v1/results?date=YYYY-MM-DD&session=morning
     */
    public function index(Request $request): JsonResponse
    {
        $date    = $request->date    ?? today()->toDateString();
        $session = $request->session ?? 'morning';

        $results = Result::where('result_date', $date)
            ->where('session', $session)
            ->orderBy('position')
            ->get();

        // Build full grid (including empty positions)
        $positions = self::POSITIONS[$session] ?? self::POSITIONS['morning'];
        $keyed     = $results->keyBy('position');
        $grid      = collect($positions)->map(fn ($pos) => [
            'position' => $pos,
            'number'   => $keyed->get($pos)?->number,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $grid,
            'message' => 'OK',
        ]);
    }

    /**
     * POST /api/v1/results — admin only
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'result_date'            => ['required', 'date'],
            'session'                => ['required', 'in:morning,noon,evening'],
            'results'                => ['required', 'array'],
            'results.*.position'     => ['required', 'string'],
            'results.*.number'       => ['required', 'string', 'max:10'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['results'] as $row) {
                Result::updateOrCreate(
                    [
                        'result_date' => $validated['result_date'],
                        'session'     => $validated['session'],
                        'position'    => $row['position'],
                    ],
                    [
                        'number'     => $row['number'],
                        'entered_by' => auth()->id(),
                    ]
                );
            }
        });

        $results = Result::where('result_date', $validated['result_date'])
            ->where('session', $validated['session'])
            ->orderBy('position')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => ResultResource::collection($results),
            'message' => 'Results saved successfully.',
        ], 201);
    }

    /**
     * PUT /api/v1/results/{id} — admin only
     */
    public function update(Request $request, Result $result): JsonResponse
    {
        $validated = $request->validate([
            'number' => ['required', 'string', 'max:10'],
        ]);

        $result->update([
            'number'     => $validated['number'],
            'entered_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'data'    => new ResultResource($result),
            'message' => 'Result updated.',
        ]);
    }
}
