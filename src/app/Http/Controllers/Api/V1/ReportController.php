<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class ReportController extends Controller
{
    #[OA\Get(
        path: '/report/summary',
        summary: 'Summary report — admin: all staff; master: own staff only',
        security: [['bearerAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'from',     in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date', example: '2026-05-01')),
            new OA\Parameter(name: 'to',       in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date', example: '2026-05-02')),
            new OA\Parameter(name: 'staff_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'session',  in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['morning', 'noon', 'evening'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Summary',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data',    ref: '#/components/schemas/ReportSummary'),
                ])
            ),
            new OA\Response(response: 403, description: 'Staff forbidden',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
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
            ->when($user->role === 'master', fn ($q) => $q->whereExists(
                fn ($s) => $s->select(DB::raw(1))->from('users')
                    ->whereColumn('users.id', 'tickets.user_id')
                    ->where('users.created_by', $user->id)
            ));

        $totals = $query->selectRaw(
            'COUNT(*) as total_bets,
             COALESCE(SUM(total_amount), 0) as total_amount,
             COALESCE(SUM(win_amount), 0) as total_win,
             COALESCE(SUM(total_amount) - SUM(win_amount), 0) as net'
        )->first();

        $bySession = DB::table('tickets')
            ->whereBetween('bet_date', [$from, $to])
            ->when($staffId, fn ($q) => $q->where('user_id', $staffId))
            ->when($user->role === 'master', fn ($q) => $q->whereExists(
                fn ($s) => $s->select(DB::raw(1))->from('users')
                    ->whereColumn('users.id', 'tickets.user_id')
                    ->where('users.created_by', $user->id)
            ))
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

    #[OA\Get(
        path: '/report/daily',
        summary: 'Daily sales breakdown — admin: all; master: own staff',
        security: [['bearerAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to',   in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daily breakdown',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data',    type: 'array',   items: new OA\Items(ref: '#/components/schemas/ReportDaily')),
                ])
            ),
            new OA\Response(response: 403, description: 'Staff forbidden',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function daily(Request $request): JsonResponse
    {
        $user = auth()->user();
        $from = $request->from ?? today()->subDays(30)->toDateString();
        $to   = $request->to   ?? today()->toDateString();

        $rows = DB::table('tickets')
            ->when($user->role === 'master', fn ($q) => $q->whereExists(
                fn ($s) => $s->select(DB::raw(1))->from('users')
                    ->whereColumn('users.id', 'tickets.user_id')
                    ->where('users.created_by', $user->id)
            ))
            ->whereBetween('bet_date', [$from, $to])
            ->selectRaw('DATE(bet_date) as date, COUNT(*) as bets, COALESCE(SUM(total_amount),0) as amount, COALESCE(SUM(win_amount),0) as win')
            ->groupBy('date')
            ->orderByDesc('date')
            ->get();

        return response()->json(['success' => true, 'data' => $rows, 'message' => 'OK']);
    }

    #[OA\Get(
        path: '/report/staff',
        summary: 'Per-staff performance — admin: all; master: own staff',
        security: [['bearerAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to',   in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Staff breakdown',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data',    type: 'array',   items: new OA\Items(ref: '#/components/schemas/ReportStaff')),
                ])
            ),
            new OA\Response(response: 403, description: 'Staff forbidden',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function staff(Request $request): JsonResponse
    {
        $user = auth()->user();
        $from = $request->from ?? today()->startOfMonth()->toDateString();
        $to   = $request->to   ?? today()->toDateString();

        $rows = DB::table('tickets')
            ->join('users', 'users.id', '=', 'tickets.user_id')
            ->when($user->role === 'master', fn ($q) => $q->where('users.created_by', $user->id))
            ->whereBetween('bet_date', [$from, $to])
            ->selectRaw(
                'users.id, users.name, COUNT(*) as bets,
                 COALESCE(SUM(tickets.total_amount),0) as amount,
                 COALESCE(SUM(tickets.win_amount),0) as win,
                 COALESCE(SUM(tickets.total_amount) - SUM(tickets.win_amount),0) as net'
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('amount')
            ->get();

        return response()->json(['success' => true, 'data' => $rows, 'message' => 'OK']);
    }

    #[OA\Get(
        path: '/report/export',
        summary: 'Export report data (summary JSON for mobile PDF render) — admin and master only',
        security: [['bearerAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to',   in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Export data',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data',    ref: '#/components/schemas/ReportSummary'),
                ])
            ),
        ]
    )]
    public function export(Request $request): JsonResponse
    {
        return $this->summary($request);
    }
}
