<?php

namespace App\Livewire;

use App\Models\BetCategory;
use App\Models\BetTimeSetting;
use App\Models\Result;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class BetTimeSettings extends Component
{
    public Collection $sessions;

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $editingId = null;

    public string $session_key = '';

    public string $session_name = '';

    public string $result_time = '';

    public array $group_type = [];

    public array $group1_types = [];

    public string $group1_cutoff = '';

    public array $group2_types = [];

    public string $group2_cutoff = '';

    public bool $is_active = true;

    public function mount(): void
    {
        $this->sessions = BetTimeSetting::orderBy('sort_order')->get();
    }

    public function openCreate(): void
    {
        $this->reset(['session_key', 'session_name', 'result_time', 'group_type',
            'group1_types', 'group1_cutoff', 'group2_types', 'group2_cutoff']);
        $this->is_active = true;
        $this->isEditing = false;
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $s = BetTimeSetting::findOrFail($id);

        $this->session_key = $s->session_key;
        $this->session_name = $s->session_name;
        $this->result_time = substr($s->result_time, 0, 5);
        $this->group_type = $s->group_type ?? [];
        $this->group1_types = $s->group1_types;
        $this->group1_cutoff = substr($s->group1_cutoff, 0, 5);
        $this->group2_types = $s->group2_types;
        $this->group2_cutoff = substr($s->group2_cutoff, 0, 5);
        $this->is_active = $s->is_active;
        $this->isEditing = true;
        $this->editingId = $id;
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $rules = [
            'session_name' => ['required', 'string', 'max:100'],
            'result_time' => ['required', 'date_format:H:i'],
            'group1_types' => ['required', 'array', 'min:1'],
            'group1_cutoff' => ['required', 'date_format:H:i'],
            'group2_types' => ['required', 'array', 'min:1'],
            'group2_cutoff' => ['required', 'date_format:H:i'],
        ];

        if (! $this->isEditing) {
            $rules['session_key'] = ['required', 'string', 'max:50', 'alpha_dash', 'unique:bet_time_settings,session_key'];
        }

        $this->validate($rules);

        $data = [
            'session_name' => $this->session_name,
            'result_time' => $this->result_time.':00',
            'group_type' => $this->group_type,
            'group1_types' => $this->group1_types,
            'group1_cutoff' => $this->group1_cutoff.':00',
            'group2_types' => $this->group2_types,
            'group2_cutoff' => $this->group2_cutoff.':00',
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            BetTimeSetting::findOrFail($this->editingId)->update($data);
        } else {
            $data['session_key'] = $this->session_key;
            $data['sort_order'] = BetTimeSetting::max('sort_order') + 1;
            BetTimeSetting::create($data);
        }

        BetTimeSetting::bustCache();
        $this->sessions = BetTimeSetting::orderBy('sort_order')->get();
        $this->showModal = false;
    }

    public function toggleActive(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $s = BetTimeSetting::findOrFail($id);
        $s->update(['is_active' => ! $s->is_active]);
        BetTimeSetting::bustCache();
        $this->sessions = BetTimeSetting::orderBy('sort_order')->get();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $s = BetTimeSetting::findOrFail($id);

        $ticketCount = Ticket::where('session', $s->session_key)->count();
        $resultCount = Result::where('session', $s->session_key)->count();

        if ($ticketCount + $resultCount > 0) {
            session()->flash('error', "Session '{$s->session_key}' has {$ticketCount} ticket(s) and {$resultCount} result(s). Deactivate instead.");

            return;
        }

        $s->delete();
        BetTimeSetting::bustCache();
        $this->sessions = BetTimeSetting::orderBy('sort_order')->get();
    }

    public function render(): View
    {
        return view('livewire.bet-time-settings', [
            'betCategories' => BetCategory::active()->orderBy('sort_order')->get(),
        ]);
    }
}
