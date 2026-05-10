<?php

namespace App\Livewire;

use App\Models\BetCategory;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class BetCategoryManager extends Component
{
    public Collection $categories;

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $editingId = null;

    public string $name = '';

    public int $type = BetCategory::TYPE_P;

    public bool $status = true;

    public function mount(): void
    {
        $this->categories = BetCategory::orderBy('sort_order')->get();
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'editingId']);
        $this->type = BetCategory::TYPE_P;
        $this->status = true;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $c = BetCategory::findOrFail($id);

        $this->name = $c->name;
        $this->type = $c->type;
        $this->status = $c->status;
        $this->isEditing = true;
        $this->editingId = $id;
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->validate([
            'name' => ['required', 'string', 'max:50'],
            'type' => ['required', 'integer', 'in:1,2'],
            'status' => ['boolean'],
        ]);

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
        ];

        if ($this->isEditing) {
            BetCategory::findOrFail($this->editingId)->update($data);
        } else {
            $data['sort_order'] = (BetCategory::max('sort_order') ?? 0) + 1;
            BetCategory::create($data);
        }

        $this->refresh();
    }

    public function toggleStatus(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $c = BetCategory::findOrFail($id);
        $c->update(['status' => ! $c->status]);
        $this->refresh();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        BetCategory::findOrFail($id)->delete();
        $this->refresh();
    }

    private function refresh(): void
    {
        $this->categories = BetCategory::orderBy('sort_order')->get();
        $this->showModal = false;
    }

    public function render(): View
    {
        return view('livewire.bet-category-manager');
    }
}
