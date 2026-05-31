@php
    $NUMPAD      = ['1','2','3','4','5','6','7','8','9','0','00','X'];
    $totalAmount = collect($bets)->sum('amount');

    $statusStyle = [
        'open'         => 'color:#16a34a;border-color:#16a34a;',
        'closing_soon' => 'color:#d97706;border-color:#d97706;',
        'partial'      => 'color:#ea580c;border-color:#ea580c;',
        'closed'       => 'color:#9ca3af;border-color:#d1d5db;',
        'done'         => 'color:#9ca3af;border-color:#d1d5db;',
    ];
    $statusIcon = [
        'open'         => '',
        'closing_soon' => ' ⚡',
        'partial'      => ' ⚠',
        'closed'       => ' ✕',
        'done'         => ' ✓',
    ];
@endphp

<div wire:poll.60000ms x-data="{
        number: '',
        startNumber: '',
        amount: '',
        active: 'number',
        showBetList: true,
        popupMessage: '',
        showPopup: false,
        betMode: $wire.entangle('betMode'),
        endNumber: '',
        showAlert(msg) { this.popupMessage = msg; this.showPopup = true; },
        showConfirm: false,
        confirmId: null,
        confirmDelete(id) { this.confirmId = id; this.showConfirm = true; },
        doDelete() { $wire.removeBet(this.confirmId); this.showConfirm = false; this.confirmId = null; },
        toastMessage: '',
        showToast: false,
        toast(msg) { this.toastMessage = msg; this.showToast = true; setTimeout(() => { this.showToast = false; }, 2000); },
        init() {
            this.$watch('startNumber', (val) => {
                if ((this.betMode === 2 || this.betMode === 5) && val !== '') {
                    let s = parseInt(val);
                    if (!isNaN(s)) {
                        let units = s % 10;
                        this.endNumber = this.betMode === 2
                            ? (90  + units).toString().padStart(2, '0')
                            : (990 + units).toString().padStart(3, '0');
                    }
                } else {
                    this.endNumber = '';
                }
            });
            this.$watch('betMode', (newVal, oldVal) => {
                this.endNumber = '';
                // Don't clear startNumber on the auto-switch Tail(3)→Tail2(4) — the user was mid-typing
                if (!(oldVal === 3 && newVal === 4)) { this.startNumber = ''; }
            });
        },
        numpad(key) {
            if (key === 'X') {
                if (this.active === 'number')           this.number      = '';
                else if (this.active === 'startNumber') this.startNumber = '';
                else if (this.active === 'endNumber')   this.endNumber   = '';
                else                                    this.amount      = '';
                return;
            }
            if (this.active === 'number') {
                this.number = (this.number + key).slice(0,3);
            } else if (this.active === 'startNumber') {
                let next = this.startNumber + key;
                if (this.betMode === 2) { this.startNumber = next.slice(0,2); }
                else { this.startNumber = next.slice(0,3); }
            } else if (this.active === 'endNumber') {
                let next = (this.endNumber + key).slice(0, 2);
                if (this.startNumber !== '' && next.length === 2) {
                    let units = parseInt(this.startNumber) % 10;
                    let corrected = Math.floor(parseInt(next) / 10) * 10 + units;
                    next = corrected.toString().padStart(2, '0');
                }
                this.endNumber = next;
            } else {
                this.amount = (this.amount + key).slice(0,10);
            }
        },
        addBet() {
            if (this.betMode === 1) {
                if (!this.number) { this.showAlert('{{ __("number_required") }}'); return; }
                if (!this.amount || parseFloat(this.amount) <= 0) { this.showAlert('{{ __("amount_required") }}'); return; }
                $wire.addBet(this.number, this.amount);
            } else {
                if (this.betMode === 4 && this.startNumber.length < 3) { this.showAlert('{{ __("tail2_min_3_digits") }}'); return; }
                if (!this.startNumber) { this.showAlert('{{ __("number_required") }}'); return; }
                if (this.betMode === 2) {
                    let start = parseInt(this.startNumber);
                    let end   = parseInt(this.endNumber);
                    let units = start % 10;
                    let maxEnd = 90 + units;
                    if (!this.endNumber || isNaN(end) || end < start || end > maxEnd) {
                        this.showAlert('{{ __("invalid_end_number") }}'); return;
                    }
                }
                if (!this.amount || parseFloat(this.amount) <= 0) { this.showAlert('{{ __("amount_required") }}'); return; }
                $wire.addRangeBet(this.startNumber, this.amount,
                    this.betMode === 2 ? this.endNumber : '');
            }
        }
     }"
     @bet-added.window="
         toast('{{ __('bet_added') }}');
         if (betMode === 1) {
             number = ''; amount = ''; active = 'number';
             $nextTick(() => $refs.numberInput && $refs.numberInput.focus());
         } else {
             startNumber = ''; endNumber = ''; amount = ''; active = 'startNumber';
             $nextTick(() => $refs.startNumberInput && $refs.startNumberInput.focus());
         }
     "
     @bet-closed.window="showAlert('{{ __('bet_closed') }}')"
     @bet-removed.window="toast('{{ __('record_deleted') }}')">

    {{-- Flash success --}}
    @if ($flashSuccess)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(()=>show=false,4000)"
             class="mx-3 mt-2 px-4 py-2 rounded text-sm text-white bg-green-500">
            {{ $flashSuccess }}
        </div>
    @endif

    {{-- Flash error (bet closed) --}}
    @if ($flashError)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(()=>show=false,4000)"
             class="mx-3 mt-2 px-4 py-2 rounded text-sm text-white bg-red-500">
            {{ $flashError }}
        </div>
    @endif

    {{-- Top bar: date + session tabs --}}
    <div class="flex items-center gap-2 px-3 py-2 bg-white border-b" style="border-color:#D4A017;">
        <span class="text-xs font-semibold" style="color:#DC143C;">{{ $today }}</span>
        <div class="flex gap-1 ml-auto flex-wrap">
            @foreach ($availableSessions as $s)
                @php
                    $st        = $s->sessionStatus();
                    $isActive  = $session === $s->session_key;
                    $isClosed  = in_array($st, ['closed', 'done']);
                    $btnStyle  = $isActive
                        ? 'background-color:#DC143C;color:#fff;border-color:#DC143C;'
                        : ($isClosed
                            ? 'background-color:#f9fafb;' . ($statusStyle[$st] ?? '')
                            : 'background-color:#fff;color:#DC143C;border-color:#DC143C;');
                @endphp
                <button wire:click="$set('session','{{ $s->session_key }}')" type="button"
                        class="px-2 py-1 text-xs font-bold rounded border transition-all leading-tight flex flex-col items-center"
                        style="{{ $btnStyle }}">
                    <span>{{ $s->session_name }}{{ $statusIcon[$st] ?? '' }}</span>
                    <span class="font-normal opacity-80" style="font-size:10px;">{{ \Carbon\Carbon::parse($s->result_time)->format('H:i') }}</span>
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
                        <div wire:key="{{ $bet['id'] }}"
                             @click="confirmDelete('{{ $bet['id'] }}')"
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
                    <p class="text-center text-xs text-gray-400 py-2">{{ __('no_bets_found') }}</p>
                @else
                    @foreach ($bets as $idx => $bet)
                        <div wire:key="m-{{ $bet['id'] }}"
                             @click="confirmDelete('{{ $bet['id'] }}')"
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

    {{-- Success toast --}}
    <div x-show="showToast" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-4 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 px-5 py-3 rounded-xl text-white font-bold text-sm shadow-lg"
         style="background-color:#16a34a;">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
        </svg>
        <span x-text="toastMessage"></span>
    </div>

    {{-- Alert popup --}}
    <div x-show="showPopup" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center"
         style="background-color:rgba(0,0,0,0.45);">
        <div class="bg-white rounded-2xl shadow-2xl mx-4 w-72 text-center overflow-hidden">
            <div class="py-4 px-6" style="background-color:#DC143C;">
                <svg class="mx-auto w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <div class="px-6 py-4">
                <p class="font-bold text-base mb-4" style="color:#DC143C;" x-text="popupMessage"></p>
                <button type="button" @click="showPopup=false"
                        class="w-full py-2 rounded-lg text-white font-bold text-sm"
                        style="background-color:#DC143C;">OK</button>
            </div>
        </div>
    </div>

    {{-- Confirm delete popup --}}
    <div x-show="showConfirm" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center"
         style="background-color:rgba(0,0,0,0.45);">
        <div class="bg-white rounded-2xl shadow-2xl mx-4 w-72 text-center overflow-hidden">
            <div class="py-4 px-6" style="background-color:#D4A017;">
                <svg class="mx-auto w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div class="px-6 py-4">
                <p class="font-bold text-base mb-4" style="color:#374151;">{{ __('delete_confirm_msg') }}</p>
                <div class="flex gap-2">
                    <button type="button" @click="showConfirm=false"
                            class="w-full py-2 rounded-lg font-bold text-sm border-2"
                            style="border-color:#D4A017;color:#D4A017;background:#fff;">{{ __('cancel') }}</button>
                    <button type="button" @click="doDelete()"
                            class="w-full py-2 rounded-lg font-bold text-sm text-white"
                            style="background-color:#DC143C;">{{ __('delete') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile: fixed bottom panel (controls + numpad) --}}
    <div class="md:hidden fixed bottom-[57px] left-0 right-0 bg-white border-t z-20 flex flex-col"
         style="border-color:#D4A017;">
        <div class="px-2 pt-1 pb-1 flex flex-col gap-1">
            @include('livewire.partials.bet-controls', ['compact' => true, 'hideNumpad' => true, 'letters' => $letters])
        </div>
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
