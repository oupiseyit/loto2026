<?php

namespace App\Livewire;

use App\Models\BetTimeSetting;
use App\Models\Result;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ResultPage extends Component
{
    public string $selectedDate;
    public string $selectedSession;
    public array  $results = [];
    public bool   $canEdit = false;

    public function mount(string $selectedDate, string $selectedSession, array $grid, bool $canEdit = false): void
    {
        $this->selectedDate    = $selectedDate;
        $this->selectedSession = $selectedSession;
        $this->results         = $grid;
        $this->canEdit         = $canEdit;
    }

    #[Computed]
    public function availableSessions(): \Illuminate\Support\Collection
    {
        return BetTimeSetting::active();
    }

    public function updatedSelectedSession(): void
    {
        $this->loadGrid();
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->loadGrid();
    }

    public function selectSession(string $session): void
    {
        $this->selectedSession = $session;
        $this->loadGrid();
    }

    private function loadGrid(): void
    {
        $positions = $this->positionsForSession($this->selectedSession);

        $existing = Result::where('result_date', $this->selectedDate)
            ->where('session', $this->selectedSession)
            ->orderBy('position')
            ->get()
            ->keyBy('position');

        $this->results = collect($positions)->map(function (string $pos) use ($existing): array {
            $row = $existing->get($pos);
            return [
                'position' => $pos,
                'number'   => $row ? ($row->number ?? '') : '',
            ];
        })->toArray();
    }

    public function save(): void
    {
        abort_unless($this->canEdit, 403);

        $this->validate([
            'results.*.number' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function (): void {
            foreach ($this->results as $row) {
                Result::updateOrCreate(
                    [
                        'result_date' => $this->selectedDate,
                        'session'     => $this->selectedSession,
                        'position'    => $row['position'],
                    ],
                    [
                        'number'     => $row['number'] ?: null,
                        'entered_by' => auth()->id(),
                    ]
                );
            }
        });

        session()->flash('success', 'Results saved successfully.');
    }

    #[Computed]
    public function dates(): \Illuminate\Support\Collection
    {
        return DB::table('results')
            ->selectRaw('DATE(result_date) as date')
            ->union(DB::table('tickets')->selectRaw('DATE(bet_date) as date'))
            ->orderByDesc('date')
            ->limit(60)
            ->pluck('date')
            ->unique()
            ->values();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.result-page');
    }

    private function positionsForSession(string $sessionKey): array
    {
        $setting = BetTimeSetting::active()->firstWhere('session_key', $sessionKey);
        if (!$setting) {
            return ['A', 'B', 'C', 'D'];
        }
        return $setting->letters();
    }
}
