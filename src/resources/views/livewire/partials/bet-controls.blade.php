@php $compact = $compact ?? false; $hideNumpad = $hideNumpad ?? false; @endphp

{{-- Group type buttons (from bet_time_settings.group_type) --}}
<div class="flex flex-wrap gap-1">
    @foreach ($letters as $l)
        <button wire:click="$set('letter','{{ $l }}')" type="button"
                class="flex-1 font-bold rounded border transition-all {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
                style="{{ $letter === $l ? 'background-color:#D4A017;color:#fff;border-color:#D4A017;' : 'background-color:#fff;color:#D4A017;border-color:#D4A017;' }}">
            {{ $l }}
        </button>
    @endforeach
</div>

{{-- Bet type select --}}
<select x-model.number="betMode"
        class="w-full font-bold rounded border-2 outline-none {{ $compact ? 'py-1 text-sm' : 'py-2 text-sm' }}"
        style="border-color:#DC143C;color:#DC143C;background-color:#fff;">
    <option value="1">Normal</option>
    <option value="2">Head</option>
    <option value="3">Tail</option>
    <option value="4">Tail 2</option>
</select>

{{-- Number inputs per bet type --}}
@include('livewire.partials.bet_type.bet_type_1', ['compact' => $compact])
@include('livewire.partials.bet_type.bet_type_2', ['compact' => $compact])
@include('livewire.partials.bet_type.bet_type_3', ['compact' => $compact])
@include('livewire.partials.bet_type.bet_type_4', ['compact' => $compact])

{{-- Amount input --}}
<input type="text" inputmode="numeric"
       x-model="amount"
       @focus="active='amount'"
       @input="amount = amount.replace(/[^0-9]/g, '').slice(0,10)"
       @keydown.enter="addBet()"
       placeholder="{{ __('amount') }}"
       class="w-full text-center font-bold rounded border-2 outline-none {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
       :style="active==='amount' ? 'border-color:#DC143C;color:#D4A017;' : 'border-color:#D4A017;color:#D4A017;'" />

{{-- Numpad (desktop only) --}}
@if (!$hideNumpad)
    <div class="flex-1 min-h-0 grid grid-cols-3 grid-rows-4 {{ $compact ? 'gap-1' : 'gap-1.5' }}">
        @foreach (['1','2','3','4','5','6','7','8','9','0','00','X'] as $key)
            @if ($key === 'X')
                <button type="button" @click="numpad('X')"
                        class="rounded font-bold text-5xl flex items-center justify-center text-white"
                        style="background-color:#C0392B;">✕</button>
            @else
                <button type="button" @click="numpad('{{ $key }}')"
                        class="rounded font-bold text-5xl flex items-center justify-center text-white"
                        style="background-color:#D4A017;">{{ $key }}</button>
            @endif
        @endforeach
    </div>
@endif

{{-- Clear + Add --}}
<div class="flex gap-2">
    <button type="button" @click="number='';startNumber='';endNumber='';amount='';"
            class="w-full rounded-lg font-bold border-2 {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
            style="border-color:#D4A017;color:#D4A017;background-color:#fff;">{{ __('clear') }}</button>
    <button type="button" @click="addBet()"
            class="w-full rounded-lg font-bold text-white {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
            style="background-color:#D4A017;">{{ __('add') }}</button>
</div>
