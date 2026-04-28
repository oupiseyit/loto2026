<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index()
    {
        $view = auth()->user()->isAdmin() ? 'admin.account' : 'account';
        return view($view);
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
