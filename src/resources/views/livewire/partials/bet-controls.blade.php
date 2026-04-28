@php $compact = $compact ?? false; $hideNumpad = $hideNumpad ?? false; @endphp

{{-- Bet type: ABCD / LO --}}
<div class="flex gap-1">
    @foreach ([['value'=>'ABCD','label'=>'ABCD'],['value'=>'LO','label'=>'LO']] as $opt)
        <button wire:click="$set('betType','{{ $opt['value'] }}')" type="button"
                class="flex-1 font-bold rounded border transition-all {{ $compact ? 'py-1 text-xs' : 'py-3 text-lg' }}"
                style="{{ $betType === $opt['value'] ? 'background-color:#DC143C;color:#fff;border-color:#DC143C;' : 'background-color:#fff;color:#DC143C;border-color:#DC143C;' }}">
            {{ $opt['label'] }}
        </button>
    @endforeach
</div>

{{-- Letter buttons --}}
<div class="flex flex-wrap gap-1">
    @foreach ($letters as $l)
        <button wire:click="$set('letter','{{ $l }}')" type="button"
                class="flex-1 font-bold rounded border transition-all {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
                style="{{ $letter === $l ? 'background-color:#D4A017;color:#fff;border-color:#D4A017;' : 'background-color:#fff;color:#D4A017;border-color:#D4A017;' }}">
            {{ $l }}
        </button>
    @endforeach
</div>

{{-- Position buttons --}}
<div class="flex gap-1">
    @foreach (['X','W','H','W*'] as $pos)
        <button wire:click="$set('position','{{ $pos }}')" type="button"
                class="flex-1 font-bold rounded border {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
                style="{{ $position === $pos ? 'background-color:#D4A017;color:#fff;border-color:#D4A017;' : 'background-color:#fff;color:#D4A017;border-color:#D4A017;' }}">
            {{ $pos }}
        </button>
    @endforeach
</div>

{{-- Number input --}}
<input type="text" inputmode="numeric"
       x-ref="numberInput"
       x-model="number"
       @focus="active='number'"
       @input="number = number.replace(/[^0-9]/g, '').slice(0,10)"
       placeholder="{{ __('number') }}"
       class="w-full text-center font-bold rounded border-2 outline-none {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
       :style="active==='number' ? 'border-color:#DC143C;color:#DC143C;' : 'border-color:#D4A017;color:#DC143C;'" />

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
    <button type="button" @click="number='';amount='';"
            class="w-full rounded-lg font-bold border-2 {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
            style="border-color:#D4A017;color:#D4A017;background-color:#fff;">{{ __('clear') }}</button>
    <button type="button" @click="addBet()"
            class="w-full rounded-lg font-bold text-white {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
            style="background-color:#D4A017;">{{ __('add') }}</button>
</div>
