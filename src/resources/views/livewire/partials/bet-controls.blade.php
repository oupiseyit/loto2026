@php $compact = $compact ?? false; $hideNumpad = $hideNumpad ?? false; $hideClear = $hideClear ?? false; $hideAdd = $hideAdd ?? false; @endphp
<div style="background:#fff;border:1px solid #E2D9CC;border-radius:6px;padding:10px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
{{-- Position Group card --}}
        <div style="background:#fff;border:1px solid #E2D9CC;border-radius:6px;padding:10px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <div style="font-size:9px;font-weight:600;letter-spacing:0.14em;color:#B0A598;text-transform:uppercase;margin-bottom:6px;">Position Group</div>
            <div class="flex flex-wrap gap-1">
                @foreach ($letters as $l)
                    <button wire:click="$set('letter','{{ $l }}')" type="button"
                            class="flex-1 font-bold rounded border transition-all {{ $compact ? 'py-1 text-sm' : 'py-2' }}"
                            style="{{ $letter === $l
                                ? 'background-color:#2A5F47;color:#fff;border-color:#2A5F47;'
                                : 'background-color:#FAF7F2;color:#7A6E64;border-color:#E2D9CC;' }}"
                            onmouseover="{{ $letter === $l ? '' : "this.style.borderColor='#2A5F47';this.style.color='#2A5F47';this.style.backgroundColor='rgba(42,95,71,0.08)';" }}"
                            onmouseout="{{ $letter === $l ? '' : "this.style.borderColor='#E2D9CC';this.style.color='#7A6E64';this.style.backgroundColor='#FAF7F2';" }}">
                        {{ $l }}
                    </button>
                @endforeach
            </div>
        </div>

    {{-- Bet Type card --}}
    <div class="mt-2" style="background:#fff;border:1px solid #E2D9CC;border-radius:6px;padding:10px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
        <div style="font-size:9px;font-weight:600;letter-spacing:0.14em;color:#B0A598;text-transform:uppercase;margin-bottom:6px;">Bet Type</div>
        <div class="flex gap-1 ">
            @foreach ([1 => 'Normal', 2 => '<', 3 => '=', 4 => '>', 5 => 'X'] as $mode => $label)
                <button type="button" @click="betMode = {{ $mode }}"
                        class="flex-1 font-bold rounded border transition-all {{ $compact ? 'py-1 text-sm' : 'py-2 text-sm' }}"
                        :style="betMode === {{ $mode }}
                            ? 'background-color:#9B2335;color:#fff;border-color:#9B2335;box-shadow:0 1px 4px rgba(155,35,53,0.3);'
                            : 'background-color:#FAF7F2;color:#7A6E64;border-color:#E2D9CC;'">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Number input card --}}
    <div class="mt-2"  style="background:#fff;border:1px solid #E2D9CC;border-radius:6px;padding:10px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
        <div style="font-size:9px;font-weight:600;letter-spacing:0.14em;color:#B0A598;text-transform:uppercase;margin-bottom:6px;">Number</div>
        @include('livewire.partials.bet_type.bet_type_1', ['compact' => $compact])
        @include('livewire.partials.bet_type.bet_type_2', ['compact' => $compact])
        @include('livewire.partials.bet_type.bet_type_3', ['compact' => $compact])
        @include('livewire.partials.bet_type.bet_type_4', ['compact' => $compact])
        @include('livewire.partials.bet_type.bet_type_5', ['compact' => $compact])
    </div>

    {{-- Amount input card --}}
    <div class="mt-2"  style="background:#fff;border:1px solid #E2D9CC;border-radius:6px;padding:10px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
        <div style="font-size:9px;font-weight:600;letter-spacing:0.14em;color:#B0A598;text-transform:uppercase;margin-bottom:6px;">Amount</div>
        <input type="text" inputmode="numeric"
            :value="formatAmount(amount)"
            @focus="active='amount'"
            @input="amount = $event.target.value.replace(/[^0-9]/g, '').slice(0,10)"
            @blur="if (amount) { let n = Math.round(parseInt(amount) / 100) * 100; amount = (n >= 100 ? n : 100).toString(); }"
            @keydown.enter="addBet()"
            placeholder="{{ __('amount') }}"
            class="w-full text-center font-bold rounded border-2 outline-none {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
            :style="active==='amount' ? 'border-color:#D4A017;color:#D4A017;background:#fff;box-shadow:0 0 0 3px rgba(212,160,23,0.15);' : 'border-color:#E2D9CC;color:#D4A017;background:#FAF7F2;'" />
    </div>

    {{-- Bet breakdown preview --}}
    <div x-show="betBreakdown() !== null" x-cloak
        class="mt-2 rounded border font-bold {{ $compact ? 'text-sm' : 'text-base' }} flex flex-col overflow-hidden"
        style="background-color:#FAF7F2;border:1.5px solid #2A5F47;max-height:12rem;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
        <div class="overflow-y-auto flex-1 min-h-0">
            <template x-for="(row, idx) in (betBreakdown() || {rows:[]}).rows" :key="idx">
                <div class="flex items-center justify-between px-3 {{ $compact ? 'py-0.5' : 'py-1' }} border-b" style="border-color:#E2D9CC;color:#9B2335;font-size:11px;">
                    <span style="color:#7A6E64;" x-text="(idx+1) + ' - '"></span><span x-text="row.num" style="font-family:'Cormorant Garamond',serif;font-size:14px;font-weight:600;color:#2C2826;"></span>
                    <span x-text="row.amt.toLocaleString('en-US') + ' × ' + posMultiplier() + '(' + letter + ') = ' + row.rowTotal.toLocaleString('en-US')" style="color:#7A6E64;"></span>
                </div>
            </template>
        </div>
        <div class="flex items-center justify-between px-3 {{ $compact ? 'py-1' : 'py-1.5' }} font-bold {{ $compact ? 'text-sm' : 'text-base' }} flex-shrink-0" style="background-color:#2A5F47;color:#fff;">
            <span style="font-size:11px;font-weight:600;letter-spacing:0.08em;">Sub-total</span>
            <span style="font-family:'Cormorant Garamond',serif;font-size:16px;font-weight:600;" x-text="'= ' + ((betBreakdown() || {total:0}).total).toLocaleString()"></span>
        </div>
    </div>
</div>


{{-- Numpad (desktop only) --}}
@if (!$hideNumpad)
    <div style="background:#fff;border:1px solid #E2D9CC;border-radius:6px;padding:10px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
        <div class="flex-1 min-h-0 grid grid-cols-3 grid-rows-4 {{ $compact ? 'gap-1' : 'gap-1.5' }}" style="height:200px;">
            @foreach (['1','2','3','4','5','6','7','8','9','0','00','X'] as $key)
                @if ($key === 'X')
                    <button type="button" @click="numpad('X')"
                            class="rounded font-bold text-2xl flex items-center justify-center"
                            style="background-color:rgba(155,35,53,0.08);color:#9B2335;border:1px solid rgba(155,35,53,0.2);"
                            onmouseover="this.style.backgroundColor='#9B2335';this.style.color='#fff';this.style.borderColor='#9B2335';"
                            onmouseout="this.style.backgroundColor='rgba(155,35,53,0.08)';this.style.color='#9B2335';this.style.borderColor='rgba(155,35,53,0.2)';">✕</button>
                @else
                    <button type="button" @click="numpad('{{ $key }}')"
                            class="rounded font-bold flex items-center justify-center"
                            style="background-color:#FAF7F2;color:#2C2826;border:1px solid #E2D9CC;font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:600;"
                            onmouseover="this.style.backgroundColor='#2A5F47';this.style.color='#fff';this.style.borderColor='#2A5F47';"
                            onmouseout="this.style.backgroundColor='#FAF7F2';this.style.color='#2C2826';this.style.borderColor='#E2D9CC';">{{ $key }}</button>
                @endif
            @endforeach
        </div>
    </div>
@endif

{{-- Clear + Add --}}
@if (!$hideClear || !$hideAdd)
<div class="flex gap-2">
    @if (!$hideClear)
        <button type="button" @click="number='';startNumber='';endNumber='';amount='';"
                class="w-full rounded-lg font-bold border-2 {{ $compact ? 'py-1 text-sm' : 'py-3' }}"
                style="border-color:#2A5F47;color:#2A5F47;background-color:#FAF7F2;"
                onmouseover="this.style.backgroundColor='rgba(42,95,71,0.08)';"
                onmouseout="this.style.backgroundColor='#FAF7F2';">{{ __('clear') }}</button>
    @endif
    @if (!$hideAdd)
        <button type="button" @click="addBet()"
                class="w-full rounded-lg font-bold text-white {{ $compact ? 'py-1 text-sm' : 'py-3' }}"
                style="background-color:#D4A017;box-shadow:0 1px 4px rgba(0,0,0,0.1);"
                onmouseover="this.style.backgroundColor='#B8860B';this.style.transform='translateY(-1px)';"
                onmouseout="this.style.backgroundColor='#D4A017';this.style.transform='';">{{ __('add') }}</button>
    @endif
</div>
@endif
