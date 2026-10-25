@php
    $compact = $compact ?? false; $hideNumpad = $hideNumpad ?? false; $hideClear = $hideClear ?? false; $hideAdd = $hideAdd ?? false;
    $card = 'background:#fff;border:1px solid #E2D9CC;border-radius:6px;padding:'.($compact ? '6px' : '10px').';box-shadow:0 1px 4px rgba(0,0,0,0.06);';
    $caption = 'font-size:9px;font-weight:600;letter-spacing:0.14em;color:#B0A598;text-transform:uppercase;margin-bottom:4px;';
    $input = 'w-full text-center font-bold rounded border-2 outline-none '.($compact ? 'py-1 text-sm' : 'py-2 text-2xl');
    // Mode ids = App\Support\BetNumbers constants
    $MODES = [1 => 'Normal', 2 => 'Xគុណ', 3 => '<=>', 4 => 'Kគូ', 5 => 'Wអូស'];
@endphp

{{-- Posts (ប៉ុស្តិ៍) — multi-select --}}
<div style="{{ $card }}">
    <div style="{{ $caption }}">{{ __('post') }}</div>
    <div class="flex flex-wrap gap-1">
        @foreach ($letters as $l)
            @php $on = in_array($l, $posts, true); @endphp
            <button wire:click="togglePost('{{ $l }}')" type="button"
                    class="min-w-[2.5rem] flex-1 font-bold rounded border {{ $compact ? 'py-1 text-xs' : 'py-2 text-sm' }}"
                    style="{{ $on ? 'background-color:#2A5F47;color:#fff;border-color:#2A5F47;' : 'background-color:#FAF7F2;color:#7A6E64;border-color:#E2D9CC;' }}">
                {{ $l }}
            </button>
        @endforeach
    </div>
</div>

{{-- Bet mode --}}
<div style="{{ $card }}">
    <div class="flex gap-1">
        @foreach ($MODES as $mode => $label)
            <button type="button" @click="betMode = {{ $mode }}"
                    class="flex-1 font-bold rounded border {{ $compact ? 'py-1 text-xs' : 'py-2 text-sm' }}"
                    :style="betMode === {{ $mode }}
                        ? 'background-color:#9B2335;color:#fff;border-color:#9B2335;'
                        : 'background-color:#FAF7F2;color:#7A6E64;border-color:#E2D9CC;'">{{ $label }}</button>
        @endforeach
    </div>
</div>

{{-- លេខ / ដល់លេខ / ទឹកប្រាក់ --}}
<div style="{{ $card }}" class="flex gap-1">
    <div class="flex-1">
        <div style="{{ $caption }}">{{ __('number') }}</div>
        <input type="text" inputmode="numeric" x-model="number" @focus="active='number'"
               @input="number = number.replace(/[^0-9]/g, '').slice(0,3)"
               :placeholder="betMode === 4 ? '00 / 000' : (betMode === 5 ? '0-9' : '')"
               class="{{ $input }}"
               :style="active==='number' ? 'border-color:#9B2335;color:#9B2335;' : 'border-color:#C8BBA8;color:#9B2335;'" />
    </div>
    <div class="flex-1" x-show="betMode === 3">
        <div style="{{ $caption }}">{{ __('to_number') }}</div>
        <input type="text" inputmode="numeric" x-model="endNumber" @focus="active='endNumber'"
               @input="endNumber = endNumber.replace(/[^0-9]/g, '').slice(0,3)"
               class="{{ $input }}"
               :style="active==='endNumber' ? 'border-color:#9B2335;color:#9B2335;' : 'border-color:#C8BBA8;color:#9B2335;'" />
    </div>
    <div class="flex-1">
        <div style="{{ $caption }}">{{ __('amount') }}</div>
        <input type="text" inputmode="numeric" :value="formatAmount(amount)" @focus="active='amount'"
               @input="amount = $event.target.value.replace(/[^0-9]/g, '').slice(0,10)"
               @blur="if (amount) { let n = Math.round(parseInt(amount) / 100) * 100; amount = (n >= 100 ? n : 100).toString(); }"
               @keydown.enter="addBet()"
               class="{{ $input }}"
               :style="active==='amount' ? 'border-color:#D4A017;color:#D4A017;' : 'border-color:#E2D9CC;color:#D4A017;'" />
    </div>
</div>

{{-- Breakdown preview: numbers × posts = total --}}
<div x-show="betBreakdown() !== null" x-cloak class="rounded text-xs px-3 py-1.5 flex flex-wrap items-center gap-x-2"
     style="background-color:#FAF7F2;border:1.5px solid #2A5F47;color:#7A6E64;">
    <span class="truncate flex-1" style="color:#2C2826;" x-text="(betBreakdown()?.nums || []).join(' ')"></span>
    <span class="font-bold" style="color:#2A5F47;"
          x-text="betBreakdown() && (parseInt(amount).toLocaleString('en-US') + ' × ' + betBreakdown().count + ' × ' + betBreakdown().posts + ' = ' + betBreakdown().total.toLocaleString('en-US') + ' KHR')"></span>
</div>

{{-- Keypad --}}
@if (!$hideNumpad)
    <div style="{{ $card }}" class="grid grid-cols-3 gap-1">
        @foreach (['1','2','3','4','5','6','7','8','9','0','00','X'] as $key)
            <button type="button" @click="numpad('{{ $key }}')"
                    class="rounded font-bold flex items-center justify-center {{ $compact ? 'h-8 text-sm' : 'h-11 text-xl' }}"
                    style="{{ $key === 'X' ? 'background-color:#9B2335;color:#fff;border:1px solid #9B2335;' : 'background-color:#FAF7F2;color:#2C2826;border:1px solid #E2D9CC;' }}">{{ $key === 'X' ? '✕' : $key }}</button>
        @endforeach
    </div>
@endif

{{-- Clear + Add --}}
@if (!$hideClear || !$hideAdd)
<div class="flex gap-2">
    @if (!$hideClear)
        <button type="button" @click="number='';endNumber='';amount='';"
                class="w-full rounded-lg font-bold text-white {{ $compact ? 'py-1 text-sm' : 'py-3' }}"
                style="background-color:#9B2335;">{{ __('clear') }}</button>
    @endif
    @if (!$hideAdd)
        <button type="button" @click="addBet()"
                class="w-full rounded-lg font-bold text-white {{ $compact ? 'py-1 text-sm' : 'py-3' }}"
                style="background-color:#D4A017;">{{ __('add') }}</button>
    @endif
</div>
@endif
