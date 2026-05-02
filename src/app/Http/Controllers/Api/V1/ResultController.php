<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResultResource;
use App\Models\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class ResultController extends Controller
{
    private const POSITIONS = [
        'morning' => ['A', 'B', 'C', 'D'],
        'noon'    => ['A', 'B', 'C', 'D', 'F', 'I', 'N'],
        'evening' => ['A', 'B', 'C', 'D'],
    ];

    #[OA\Get(
        path: '/results',
        summary: 'Get result grid for a date and session — all roles',
        security: [['bearerAuth' => []]],
        tags: ['Results'],
        parameters: [
            new OA\Parameter(name: 'date',    in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date',   example: '2026-05-02')),
            new OA\Parameter(name: 'session', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['morning', 'noon', 'evening'], example: 'morning')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Result grid',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data',    type: 'array',   items: new OA\Items(ref: '#/components/schemas/ResultItem')),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $date    = $request->date    ?? today()->toDateString();
        $session = $request->session ?? 'morning';

        $results   = Result::where('result_date', $date)->where('session', $session)->orderBy('position')->get();
        $positions = self::POSITIONS[$session] ?? self::POSITIONS['morning'];
        $keyed     = $results->keyBy('position');
        $grid      = collect($positions)->map(fn ($pos) => [
            'position' => $pos,
            'number'   => $keyed->get($pos)?->number,
        ]);

        return response()->json(['success' => true, 'data' => $grid, 'message' => 'OK']);
    }

    #[OA\Post(
        path: '/results',
        summary: 'Enter results for a session — admin only',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['result_date', 'session', 'results'],
                properties: [
                    new OA\Property(property: 'result_date', type: 'string', format: 'date', example: '2026-05-02'),
                    new OA\Property(property: 'session',     type: 'string', enum: ['morning', 'noon', 'evening']),
                    new OA\Property(property: 'results', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'position', type: 'string', example: 'A'),
                            new OA\Property(property: 'number',   type: 'string', example: '25'),
                        ]
                    )),
                ]
            )
        ),
        security: [['bearerAuth' => []]],
        tags: ['Results'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Results saved',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data',    type: 'array',   items: new OA\Items(ref: '#/components/schemas/ResultItem')),
                ])
            ),
            new OA\Response(response: 403, description: 'Admin only',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(response: 422, description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'result_date'        => ['required', 'date'],
            'session'            => ['required', 'in:morning,noon,evening'],
            'results'            => ['required', 'array'],
            'results.*.position' => ['required', 'string'],
            'results.*.number'   => ['required', 'string', 'max:10'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['results'] as $row) {
                Result::updateOrCreate(
                    ['result_date' => $validated['result_date'], 'session' => $validated['session'], 'position' => $row['position']],
                    ['number' => $row['number'], 'entered_by' => auth()->id()]
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

    #[OA\Put(
        path: '/results/{id}',
        summary: 'Update a single result entry — admin only',
        security: [['bearerAuth' => []]],
        tags: ['Results'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['number'],
                properties: [new OA\Property(property: 'number', type: 'string', example: '42')]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Result updated',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data',    ref: '#/components/schemas/ResultItem'),
                ])
            ),
            new OA\Response(response: 403, description: 'Admin only',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(response: 404, description: 'Not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function update(Request $request, Result $result): JsonResponse
    {
        $validated = $request->validate(['number' => ['required', 'string', 'max:10']]);

        $result->update(['number' => $validated['number'], 'entered_by' => auth()->id()]);

        return response()->json(['success' => true, 'data' => new ResultResource($result), 'message' => 'Result updated.']);
    }
}
