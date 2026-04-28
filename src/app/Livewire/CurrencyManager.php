<?php

namespace App\Livewire;

use App\Models\Currency;
use Livewire\Component;

class CurrencyManager extends Component
{
    // Create form
    public bool   $showCreate      = false;
    public string $newName         = '';
    public string $newCountryName  = '';
    public string $newSymbol       = '';

    // Edit form
    public bool   $showEdit         = false;
    public ?int   $editId           = null;
    public string $editName         = '';
    public string $editCountryName  = '';
    public string $editSymbol       = '';

    public function openCreate(): void
    {
        $this->newName        = '';
        $this->newCountryName = '';
        $this->newSymbol      = '';
        $this->resetErrorBag();
        $this->showCreate = true;
    }

    public function store(): void
    {
        $this->validate([
            'newCountryName' => ['required', 'string', 'max:100'],
            'newName'        => ['required', 'string', 'max:50'],
            'newSymbol'      => ['required', 'string', 'max:10'],
        ], [], [
            'newCountryName' => 'Country Name',
            'newName'        => 'Currency Name',
            'newSymbol'      => 'Symbol',
        ]);

        Currency::create([
            'country_name' => $this->newCountryName,
            'name'         => $this->newName,
            'symbol'       => $this->newSymbol,
            'is_active'    => true,
        ]);

        $this->showCreate = false;
        session()->flash('success', 'Currency created.');
    }

    public function openEdit(int $id): void
    {
        $currency = Currency::findOrFail($id);

        $this->editId          = $id;
        $this->editName        = $currency->name;
        $this->editCountryName = $currency->country_name ?? '';
        $this->editSymbol      = $currency->symbol;
        $this->resetErrorBag();
        $this->showEdit = true;
    }

    public function update(): void
    {
        $this->validate([
            'editCountryName' => ['required', 'string', 'max:100'],
            'editName'        => ['required', 'string', 'max:50'],
            'editSymbol'      => ['required', 'string', 'max:10'],
        ], [], [
            'editCountryName' => 'Country Name',
            'editName'        => 'Currency Name',
            'editSymbol'      => 'Symbol',
        ]);

        Currency::findOrFail($this->editId)->update([
            'country_name' => $this->editCountryName,
            'name'         => $this->editName,
            'symbol'       => $this->editSymbol,
        ]);

        $this->showEdit = false;
        session()->flash('success', 'Currency updated.');
    }

    public function toggleActive(int $id): void
    {
        $currency = Currency::findOrFail($id);
        $currency->update(['is_active' => ! $currency->is_active]);
    }

    public function delete(int $id): void
    {
        Currency::findOrFail($id)->delete();
        session()->flash('success', 'Currency deleted.');
    }

    public function render()
    {
        return view('livewire.currency-manager', [
            'currencies' => Currency::orderBy('country_name')->get(),
        ]);
    }
}
