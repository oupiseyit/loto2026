<?php

// Screen: account | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Today's sales (scoped by role)
        $salesQuery = Ticket::whereDate('bet_date', today());
        if ($user->role === 'staff') {
            $salesQuery->where('user_id', $user->id);
        } elseif ($user->role === 'master') {
            $salesQuery->whereHas('user', fn ($q) => $q->where('created_by', $user->id));
        }

        $todaySales = $salesQuery->sum('total_amount');

        // Sales breakdown by session for today
        $breakdown = (clone $salesQuery)
            ->selectRaw('session, COUNT(*) as count, SUM(total_amount) as amount')
            ->groupBy('session')
            ->get()
            ->keyBy('session');

        // Staff list for admin / master
        $staff = null;
        if ($user->role === 'admin' || $user->role === 'master') {
            $staffQuery = User::where('role', 'staff');
            if ($user->role === 'master') {
                $staffQuery->where('created_by', $user->id);
            }

            $staff = $staffQuery->withSum(
                ['tickets as today_amount' => fn ($q) => $q->whereDate('bet_date', today())],
                'total_amount'
            )->latest()->get();
        }

        return Inertia::render('Account', [
            'today_sales' => (float) $todaySales,
            'breakdown'   => $breakdown,
            'staff'       => $staff,
        ]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    /**
     * Create staff — admin or master.
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        User::create([
            'name'       => $validated['name'],
            'username'   => $validated['username'],
            'password'   => Hash::make($validated['password']),
            'role'       => 'staff',
            'created_by' => auth()->id(),
            'is_active'  => true,
        ]);

        return back()->with('success', 'Staff account created.');
    }

    /**
     * Update staff — admin: any; master: own staff only.
     */
    public function updateUser(Request $request, User $user)
    {
        $actor = auth()->user();

        // Master can only update their own staff
        if ($actor->role === 'master' && $user->created_by !== $actor->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'password'  => ['sometimes', 'nullable', 'min:6', 'confirmed'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', 'Staff updated.');
    }

    /**
     * Delete staff — admin only.
     */
    public function destroyUser(User $user)
    {
        if ($user->role === 'admin') {
            abort(403, 'Cannot delete admin account.');
        }

        $user->delete();

        return back()->with('success', 'Staff account deleted.');
    }
}
