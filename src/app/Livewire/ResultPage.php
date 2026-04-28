<?php

namespace App\Livewire;

use App\Models\Result;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ResultPage extends Component
{
    public string $selectedDate;
    public string $selectedSession;
    public array  $results = [];
    public bool $canEdit = false;

    private const POSITIONS = [
        'morning' => ['A', 'B', 'C', 'D'],
        'noon'    => ['A', 'B', 'C', 'D', 'F', 'I', 'N'],
        'evening' => ['A', 'B', 'C', 'D'],
    ];

    public function mount(string $selectedDate, string $selectedSession, array $grid, bool $canEdit = false): void
    {
        $this->selectedDate    = $selectedDate;
        $this->selectedSession = $selectedSession;
        $this->results = $grid;
        $this->canEdit = $canEdit;
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
        $positions = self::POSITIONS[$this->selectedSession] ?? self::POSITIONS['morning'];

        $existing = Result::where('result_date', $this->selectedDate)
            ->where('session', $this->selectedSession)
            ->orderBy('position')
            ->get()
            ->keyBy('position');

        $this->results = collect($positions)->map(function ($pos) use ($existing) {
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

        DB::transaction(function () {
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

    public function render()
    {
        return view('livewire.result-page');
    }
}
