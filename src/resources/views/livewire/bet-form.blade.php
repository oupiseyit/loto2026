@php
    $SESSIONS  = [['value'=>'morning','label'=>__('morning')],['value'=>'noon','label'=>__('noon')],['value'=>'evening','label'=>__('evening')]];
    $POSITIONS = ['X','W','H','W*'];
    $NUMPAD    = ['1','2','3','4','5','6','7','8','9','0','00','X'];
    $totalAmount = collect($bets)->sum('amount');
@endphp

<div x-data="{
        number: '',
        amount: '',
        active: 'number',
        showBetList: true,
        numpad(key) {
            if (key === 'X') { if (this.active==='number') this.number=''; else this.amount=''; return; }
            if (this.active === 'number') this.number = (this.number + key).slice(0,10);
            else this.amount = (this.amount + key).slice(0,10);
        },
        addBet() {
            if (!this.number || !this.amount || parseFloat(this.amount) <= 0) return;
            $wire.addBet(this.number, this.amount);
            this.number = '';
            this.amount = '';
            this.active = 'number';
            this.$nextTick(() => this.$refs.numberInput.focus());
        }
     }">

    {{-- Flash success --}}
    @if ($flashSuccess)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(()=>show=false,4000)"
             class="mx-3 mt-2 px-4 py-2 rounded text-sm text-white bg-green-500">
            {{ $flashSuccess }}
        </div>
    @endif

    {{-- Top bar: date + session --}}
    <div class="flex items-center gap-2 px-3 py-2 bg-white border-b" style="border-color:#D4A017;">
        <span class="text-xs font-semibold" style="color:#DC143C;">{{ $today }}</span>
        <div class="flex gap-1 ml-auto">
            @foreach ($SESSIONS as $s)
                <button wire:click="$set('session','{{ $s['value'] }}')" type="button"
                        class="px-2 py-1 text-xs font-bold rounded border transition-all"
                        style="{{ $session === $s['value'] ? 'background-color:#DC143C;color:#fff;border-color:#DC143C;' : 'background-color:#fff;color:#DC143C;border-color:#DC143C;' }}">
                    {{ $s['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- ═══ Desktop layout ═══════════════════════════════════════ --}}
    <div class="hidden md:flex flex-1 overflow-hidden" style="height: calc(100vh - 7rem);">

        {{-- Left: bet list --}}
        <div class="flex flex-col w-1/2 border-r" style="border-color:#D4A017;">
            <div class="grid grid-cols-5 text-center text-xs font-bold text-white py-1.5" style="background-color:#DC143C;">
                <span>{{ __('letter') }}</span><span>{{ __('number') }}</span><span>{{ __('bet_type') }}</span><span>{{ __('total') }}</span><span>{{ __('position') }}</span>
            </div>
            <div class="flex-1 overflow-y-auto">
                @if (empty($bets))
                    <div class="flex items-center justify-center h-full opacity-10">
                        <div class="text-center">
                            <div class="text-6xl font-black" style="color:#D4A017;">HT</div>
                            <div class="text-sm" style="color:#DC143C;">ភ្នាក់</div>
                        </div>
                    </div>
                @else
                    @foreach ($bets as $idx => $bet)
                        <div wire:click="removeBet('{{ $bet['id'] }}')"
                             class="grid grid-cols-5 text-center text-base py-1.5 border-b cursor-pointer hover:opacity-70"
                             style="background-color:{{ $idx % 2 === 0 ? '#FFF8DC' : '#fff' }};border-color:#e5e7eb;">
                            <span class="font-medium">{{ $bet['letter'] }}</span>
                            <span>{{ $bet['number'] }}</span>
                            <span class="uppercase" style="color:#DC143C;">{{ $bet['bet_type'] }}</span>
                            <span>{{ number_format($bet['amount']) }}</span>
                            <span style="color:#D4A017;">{{ $bet['position'] }}</span>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="border-t p-2 bg-white" style="border-color:#D4A017;">
                <div class="flex items-center gap-2 mb-2 text-xs">
                    <span style="color:#DC143C;">{{ __('total') }}:</span>
                    <span class="font-bold" style="color:#DC143C;">{{ number_format($totalAmount) }}</span>
                    <span class="ml-auto px-2 py-0.5 rounded text-white text-xs" style="background-color:#D4A017;">{{ count($bets) }}R</span>
                </div>
                <button wire:click="submitBets" type="button"
                        {{ empty($bets) ? 'disabled' : '' }}
                        class="w-full py-2 rounded-lg text-white font-bold text-sm disabled:opacity-50 border-2 py-3 text-3xl"
                        style="background-color:#DC143C;">
                    <span wire:loading.remove wire:target="submitBets">{{ __('submit') }}</span>
                    <span wire:loading wire:target="submitBets">{{ __('submitting') }}</span>
                </button>
            </div>
        </div>

        {{-- Right: input controls --}}
        <div class="flex flex-col w-1/2 p-3 gap-2 bg-white overflow-hidden">
            @include('livewire.partials.bet-controls', ['compact' => false, 'letters' => $letters])
        </div>
    </div>

    {{-- ═══ Mobile layout ═══════════════════════════════════════ --}}
    <div class="flex md:hidden flex-col overflow-auto" style="padding-bottom:320px;">
        <div class="border-t" style="border-color:#D4A017;">
            {{-- Toggle bet list --}}
            <button type="button" @click="showBetList=!showBetList"
                    class="w-full flex items-center justify-between px-3 py-2 bg-white" style="color:#DC143C;">
                <div class="flex items-center gap-2 text-xs font-bold">
                    <span>{{ __('total') }}: {{ number_format($totalAmount) }}</span>
                    <span class="px-2 py-0.5 rounded text-white text-xs" style="background-color:#D4A017;">{{ count($bets) }}R</span>
                </div>
                <svg class="w-4 h-4 transition-transform" :class="showBetList ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="showBetList" class="max-h-32 overflow-y-auto p-2 bg-white border-b">
                <div class="grid grid-cols-5 text-center text-[10px] font-bold text-white py-1" style="background-color:#DC143C;">
                    <span>{{ __('letter') }}</span><span>{{ __('number') }}</span><span>{{ __('bet_type') }}</span><span>{{ __('total') }}</span><span>{{ __('position') }}</span>
                </div>
                @if (empty($bets))
                    <p class="text-center text-xs text-gray-400 py-2">No bets yet</p>
                @else
                    @foreach ($bets as $idx => $bet)
                        <div wire:click="removeBet('{{ $bet['id'] }}')"
                             class="grid grid-cols-5 text-center text-sm py-1.5 border-b cursor-pointer hover:opacity-70"
                             style="background-color:{{ $idx % 2 === 0 ? '#FFF8DC' : '#fff' }};border-color:#e5e7eb;">
                            <span class="font-medium">{{ $bet['letter'] }}</span>
                            <span>{{ $bet['number'] }}</span>
                            <span class="uppercase" style="color:#DC143C;">{{ $bet['bet_type'] }}</span>
                            <span>{{ number_format($bet['amount']) }}</span>
                            <span style="color:#D4A017;">{{ $bet['position'] }}</span>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Submit — sits just above the fixed panel border --}}
            <div class="px-2 py-1.5 bg-white">
                <button wire:click="submitBets" type="button"
                        {{ empty($bets) ? 'disabled' : '' }}
                        class="w-full py-1.5 rounded-lg text-white font-bold text-sm disabled:opacity-40"
                        style="background-color:#DC143C;">
                    <span wire:loading.remove wire:target="submitBets">{{ __('submit') }}</span>
                    <span wire:loading wire:target="submitBets">{{ __('submitting') }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile: fixed bottom panel (controls + numpad) --}}
    <div class="md:hidden fixed bottom-[57px] left-0 right-0 bg-white border-t z-20 flex flex-col"
         style="border-color:#D4A017;">

        {{-- Controls --}}
        <div class="px-2 pt-1 pb-1 flex flex-col gap-1">
            @include('livewire.partials.bet-controls', ['compact' => true, 'hideNumpad' => true, 'letters' => $letters])
        </div>

        {{-- Numpad --}}
        <div class="px-2 pb-2 grid grid-cols-3 gap-1">
            @foreach ($NUMPAD as $key)
                @if ($key === 'X')
                    <button type="button" @click="numpad('X')"
                            class="h-8 rounded font-bold text-xs flex items-center justify-center text-white"
                            style="background-color:#C0392B;">✕</button>
                @else
                    <button type="button" @click="numpad('{{ $key }}')"
                            class="h-8 rounded font-bold text-xs flex items-center justify-center text-white"
                            style="background-color:#D4A017;">{{ $key }}</button>
                @endif
            @endforeach
        </div>
    </div>
</div>
