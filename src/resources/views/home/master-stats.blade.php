@if(auth()->user()->isMaster() && isset($staffStats) && $staffStats->isNotEmpty())

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-3 gap-2 mx-4 mt-2 mb-3">
    <div class="kpi-card">
        <div class="kpi-value">{{ $staffStats->count() }}</div>
        <div class="kpi-label">Staff</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-value">{{ $staffStats->sum('tickets_today') }}</div>
        <div class="kpi-label">Tickets</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-value">{{ $staffStats->sum('amount_today') >= 1000000 ? number_format($staffStats->sum('amount_today') / 1000000, 1) . 'M' : number_format($staffStats->sum('amount_today') / 1000, 0) . 'K' }}</div>
        <div class="kpi-label">Amount KHR</div>
    </div>
</div>

{{-- Staff Table --}}
<div x-data="{ open: true }" class="mx-4 mb-4 luxury-card">

    {{-- Section header --}}
    <button @click="open=!open" type="button"
            class="w-full flex items-center justify-between px-4 py-2.5"
            style="border-bottom: 1px solid var(--ht-border);">
        <span class="luxury-section-title">Today's Staff Summary — {{ today()->format('d M Y') }}</span>
        <svg class="w-4 h-4 flex-shrink-0 transition-transform" :class="open ? 'rotate-180':''"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--ht-crimson);">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="overflow-x-auto">
            <table class="w-full text-xs font-nunito">
                <thead>
                    <tr style="background-color: #B91C1C; color: #fff;">
                        <th class="px-4 py-2.5 text-left font-semibold tracking-wide">Staff</th>
                        <th class="px-4 py-2.5 text-center font-semibold tracking-wide">Tickets</th>
                        <th class="px-4 py-2.5 text-right font-semibold tracking-wide">Amount (KHR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffStats as $i => $staff)
                    <tr style="background-color: {{ $i % 2 === 0 ? '#F5EDDA' : '#FFFDF7' }};">
                        <td class="px-4 py-2.5 font-semibold" style="color: #B91C1C;">
                            <span class="inline-block w-1 h-3 rounded-sm mr-1.5 align-middle" style="background-color: #C8922A; opacity: 0.6;"></span>
                            {{ $staff->name }}
                        </td>
                        <td class="px-4 py-2.5 text-center" style="color: var(--ht-text);">{{ $staff->tickets_today }}</td>
                        <td class="px-4 py-2.5 text-right font-bold" style="color: #C8922A;">{{ number_format($staff->amount_today) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #C8922A; color: #fff;">
                        <td class="px-4 py-2.5 font-bold tracking-wide uppercase text-[10px]">Total</td>
                        <td class="px-4 py-2.5 text-center font-bold">{{ $staffStats->sum('tickets_today') }}</td>
                        <td class="px-4 py-2.5 text-right font-bold">{{ number_format($staffStats->sum('amount_today')) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endif
