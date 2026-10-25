@php
    $totalAmount = collect($bets)->sum(fn ($b) => $b['amount'] * $b['count']);
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
        endNumber: '',
        amount: '',
        active: 'number',
        showBetList: true,
        popupMessage: '',
        showPopup: false,
        // Client-only: entangling it let slow Livewire responses reset the mode mid-entry
        betMode: 1,
        posts: $wire.entangle('posts'),
        showAlert(msg) { this.popupMessage = msg; this.showPopup = true; },
        showConfirm: false,
        confirmIndex: null,
        confirmDelete(i) { this.confirmIndex = i; this.showConfirm = true; },
        doDelete() { $wire.removeBet(this.confirmIndex); this.showConfirm = false; this.confirmIndex = null; },
        showSubmit: false,
        toastMessage: '',
        showToast: false,
        toast(msg) { this.toastMessage = msg; this.showToast = true; setTimeout(() => { this.showToast = false; }, 2000); },
        init() {
            this.$watch('betMode', () => { this.number = ''; this.endNumber = ''; this.active = 'number'; });
        },
        numpad(key) {
            if (key === 'X') { this[this.active] = ''; return; }
            const max = this.active === 'amount' ? 10 : 3;
            this[this.active] = (this[this.active] + key).slice(0, max);
        },
        // Mirror of App\Support\BetNumbers::expand() — keep both in sync
        expand() {
            const n = this.number, e = this.endNumber, len = n.length;
            if (this.betMode !== 4 && !/^\d+$/.test(n + e)) return [];
            const uniq = (a) => [...new Set(a)];
            switch (this.betMode) {
                case 1: return len >= 1 && len <= 3 ? [n] : [];
                case 2: {
                    if (len < 2 || len > 3) return [];
                    const perm = (s) => s.length <= 1 ? [s] : [...s].flatMap((d, i) => perm(s.slice(0, i) + s.slice(i + 1)).map(r => d + r));
                    return uniq(perm(n));
                }
                case 3: {
                    if (len < 2 || len > 3 || e.length !== len || +e <= +n) return [];
                    const diff = [...n].map((d, i) => d !== e[i] ? i : -1).filter(i => i >= 0);
                    const step = diff.length === 1 ? 10 ** (len - 1 - diff[0]) : 1;
                    const out = [];
                    for (let x = +n; x <= +e; x += step) out.push(String(x).padStart(len, '0'));
                    return out;
                }
                case 4: {
                    const l = n === '' ? 2 : len;
                    return l === 2 || l === 3 ? [...Array(10).keys()].map(d => String(d).repeat(l)) : [];
                }
                case 5: return len === 1 ? uniq([...Array(10).keys()].flatMap(x => [n + x, x + n])) : [];
            }
            return [];
        },
        betBreakdown() {
            const amt = parseInt(this.amount);
            const nums = this.expand();
            if (!amt || !nums.length || !this.posts.length) return null;
            return { count: nums.length, posts: this.posts.length, nums, total: amt * nums.length * this.posts.length };
        },
        addBet() {
            const amt = parseInt(this.amount);
            if (!this.posts.length) { this.showAlert('{{ __('select_post') }}'); return; }
            if (!this.expand().length) { this.showAlert('{{ __('number_required') }}'); return; }
            if (!amt || amt < 100) { this.showAlert('{{ __('amount_min_100') }}'); return; }
            if (amt % 100 !== 0) { this.showAlert('{{ __('amount_multiple_100') }}'); return; }
            $wire.addBets(this.number, this.amount, this.endNumber, this.betMode);
        },
        formatAmount(v) {
            const n = parseInt(v);
            return isNaN(n) ? '' : n.toLocaleString('en-US');
        },
     }"
     @bet-added.window="toast('{{ __('bet_added') }}'); number = ''; endNumber = ''; active = 'number';"
     @bet-closed.window="showAlert('{{ __('bet_closed') }}')"
     @bet-removed.window="toast('{{ __('record_deleted') }}')">

    @if ($flashSuccess)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(()=>show=false,4000)"
             class="mx-3 mt-2 px-4 py-2 rounded text-sm text-white" style="background-color:#2A5F47;">
            {{ $flashSuccess }}
        </div>
    @endif

    @if ($flashError)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(()=>show=false,4000)"
             class="mx-3 mt-2 px-4 py-2 rounded text-sm text-white bg-red-500">
            {{ $flashError }}
        </div>
    @endif

    {{-- Top bar: date + today's money · lottery type + draw time --}}
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2 px-3 py-2 border-b" style="background-color:#FAF7F2;border-color:#C8BBA8;">
        <span class="text-xs" style="color:#7A6E64;">{{ __('date_bet') }}: <b style="color:#2A5F47;">{{ $today }}</b></span>
        <span class="text-xs" style="color:#7A6E64;">{{ __('money') }}: <b style="color:#D4A017;">{{ number_format($todaySales) }} KHR</b></span>
        <div class="flex items-center gap-1 ml-auto">
            @foreach ($lotteryTypes as $t)
                <button type="button" wire:click="$set('lotteryType','{{ $t }}')"
                        class="text-xs font-bold rounded border-2 px-2.5 py-1"
                        style="{{ $lotteryType === $t ? 'background-color:#2A5F47;color:#fff;border-color:#2A5F47;' : 'background-color:#fff;color:#2A5F47;border-color:#2A5F47;' }}">{{ $t }}</button>
            @endforeach
            <select wire:model.live="session"
                    class="text-xs font-bold rounded border-2 px-2 py-1 outline-none cursor-pointer"
                    style="border-color:#2A5F47;color:#2A5F47;background-color:#fff;">
                @foreach ($availableSessions as $s)
                    @php $st = $s->sessionStatus(); $off = in_array($st, ['closed', 'done']); @endphp
                    <option value="{{ $s->session_key }}" {{ $off ? 'disabled' : '' }} style="{{ $off ? 'color:#9ca3af;' : 'color:#2A5F47;' }}">
                        {{ \Carbon\Carbon::parse($s->result_time)->format('g:iA') }} · {{ $s->session_name }}{{ $statusIcon[$st] ?? '' }}
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
                @include('livewire.partials.bet-list', ['compact' => false])
            </div>
            <div class="mx-3 mt-2 mb-3" style="background:#fff;border:1px solid #E2D9CC;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.07);padding:12px 14px;">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xs font-semibold tracking-wide" style="color:#7A6E64;">{{ __('total') }}</span>
                    <span class="font-cormorant font-bold text-2xl" style="color:#2A5F47;">{{ number_format($totalAmount) }} KHR</span>
                    <span class="ml-auto border font-semibold text-xs px-2.5 py-0.5 rounded-full" style="background-color:rgba(42,95,71,0.1);border-color:#2A5F47;color:#2A5F47;">{{ collect($bets)->sum('count') }} R</span>
                </div>
                <button @click="showSubmit = true" type="button"
                        {{ empty($bets) ? 'disabled' : '' }}
                        class="w-full py-4 rounded-lg text-white font-bold text-sm disabled:opacity-40 tracking-widest uppercase"
                        style="background-color:#2A5F47;">{{ __('submit') }}</button>
            </div>
        </div>

        {{-- Right: input controls --}}
        <div class="flex flex-col w-1/2 overflow-hidden" style="background-color:#F4EFE6;">
            <div class="flex-1 overflow-y-auto p-3 flex flex-col gap-2">
                @include('livewire.partials.bet-controls', ['compact' => false, 'hideNumpad' => false, 'hideClear' => true, 'hideAdd' => true, 'letters' => $letters])
            </div>
            <div class="mx-3 mt-2 mb-3" style="background:#fff;border:1px solid #E2D9CC;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.07);padding:12px 14px;">
                <button type="button" @click="addBet()"
                        class="w-full py-3 rounded-lg text-white font-bold text-sm tracking-widest uppercase"
                        style="background-color:#D4A017;">{{ __('add') }}</button>
            </div>
        </div>
    </div>

    {{-- ═══ Mobile layout ═══════════════════════════════════════ --}}
    <div class="flex md:hidden flex-col overflow-auto" style="padding-bottom:360px;">
        <div class="border-t" style="border-color:#C8BBA8;">
            <button type="button" @click="showBetList=!showBetList"
                    class="w-full flex items-center justify-between px-3 py-2" style="background-color:#FAF7F2;color:#2A5F47;">
                <div class="flex items-center gap-2 text-xs font-bold">
                    <span>{{ __('total') }}={{ number_format($totalAmount) }}KHR</span>
                    <span class="px-2 py-0.5 rounded-full text-white text-xs" style="background-color:#D4A017;">{{ collect($bets)->sum('count') }}R</span>
                </div>
                <svg class="w-4 h-4 transition-transform" :class="showBetList ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="showBetList" class="max-h-32 overflow-y-auto border-b" style="border-color:#C8BBA8;">
                @include('livewire.partials.bet-list', ['compact' => true])
            </div>

            <div class="px-2 py-1.5" style="background-color:#FAF7F2;">
                <button @click="showSubmit = true" type="button"
                        {{ empty($bets) ? 'disabled' : '' }}
                        class="w-full py-1.5 rounded-lg text-white font-bold text-sm disabled:opacity-40 tracking-widest uppercase"
                        style="background-color:#2A5F47;">{{ __('submit') }}</button>
            </div>
        </div>
    </div>

    {{-- Success toast --}}
    <div x-show="showToast" x-cloak x-transition
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
            <div class="px-6 py-5">
                <p class="font-bold text-base mb-4" style="color:#2C2826;">{{ __('delete_confirm_msg') }}</p>
                <div class="flex gap-2">
                    <button type="button" @click="showConfirm=false"
                            class="w-full py-2.5 rounded-lg font-bold text-sm border-2"
                            style="border-color:#2A5F47;color:#2A5F47;background:#fff;">{{ __('cancel') }}</button>
                    <button type="button" @click="doDelete()"
                            class="w-full py-2.5 rounded-lg font-bold text-sm text-white"
                            style="background-color:#9B2335;">{{ __('delete') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm submit popup (បញ្ជាក់) --}}
    <div x-show="showSubmit" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center"
         style="background-color:rgba(44,40,38,0.5);backdrop-filter:blur(4px);">
        <div class="rounded-xl shadow-2xl mx-4 w-72 text-center overflow-hidden" style="background-color:#fff;">
            <div class="py-3 px-6 text-white font-bold" style="background-color:#2A5F47;">{{ __('confirm') }}</div>
            <div class="px-6 py-5">
                <p class="text-sm mb-1" style="color:#7A6E64;">{{ \App\Models\BetTimeSetting::label($session) }}</p>
                <p class="font-bold text-xl mb-4" style="color:#2C2826;">{{ __('total') }}={{ number_format($totalAmount) }}KHR</p>
                <div class="flex gap-2">
                    <button type="button" @click="showSubmit=false"
                            class="w-full py-2.5 rounded-lg font-bold text-sm border-2"
                            style="border-color:#2A5F47;color:#2A5F47;background:#fff;">{{ __('cancel') }}</button>
                    <button type="button" @click="showSubmit=false; $wire.submitBets()"
                            class="w-full py-2.5 rounded-lg font-bold text-sm text-white"
                            style="background-color:#2A5F47;">{{ __('submit') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile: fixed bottom panel (controls + numpad) --}}
    <div class="md:hidden fixed bottom-[57px] left-0 right-0 border-t z-20 flex flex-col max-h-[60vh] overflow-y-auto"
         style="background-color:#FAF7F2;border-color:#C8BBA8;">
        <div class="px-2 pt-1 pb-2 flex flex-col gap-1">
            @include('livewire.partials.bet-controls', ['compact' => true, 'hideNumpad' => false, 'letters' => $letters])
        </div>
    </div>
</div>
