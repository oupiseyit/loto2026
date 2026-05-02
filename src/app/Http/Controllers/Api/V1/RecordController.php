<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecordResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class RecordController extends Controller
{
    #[OA\Get(
        path: '/records',
        summary: 'Paginated bet records — staff: own; master: own staff; admin: all',
        security: [['bearerAuth' => []]],
        tags: ['Records'],
        parameters: [
            new OA\Parameter(name: 'date',    in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date', example: '2026-05-02')),
            new OA\Parameter(name: 'session', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['morning', 'noon', 'evening'])),
            new OA\Parameter(name: 'page',    in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Record list',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data',    type: 'array',   items: new OA\Items(ref: '#/components/schemas/Ticket')),
                    new OA\Property(property: 'totals', type: 'object', properties: [
                        new OA\Property(property: 'total_count',  type: 'integer', example: 10),
                        new OA\Property(property: 'total_amount', type: 'number',  example: 50000),
                        new OA\Property(property: 'total_win',    type: 'number',  example: 5000),
                    ]),
                    new OA\Property(property: 'meta', type: 'object', properties: [
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(property: 'last_page',    type: 'integer', example: 3),
                        new OA\Property(property: 'total',        type: 'integer', example: 60),
                    ]),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user    = auth()->user();
        $date    = $request->date    ?? today()->toDateString();
        $session = $request->session;

        $query = Ticket::with('user')->withCount('bets')->whereDate('bet_date', $date);

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

    #[OA\Get(
        path: '/records/winning',
        summary: 'Winning tickets only — staff: own; master: own staff; admin: all',
        security: [['bearerAuth' => []]],
        tags: ['Records'],
        parameters: [
            new OA\Parameter(name: 'date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date', example: '2026-05-02')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Winning record list',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data',    type: 'array',   items: new OA\Items(ref: '#/components/schemas/Ticket')),
                    new OA\Property(property: 'meta', type: 'object', properties: [
                        new OA\Property(property: 'current_page', type: 'integer'),
                        new OA\Property(property: 'last_page',    type: 'integer'),
                        new OA\Property(property: 'total',        type: 'integer'),
                    ]),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function winning(Request $request): JsonResponse
    {
        $user  = auth()->user();
        $date  = $request->date ?? today()->toDateString();
        $query = Ticket::with('user')->withCount('bets')
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
