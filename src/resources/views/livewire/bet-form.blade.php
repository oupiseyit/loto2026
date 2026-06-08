@php
    $NUMPAD      = ['1','2','3','4','5','6','7','8','9','0','00','X'];
    $totalAmount = collect($bets)->sum(function ($b) {
        $mul = (int) preg_replace('/[^0-9]/', '', $b['letter'] ?? '');
        return $b['amount'] * max(1, $mul);
    });

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
        letter: $wire.entangle('letter'),
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
                if (val === '' || this.betMode === 1 || this.betMode === 5) { this.endNumber = ''; return; }
                const s = parseInt(val);
                if (isNaN(s)) { this.endNumber = ''; return; }
                if (this.betMode === 2) {
                    const units = s % 10;
                    this.endNumber = val.length <= 2
                        ? (90 + units).toString().padStart(2, '0')
                        : (900 + (s % 100)).toString().padStart(3, '0');
                } else if (this.betMode === 3 && val.length === 3) {
                    this.endNumber = (Math.floor(s / 100) * 100 + 90 + s % 10).toString().padStart(3, '0');
                } else if (this.betMode === 4) {
                    this.endNumber = (Math.floor(s / 10) * 10 + 9).toString().padStart(val.length, '0');
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
                const maxLen = this.betMode === 3 ? 3 : (this.startNumber.length || 3);
                this.endNumber = (this.endNumber + key).slice(0, maxLen);
            } else {
                this.amount = (this.amount + key).slice(0,10);
            }
        },
        addBet() {
            const amtVal = parseFloat(this.amount);
            if (!this.amount || amtVal < 100) { this.showAlert('{{ __("amount_min_100") }}'); return; }
            if (amtVal % 100 !== 0) { this.showAlert('{{ __("amount_multiple_100") }}'); return; }
            if (this.betMode === 1) {
                if (!this.number) { this.showAlert('{{ __("number_required") }}'); return; }
                $wire.addBet(this.number, this.amount);
            } else {
                if (this.betMode === 4 && this.startNumber.length < 3) { this.showAlert('{{ __("tail2_min_3_digits") }}'); return; }
                if (!this.startNumber) { this.showAlert('{{ __("number_required") }}'); return; }
                if (this.betMode === 2 || this.betMode === 3 || this.betMode === 4) {
                    const start = parseInt(this.startNumber);
                    const end   = parseInt(this.endNumber);
                    if (!this.endNumber || isNaN(end) || end < start) {
                        this.showAlert('{{ __("invalid_end_number") }}'); return;
                    }
                }
                $wire.addRangeBet(this.startNumber, this.amount,
                    (this.betMode === 2 || this.betMode === 3 || this.betMode === 4) ? this.endNumber : '');
            }
        },
        formatAmount(v) {
            if (!v) return '';
            const n = parseInt(v);
            return isNaN(n) ? '' : n.toLocaleString('en-US');
        },
        posMultiplier() {
            const m = parseInt((this.letter || '').replace(/[^0-9]/g, ''));
            return (isNaN(m) || m <= 0) ? 1 : m;
        },
        betBreakdown() {
            const amt = parseFloat(this.amount);
            if (!amt || amt <= 0) return null;
            const mul = this.posMultiplier();
            if (this.betMode === 1) {
                if (!this.number) return null;
                return { rows: [{ num: this.number, amt, rowTotal: amt * mul }], total: amt * mul };
            }
            if (this.betMode === 5) {
                if (this.startNumber.length !== 3) return null;
                const digits = this.startNumber.split('');
                const perms = []; const seen = new Set();
                const go = (rem, cur) => {
                    if (!rem.length) { const k=cur.join(''); if(!seen.has(k)){seen.add(k);perms.push(k);} return; }
                    const used = new Set();
                    rem.forEach((d,i) => { if(used.has(d))return; used.add(d); const nx=[...rem];nx.splice(i,1); go(nx,[...cur,d]); });
                };
                go(digits, []);
                if (!perms.length) return null;
                return { rows: perms.map(p => ({ num: p, amt, rowTotal: amt * mul })), total: amt * mul * perms.length };
            }
            if (!this.startNumber || !this.endNumber) return null;
            const start = parseInt(this.startNumber);
            const end = parseInt(this.endNumber);
            if (isNaN(start) || isNaN(end) || end < start) return null;
            const step = this.betMode === 2
                ? (this.startNumber.length <= 2 ? 10 : 100)
                : this.betMode === 3 ? 10 : 1;
            const padLen = this.startNumber.length;
            const rows = [];
            for (let n = start; n <= end; n += step) {
                rows.push({ num: n.toString().padStart(padLen, '0'), amt, rowTotal: amt * mul });
            }
            if (!rows.length) return null;
            return { rows, total: amt * mul * rows.length };
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
             class="mx-3 mt-2 px-4 py-2 rounded text-sm text-white" style="background-color:#2A5F47;">
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

    {{-- Top bar: date + session select --}}
    <div class="flex items-center gap-2 px-3 py-2 border-b" style="background-color:#FAF7F2;border-color:#C8BBA8;">
        <span class="text-xs font-semibold tracking-wide" style="color:#2A5F47;">{{ $today }}</span>
        <div class="ml-auto">
            <select wire:model.live="session"
                    class="text-xs font-bold rounded border-2 px-2 py-1.5 outline-none cursor-pointer"
                    style="border-color:#2A5F47;color:#2A5F47;background-color:#fff;">
                @foreach ($availableSessions as $s)
                    @php $st = $s->sessionStatus(); @endphp
                    <option value="{{ $s->session_key }}"
                            {{ in_array($st, ['closed', 'done']) ? 'disabled' : '' }}
                            style="{{ in_array($st, ['closed', 'done']) ? 'color:#9ca3af;' : 'color:#2A5F47;' }}">
                        {{ $s->session_name }}{{ $statusIcon[$st] ?? '' }} · {{ \Carbon\Carbon::parse($s->result_time)->format('H:i') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ═══ Desktop layout ═══════════════════════════════════════ --}}
    <div class="hidden md:flex flex-1 overflow-hidden" style="height: calc(100vh - 7rem);">

        {{-- Left: bet list --}}
        <div class="flex flex-col w-1/2 border-r" style="border-color:#C8BBA8;background-color:#F4EFE6;">
            <div class="m-3 mb-0 flex flex-col flex-1 overflow-hidden" style="background:#fff;border:1px solid #E2D9CC;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.07);">
            <div class="grid text-center text-xs font-bold text-white py-1.5" style="background-color:#2A5F47;grid-template-columns:2rem 1fr 1fr 1fr 1fr 2.5rem;border-radius:8px 8px 0 0;">
                <span>#</span><span>{{ __('number') }}</span><span>{{ __('amount') }}</span><span>{{ __('bet_type') }}</span><span>Sub-total</span><span></span>
            </div>
            <div class="flex-1 overflow-y-auto">
                @if (empty($bets))
                    <div class="flex items-center justify-center h-full opacity-10 select-none pointer-events-none">
                        <div class="text-center">
                            <div class="font-cormorant italic text-6xl font-bold" style="color:#2A5F47;">HT</div>
                            <div class="text-sm tracking-widest" style="color:#2A5F47;">ភ្នាក់</div>
                        </div>
                    </div>
                @else
                    @foreach ($bets as $idx => $bet)
                        @php
                            $dMul = (int) preg_replace('/[^0-9]/', '', $bet['letter'] ?? '');
                            $dSubtotal = $bet['amount'] * max(1, $dMul);
                        @endphp
                        <div wire:key="{{ $bet['id'] }}"
                             class="grid text-center text-sm py-1.5 border-b items-center"
                             style="background-color:{{ $idx % 2 === 0 ? '#FAF7F2' : '#fff' }};border-color:#e9e4dc;grid-template-columns:2rem 1fr 1fr 1fr 1fr 2.5rem;">
                            <span class="font-medium" style="color:#9ca3af;">{{ $idx + 1 }}</span>
                            <span class="font-bold font-cormorant text-lg" style="color:#2C2826;">{{ $bet['number'] }}</span>
                            <span style="color:#7A6E64;">{{ number_format($bet['amount']) }}</span>
                            <span class="uppercase font-bold" style="color:#2A5F47;">{{ $bet['bet_type'] }}</span>
                            <span class="font-bold" style="color:#D4A017;">{{ number_format($dSubtotal) }}</span>
                            <span class="flex items-center justify-center">
                                <button type="button" @click="confirmDelete('{{ $bet['id'] }}')"
                                        class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold border"
                                        style="background-color:transparent;border-color:#C8BBA8;color:#9B2335;"
                                        onmouseover="this.style.backgroundColor='#9B2335';this.style.color='#fff';this.style.borderColor='#9B2335';"
                                        onmouseout="this.style.backgroundColor='transparent';this.style.color='#9B2335';this.style.borderColor='#C8BBA8';">✕</button>
                            </span>
                        </div>
                    @endforeach
                @endif
            </div>
            </div>{{-- /list-card --}}
            <div class="mx-3 mt-2 mb-3" style="background:#fff;border:1px solid #E2D9CC;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.07);padding:12px 14px;">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xs font-semibold tracking-wide" style="color:#7A6E64;">{{ __('total') }}</span>
                    <span class="font-cormorant font-bold text-2xl" style="color:#2A5F47;">{{ number_format($totalAmount) }}</span>
                    <span class="ml-auto border font-semibold text-xs px-2.5 py-0.5 rounded-full" style="background-color:rgba(42,95,71,0.1);border-color:#2A5F47;color:#2A5F47;">{{ count($bets) }} R</span>
                </div>
                <button wire:click="submitBets" type="button"
                        {{ empty($bets) ? 'disabled' : '' }}
                        class="w-full py-4 rounded-lg text-white font-bold text-sm disabled:opacity-40 tracking-widest uppercase transition-all relative overflow-hidden"
                        style="background-color:#2A5F47;"
                        onmouseover="if(!this.disabled){this.style.backgroundColor='#3D8965';this.style.transform='translateY(-1px)';}"
                        onmouseout="this.style.backgroundColor='#2A5F47';this.style.transform='';">
                    <span wire:loading.remove wire:target="submitBets">{{ __('submit') }}</span>
                    <span wire:loading wire:target="submitBets">{{ __('submitting') }}</span>
                </button>
            </div>
        </div>

        {{-- Right: input controls --}}
        <div class="flex flex-col w-1/2 overflow-hidden" style="background-color:#F4EFE6;">
            <div class="flex-1 overflow-y-auto p-3 flex flex-col gap-2">
                @include('livewire.partials.bet-controls', ['compact' => false, 'hideNumpad' => true, 'hideClear' => true, 'hideAdd' => true, 'letters' => $letters])
            </div>
            <div class="mx-3 mt-2 mb-3" style="background:#fff;border:1px solid #E2D9CC;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.07);padding:12px 14px;">
            <!-- <div class="p-3 pt-2 border-t" style="border-color:#E2D9CC;background-color:#F4EFE6;"> -->
                <button type="button" @click="addBet()"
                        class="w-full py-3 rounded-lg text-white font-bold text-sm tracking-widest uppercase transition-all"
                        style="background-color:#D4A017;"
                        onmouseover="this.style.backgroundColor='#B8860B';"
                        onmouseout="this.style.backgroundColor='#D4A017';">{{ __('add') }}</button>
            </div>
        </div>
    </div>

    {{-- ═══ Mobile layout ═══════════════════════════════════════ --}}
    <div class="flex md:hidden flex-col overflow-auto" style="padding-bottom:320px;">
        <div class="border-t" style="border-color:#C8BBA8;">
            {{-- Toggle bet list --}}
            <button type="button" @click="showBetList=!showBetList"
                    class="w-full flex items-center justify-between px-3 py-2" style="background-color:#FAF7F2;color:#2A5F47;">
                <div class="flex items-center gap-2 text-xs font-bold">
                    <span>{{ __('total') }}: {{ number_format($totalAmount) }}</span>
                    <span class="px-2 py-0.5 rounded-full text-white text-xs" style="background-color:#D4A017;">{{ count($bets) }}R</span>
                </div>
                <svg class="w-4 h-4 transition-transform" :class="showBetList ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="showBetList" class="max-h-32 overflow-y-auto border-b" style="border-color:#C8BBA8;">
                <div class="grid text-center text-[9px] font-bold text-white py-1" style="background-color:#2A5F47;grid-template-columns:1.4rem 1fr 1fr 1fr 1fr 1.8rem;">
                    <span>#</span><span>{{ __('number') }}</span><span>{{ __('amount') }}</span><span>{{ __('bet_type') }}</span><span>Sub-total</span><span></span>
                </div>
                @if (empty($bets))
                    <p class="text-center text-xs text-gray-400 py-2">{{ __('no_bets_found') }}</p>
                @else
                    @foreach ($bets as $idx => $bet)
                        @php
                            $mMul = (int) preg_replace('/[^0-9]/', '', $bet['letter'] ?? '');
                            $mSubtotal = $bet['amount'] * max(1, $mMul);
                        @endphp
                        <div wire:key="m-{{ $bet['id'] }}"
                             class="grid text-center text-xs py-1 border-b items-center"
                             style="background-color:{{ $idx % 2 === 0 ? '#FAF7F2' : '#fff' }};border-color:#e9e4dc;grid-template-columns:1.4rem 1fr 1fr 1fr 1fr 1.8rem;">
                            <span style="color:#9ca3af;">{{ $idx + 1 }}</span>
                            <span class="font-bold">{{ $bet['number'] }}</span>
                            <span>{{ number_format($bet['amount']) }}</span>
                            <span class="uppercase font-bold" style="color:#2A5F47;">{{ $bet['bet_type'] }}</span>
                            <span class="font-bold" style="color:#D4A017;">{{ number_format($mSubtotal) }}</span>
                            <span class="flex items-center justify-center">
                                <button type="button" @click="confirmDelete('{{ $bet['id'] }}')"
                                        class="w-4 h-4 rounded-full flex items-center justify-center text-[8px] font-bold"
                                        style="background-color:#9B2335;color:#fff;">✕</button>
                            </span>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="px-2 py-1.5" style="background-color:#FAF7F2;">
                <button wire:click="submitBets" type="button"
                        {{ empty($bets) ? 'disabled' : '' }}
                        class="w-full py-1.5 rounded-lg text-white font-bold text-sm disabled:opacity-40 tracking-widest uppercase"
                        style="background-color:#2A5F47;">
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
         style="background-color:#2A5F47;">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
        </svg>
        <span x-text="toastMessage"></span>
    </div>

    {{-- Alert popup --}}
    <div x-show="showPopup" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center"
         style="background-color:rgba(44,40,38,0.5);backdrop-filter:blur(4px);">
        <div class="rounded-xl shadow-2xl mx-4 w-72 text-center overflow-hidden" style="background-color:#fff;">
            <div class="py-5 px-6" style="background-color:#2A5F47;">
                <svg class="mx-auto w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <div class="px-6 py-5">
                <p class="font-bold text-base mb-4" style="color:#2C2826;" x-text="popupMessage"></p>
                <button type="button" @click="showPopup=false"
                        class="w-full py-2.5 rounded-lg text-white font-bold text-sm tracking-wide"
                        style="background-color:#9B2335;">OK</button>
            </div>
        </div>
    </div>

    {{-- Confirm delete popup --}}
    <div x-show="showConfirm" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center"
         style="background-color:rgba(44,40,38,0.5);backdrop-filter:blur(4px);">
        <div class="rounded-xl shadow-2xl mx-4 w-72 text-center overflow-hidden" style="background-color:#fff;">
            <div class="py-5 px-6" style="background-color:#2A5F47;">
                <svg class="mx-auto w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div class="px-6 py-5">
                <p class="font-bold text-base mb-4" style="color:#2C2826;">{{ __('delete_confirm_msg') }}</p>
                <div class="flex gap-2">
                    <button type="button" @click="showConfirm=false"
                            class="w-full py-2.5 rounded-lg font-bold text-sm border-2 tracking-wide"
                            style="border-color:#2A5F47;color:#2A5F47;background:#fff;">{{ __('cancel') }}</button>
                    <button type="button" @click="doDelete()"
                            class="w-full py-2.5 rounded-lg font-bold text-sm text-white tracking-wide"
                            style="background-color:#9B2335;">{{ __('delete') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile: fixed bottom panel (controls + numpad) --}}
    <div class="md:hidden fixed bottom-[57px] left-0 right-0 border-t z-20 flex flex-col"
         style="background-color:#FAF7F2;border-color:#C8BBA8;">
        <div class="px-2 pt-1 pb-1 flex flex-col gap-1">
            @include('livewire.partials.bet-controls', ['compact' => true, 'hideNumpad' => true, 'letters' => $letters])
        </div>
        <div class="px-2 pb-2 grid grid-cols-3 gap-1">
            @foreach ($NUMPAD as $key)
                @if ($key === 'X')
                    <button type="button" @click="numpad('X')"
                            class="h-8 rounded font-bold text-xs flex items-center justify-center"
                            style="background-color:rgba(155,35,53,0.12);color:#9B2335;border:1px solid rgba(155,35,53,0.25);">✕</button>
                @else
                    <button type="button" @click="numpad('{{ $key }}')"
                            class="h-8 rounded font-bold text-sm flex items-center justify-center"
                            style="background-color:#FAF7F2;color:#2C2826;border:1px solid #E2D9CC;font-family:'Cormorant Garamond',serif;">{{ $key }}</button>
                @endif
            @endforeach
        </div>
    </div>
</div>
