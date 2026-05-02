<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AccountController extends Controller
{
    #[OA\Get(
        path: '/account',
        summary: 'Today\'s sales totals — staff: own; master: own staff; admin: all',
        security: [['bearerAuth' => []]],
        tags: ['Account'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sales summary',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data', type: 'object', properties: [
                        new OA\Property(property: 'today_sales', type: 'number',  example: 150000),
                        new OA\Property(property: 'today_win',   type: 'number',  example: 20000),
                        new OA\Property(property: 'today_count', type: 'integer', example: 10),
                    ]),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function sales(): JsonResponse
    {
        $user  = auth()->user();
        $query = Ticket::whereDate('bet_date', today());

        match ($user->role) {
            'staff'  => $query->where('user_id', $user->id),
            'master' => $query->whereHas('user', fn ($q) => $q->where('created_by', $user->id)),
            default  => null,
        };

        return response()->json([
            'success' => true,
            'data'    => [
                'today_sales' => (float) $query->sum('total_amount'),
                'today_win'   => (float) $query->sum('win_amount'),
                'today_count' => $query->count(),
            ],
            'message' => 'OK',
        ]);
    }

    #[OA\Get(
        path: '/account/sales/detail',
        summary: 'Today\'s sales breakdown by session — staff: own; master: own staff; admin: all',
        security: [['bearerAuth' => []]],
        tags: ['Account'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sales breakdown',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'session', type: 'string',  enum: ['morning', 'noon', 'evening']),
                            new OA\Property(property: 'count',   type: 'integer', example: 10),
                            new OA\Property(property: 'amount',  type: 'number',  example: 50000),
                            new OA\Property(property: 'win',     type: 'number',  example: 5000),
                        ]
                    )),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function salesDetail(): JsonResponse
    {
        $user  = auth()->user();
        $query = Ticket::whereDate('bet_date', today());

        match ($user->role) {
            'staff'  => $query->where('user_id', $user->id),
            'master' => $query->whereHas('user', fn ($q) => $q->where('created_by', $user->id)),
            default  => null,
        };

        $breakdown = $query
            ->selectRaw('session, COUNT(*) as count, SUM(total_amount) as amount, SUM(win_amount) as win')
            ->groupBy('session')
            ->get();

        return response()->json(['success' => true, 'data' => $breakdown, 'message' => 'OK']);
    }

    #[OA\Post(
        path: '/account/password',
        summary: 'Change own password — all roles',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'current_password',      type: 'string', example: 'oldpass'),
                    new OA\Property(property: 'password',              type: 'string', example: 'newpass123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'newpass123'),
                ]
            )
        ),
        security: [['bearerAuth' => []]],
        tags: ['Account'],
        responses: [
            new OA\Response(response: 200, description: 'Password changed',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')
            ),
            new OA\Response(response: 422, description: 'Wrong current password or validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
    }
}
