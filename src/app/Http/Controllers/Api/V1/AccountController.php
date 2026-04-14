<?php

// Screen: account | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    /**
     * GET /api/v1/account/sales
     */
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

    /**
     * GET /api/v1/account/sales/detail
     */
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

        return response()->json([
            'success' => true,
            'data'    => $breakdown,
            'message' => 'OK',
        ]);
    }

    /**
     * PUT /api/v1/account/password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }
}
