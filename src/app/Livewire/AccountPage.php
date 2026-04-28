<?php

namespace App\Livewire;

use App\Models\Currency;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AccountPage extends Component
{
    // Password change form
    public string $currentPassword = '';
    public string $newPassword     = '';
    public string $confirmPassword = '';

    // Create modal (shared for staff & master)
    public bool   $showCreateModal = false;
    public string $newRole         = 'staff';   // 'staff' | 'master'
    public string $newName         = '';
    public string $newUsername     = '';
    public string $newStaffPass    = '';
    public string $newStaffConfirm = '';
    public string $newLocale       = 'en';
    public ?int   $newCurrencyId   = null;

    // Edit modal
    public bool   $showEditModal  = false;
    public ?int   $editUserId     = null;
    public string $editUserRole   = 'staff';
    public string $editName       = '';
    public bool   $editIsActive   = true;
    public string $editPassword   = '';
    public string $editConfirm    = '';

    // ── Computed ──────────────────────────────────────────────────────

    #[Computed]
    public function todaySales(): float
    {
        $user  = auth()->user();
        $query = Ticket::whereDate('bet_date', today())->where('status', '!=', 'draft');

        if ($user->role === 'staff') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'master') {
            $query->whereHas('user', fn ($q) => $q->where('created_by', $user->id));
        }

        return (float) $query->sum('total_amount');
    }

    #[Computed]
    public function breakdown(): array
    {
        $user  = auth()->user();
        $query = Ticket::whereDate('bet_date', today())->where('status', '!=', 'draft');

        if ($user->role === 'staff') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'master') {
            $query->whereHas('user', fn ($q) => $q->where('created_by', $user->id));
        }

        return $query
            ->selectRaw('session, COUNT(*) as count, SUM(total_amount) as amount')
            ->groupBy('session')
            ->get()
            ->keyBy('session')
            ->map(fn ($row) => ['count' => $row->count, 'amount' => $row->amount])
            ->toArray();
    }

    #[Computed]
    public function masters(): ?array
    {
        if (auth()->user()->role !== 'admin') {
            return null;
        }

        return User::where('role', 'master')
            ->withCount('staff')
            ->latest()
            ->get()
            ->map(fn ($m) => [
                'id'          => $m->id,
                'name'        => $m->name,
                'username'    => $m->username,
                'is_active'   => (bool) $m->is_active,
                'staff_count' => $m->staff_count,
            ])
            ->toArray();
    }

    #[Computed]
    public function staff(): ?array
    {
        $user = auth()->user();

        if (! in_array($user->role, ['admin', 'master'])) {
            return null;
        }

        $query = User::where('role', 'staff');
        if ($user->role === 'master') {
            $query->where('created_by', $user->id);
        }

        return $query
            ->withSum(
                ['tickets as today_amount' => fn ($q) => $q->whereDate('bet_date', today())->where('status', '!=', 'draft')],
                'total_amount'
            )
            ->latest()
            ->get()
            ->map(fn ($s) => [
                'id'           => $s->id,
                'name'         => $s->name,
                'username'     => $s->username,
                'is_active'    => (bool) $s->is_active,
                'today_amount' => (float) ($s->today_amount ?? 0),
            ])
            ->toArray();
    }

    // ── Actions ───────────────────────────────────────────────────────

    public function changePassword(): void
    {
        $this->validate([
            'currentPassword' => ['required'],
            'newPassword'     => ['required', 'min:8'],
            'confirmPassword' => ['required', 'same:newPassword'],
        ]);

        if (! Hash::check($this->currentPassword, auth()->user()->password)) {
            $this->addError('currentPassword', 'Current password is incorrect.');
            return;
        }

        auth()->user()->update(['password' => Hash::make($this->newPassword)]);
        $this->currentPassword = $this->newPassword = $this->confirmPassword = '';
        session()->flash('success', 'Password changed successfully.');
    }

    public function openCreate(string $role = 'staff'): void
    {
        $actor = auth()->user();

        if ($role === 'master' && $actor->role !== 'admin') {
            abort(403);
        }
        if ($role === 'staff' && ! in_array($actor->role, ['admin', 'master'])) {
            abort(403);
        }

        $this->newRole       = $role;
        $this->newLocale     = 'en';
        $this->newCurrencyId = Currency::active()->value('id');
        $this->newName       = $this->newUsername = $this->newStaffPass = $this->newStaffConfirm = '';
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function createUser(): void
    {
        $rules = [
            'newUsername'     => ['required', 'string', 'max:50', 'unique:users,username'],
            'newStaffPass'    => ['required', 'min:6'],
            'newStaffConfirm' => ['required', 'same:newStaffPass'],
        ];
        $attrs = ['newUsername' => 'Username', 'newStaffPass' => 'Password', 'newStaffConfirm' => 'Confirm Password'];

        if ($this->newRole === 'staff') {
            $rules['newName'] = ['required', 'string', 'max:255'];
            $attrs['newName'] = 'Full Name';
        }

        $this->validate($rules, [], $attrs);

        User::create([
            'name'       => $this->newRole === 'staff' ? $this->newName : $this->newUsername,
            'username'   => $this->newUsername,
            'password'   => Hash::make($this->newStaffPass),
            'role'       => $this->newRole,
            'created_by' => $this->newRole === 'master' ? null : auth()->id(),
            'locale'      => $this->newLocale,
            'currency_id' => $this->newCurrencyId,
            'is_active'   => true,
        ]);

        $this->showCreateModal = false;
        unset($this->staff, $this->masters);
        session()->flash('success', ucfirst($this->newRole) . ' account created.');
    }

    public function openEdit(int $userId): void
    {
        $user  = User::findOrFail($userId);
        $actor = auth()->user();

        if ($actor->role === 'master' && $user->created_by !== $actor->id) {
            abort(403);
        }

        $this->editUserId   = $userId;
        $this->editUserRole = $user->role;
        $this->editName     = $user->name;
        $this->editIsActive = (bool) $user->is_active;
        $this->editPassword = $this->editConfirm = '';
        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function updateUser(): void
    {
        $this->validate([
            'editName'     => ['required', 'string', 'max:255'],
            'editPassword' => ['nullable', 'min:6'],
            'editConfirm'  => ['nullable', 'same:editPassword'],
        ], [], [
            'editName'     => 'Full Name',
            'editPassword' => 'Password',
            'editConfirm'  => 'Confirm Password',
        ]);

        $user = User::findOrFail($this->editUserId);
        $data = ['name' => $this->editName, 'is_active' => $this->editIsActive];

        if ($this->editPassword) {
            $data['password'] = Hash::make($this->editPassword);
        }

        $user->update($data);
        $this->showEditModal = false;
        unset($this->staff, $this->masters);
        session()->flash('success', ucfirst($this->editUserRole) . ' updated.');
    }

    public function deleteUser(int $userId): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $user = User::findOrFail($userId);
        if ($user->role === 'admin') {
            abort(403, 'Cannot delete admin.');
        }

        $user->delete();
        unset($this->staff, $this->masters);
        session()->flash('success', 'Account deleted.');
    }

    public function render()
    {
    return view('livewire.account-page', [
            'todaySales' => $this->todaySales,
            'breakdown'  => $this->breakdown,
            'masters'    => $this->masters,
            'staff'      => $this->staff,
            'currencies' => Currency::active()->orderBy('country_name')->get(['id', 'name', 'country_name', 'symbol']),
        ]);
    }
}
