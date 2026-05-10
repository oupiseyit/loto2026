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

{{-- Bet type buttons (Normal / Head / Tail / Tail 2) --}}
<div class="flex gap-1">
    @foreach ([1 => 'Normal', 2 => 'Head', 5 => 'Head 2', 3 => 'Tail', 4 => 'Tail 2'] as $mode => $label)
        <button wire:click="$set('betMode', {{ $mode }})" type="button"
                class="flex-1 font-bold rounded border transition-all {{ $compact ? 'py-1 text-xs' : 'py-2 text-sm' }}"
                style="{{ $betMode === $mode ? 'background-color:#DC143C;color:#fff;border-color:#DC143C;' : 'background-color:#fff;color:#DC143C;border-color:#DC143C;' }}">
            {{ $label }}
        </button>
    @endforeach
</div>

{{-- Number input — Normal bet (betMode = 1) --}}
<div x-show="betMode === 1">
    <input type="text" inputmode="numeric"
           x-ref="numberInput"
           x-model="number"
           @focus="active='number'"
           @input="number = number.replace(/[^0-9]/g, '').slice(0,3)"
           placeholder="{{ __('number') }}"
           class="w-full text-center font-bold rounded border-2 outline-none {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
           :style="active==='number' ? 'border-color:#DC143C;color:#DC143C;' : 'border-color:#D4A017;color:#DC143C;'" />
</div>

{{-- Start + End number inputs — Head / Tail / Tail 2 (betMode > 1) --}}
<div x-show="betMode > 1" class="flex gap-1">
    <input type="text" inputmode="numeric"
           x-ref="startNumberInput"
           x-model="startNumber"
           @focus="active='startNumber'"
           @input="
               startNumber = startNumber.replace(/[^0-9]/g, '');
               if (betMode === 3 && startNumber.length > 2) { betMode = 4; startNumber = startNumber.slice(0,3); }
               else if (betMode === 2 || betMode === 3) { startNumber = startNumber.slice(0,2); }
               else { startNumber = startNumber.slice(0,3); }
           "
           :maxlength="(betMode === 4 || betMode === 5) ? 3 : 2"
           placeholder="{{ __('start_number') }}"
           class="w-1/2 text-center font-bold rounded border-2 outline-none {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
           :style="active==='startNumber' ? 'border-color:#DC143C;color:#DC143C;' : 'border-color:#D4A017;color:#DC143C;'" />

    {{-- Head / Head 2: editable end number (units digit locked to start's units) --}}
    <input type="text" inputmode="numeric"
           x-show="betMode === 2 || betMode === 5"
           x-model="endNumber"
           @focus="active='endNumber'"
           @input="
               endNumber = endNumber.replace(/[^0-9]/g, '');
               let padLen = betMode === 5 ? 3 : 2;
               endNumber = endNumber.slice(0, padLen);
               if (startNumber !== '' && endNumber.length === padLen) {
                   let units = parseInt(startNumber) % 10;
                   let corrected = Math.floor(parseInt(endNumber) / 10) * 10 + units;
                   endNumber = corrected.toString().padStart(padLen, '0');
               }
           "
           :maxlength="betMode === 5 ? 3 : 2"
           placeholder="{{ __('end_number') }}"
           class="w-1/2 text-center font-bold rounded border-2 outline-none {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
           :style="active==='endNumber' ? 'border-color:#DC143C;color:#DC143C;' : 'border-color:#D4A017;color:#DC143C;'" />

    {{-- Tail / Tail 2: readonly auto-calculated end number --}}
    <input type="text" readonly
           x-show="betMode === 3 || betMode === 4"
           :value="startNumber === '' ? '' : betMode === 3
               ? (Math.floor(parseInt(startNumber)/10)*10+9).toString().padStart(2,'0')
               : (Math.floor(parseInt(startNumber)/100)*100+99).toString().padStart(3,'0')"
           placeholder="{{ __('end_number') }}"
           class="w-1/2 text-center font-bold rounded border-2 outline-none bg-gray-50 {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
           style="border-color:#D4A017;color:#9ca3af;cursor:default;" />
</div>

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
