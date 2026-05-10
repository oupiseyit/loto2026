<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\PlaceBetDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\BetRequest;
use App\Models\Bet;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BetController extends Controller
{
    #[OA\Get(
        path: '/bets',
        summary: 'List today\'s tickets — staff/master: own only; admin: all',
        security: [['bearerAuth' => []]],
        tags: ['Bets'],
        parameters: [
            new OA\Parameter(name: 'date',    in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date',   example: '2026-05-02')),
            new OA\Parameter(name: 'session', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['morning', 'noon', 'evening'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ticket list',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Ticket')),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Ticket::with('bets')->where('user_id', $user->id);

        if ($request->filled('date')) {
            $query->whereDate('bet_date', $request->date);
        }
        if ($request->filled('session')) {
            $query->where('session', $request->session);
        }

        return response()->json(['success' => true, 'data' => $query->latest()->paginate(20)]);
    }

    #[OA\Post(
        path: '/bets',
        summary: 'Submit a new ticket with bets — all roles',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['session', 'bet_date', 'bets'],
                properties: [
                    new OA\Property(property: 'session',  type: 'string', enum: ['morning', 'noon', 'evening']),
                    new OA\Property(property: 'bet_date', type: 'string', format: 'date', example: '2026-05-02'),
                    new OA\Property(property: 'bets', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'bet_type', type: 'string', enum: ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'Lo23', 'Lo25', 'Lo27']),
                            new OA\Property(property: 'letter',   type: 'string', enum: ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'Lo23', 'Lo25', 'Lo27']),
                            new OA\Property(property: 'position', type: 'string', enum: ['X', 'W', 'H', 'W*']),
                            new OA\Property(property: 'number',   type: 'string', example: '25'),
                            new OA\Property(property: 'amount',   type: 'number', example: 5000),
                        ]
                    )),
                ]
            )
        ),
        security: [['bearerAuth' => []]],
        tags: ['Bets'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Ticket created',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string',  example: 'Bets submitted successfully.'),
                    new OA\Property(property: 'data', type: 'object', properties: [
                        new OA\Property(property: 'invoice_number', type: 'string', example: 'INV-20260502-0001'),
                        new OA\Property(property: 'total_amount',   type: 'number', example: 15000),
                    ]),
                ])
            ),
            new OA\Response(response: 422, description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function store(BetRequest $request): JsonResponse
    {
        $dto = PlaceBetDTO::fromRequest($request);

        $invoice = 'INV-' . now()->format('Ymd') . '-' . str_pad(
            Ticket::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT
        );

        $ticket = Ticket::create([
            'user_id'        => auth()->id(),
            'session'        => $dto->session,
            'bet_date'       => $dto->betDate,
            'total_amount'   => $dto->totalAmount(),
            'invoice_number' => $invoice,
            'status'         => 'pending',
            'win_amount'     => 0,
        ]);

        foreach ($dto->bets as $betItem) {
            Bet::create($betItem->toModelArray($ticket->id, auth()->id()));
        }

        return response()->json([
            'success' => true,
            'message' => 'Bets submitted successfully.',
            'data'    => ['invoice_number' => $invoice, 'total_amount' => $dto->totalAmount()],
        ], 201);
    }

    #[OA\Get(
        path: '/bets/{id}',
        summary: 'Get a ticket with its bets — own only',
        security: [['bearerAuth' => []]],
        tags: ['Bets'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ticket detail',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data', ref: '#/components/schemas/Ticket'),
                ])
            ),
            new OA\Response(response: 403, description: 'Forbidden',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(response: 404, description: 'Not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        return response()->json(['success' => true, 'data' => $ticket->load('bets')]);
    }
}
