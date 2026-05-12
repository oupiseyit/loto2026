@if(auth()->user()->isAdmin() && isset($masterStats) && $masterStats->isNotEmpty())

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-3 gap-2 mx-4 mt-2 mb-3">
    <div class="kpi-card">
        <div class="kpi-value">{{ $masterStats->count() }}</div>
        <div class="kpi-label">Masters</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-value">{{ $masterStats->sum('tickets_today') }}</div>
        <div class="kpi-label">Tickets</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-value">{{ $masterStats->sum('amount_today') >= 1000000 ? number_format($masterStats->sum('amount_today') / 1000000, 1) . 'M' : number_format($masterStats->sum('amount_today') / 1000, 0) . 'K' }}</div>
        <div class="kpi-label">Amount KHR</div>
    </div>
</div>

{{-- Master Summary Table --}}
<div x-data="{ open: true }" class="mx-4 mb-4 luxury-card">

    <button @click="open=!open" type="button"
            class="w-full flex items-center justify-between px-4 py-2.5"
            style="border-bottom: 1px solid var(--ht-border);">
        <span class="luxury-section-title">Today's Master Summary — {{ today()->format('d M Y') }}</span>
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
                        <th class="px-4 py-2.5 text-left font-semibold tracking-wide">Master</th>
                        <th class="px-4 py-2.5 text-center font-semibold tracking-wide">Staff</th>
                        <th class="px-4 py-2.5 text-center font-semibold tracking-wide">Tickets</th>
                        <th class="px-4 py-2.5 text-right font-semibold tracking-wide">Amount (KHR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($masterStats as $i => $master)
                    <tr style="background-color: {{ $i % 2 === 0 ? '#F5EDDA' : '#FFFDF7' }};">
                        <td class="px-4 py-2.5 font-semibold" style="color: #B91C1C;">
                            <span class="inline-block w-1 h-3 rounded-sm mr-1.5 align-middle" style="background-color: #C8922A; opacity: 0.6;"></span>
                            {{ $master->name }}
                        </td>
                        <td class="px-4 py-2.5 text-center" style="color: var(--ht-text);">{{ $master->staff_count }}</td>
                        <td class="px-4 py-2.5 text-center" style="color: var(--ht-text);">{{ $master->tickets_today }}</td>
                        <td class="px-4 py-2.5 text-right font-bold" style="color: #C8922A;">{{ number_format($master->amount_today) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #C8922A; color: #fff;">
                        <td class="px-4 py-2.5 font-bold tracking-wide uppercase text-[10px]">Total</td>
                        <td class="px-4 py-2.5 text-center font-bold">{{ $masterStats->sum('staff_count') }}</td>
                        <td class="px-4 py-2.5 text-center font-bold">{{ $masterStats->sum('tickets_today') }}</td>
                        <td class="px-4 py-2.5 text-right font-bold">{{ number_format($masterStats->sum('amount_today')) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- 7-day line chart --}}
<div class="mx-4 mb-4 luxury-card px-4 py-3">
    <p class="luxury-section-title mb-3">Staff Bet Total — Last 7 Days</p>
    <canvas id="masterChart" height="110"></canvas>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const COLORS = [
        '#B91C1C','#C8922A','#2563eb','#16a34a',
        '#9333ea','#ea580c','#0891b2','#db2777',
    ];

    const labels  = @json($chartLabels);
    const masters = @json($masterStats->map(fn($m) => ['name' => $m->name, 'data' => $m->chart_data])->values());

    const datasets = masters.map((m, i) => ({
        label           : m.name,
        data            : m.data,
        borderColor     : COLORS[i % COLORS.length],
        backgroundColor : COLORS[i % COLORS.length] + '18',
        borderWidth     : 2.5,
        pointRadius     : 5,
        pointBackgroundColor: COLORS[i % COLORS.length],
        pointBorderColor: '#FFFDF7',
        pointBorderWidth: 1.5,
        tension         : 0.4,
        fill            : true,
    }));

    new Chart(document.getElementById('masterChart'), {
        type : 'line',
        data : { labels, datasets },
        options : {
            responsive : true,
            plugins : {
                legend  : {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        boxHeight: 10,
                        borderRadius: 3,
                        useBorderRadius: true,
                        font: { family: 'Nunito', size: 11 },
                        color: '#7A6347',
                        padding: 12,
                    }
                },
                tooltip : {
                    backgroundColor: '#FFFDF7',
                    titleColor: '#2C1F0E',
                    bodyColor: '#7A6347',
                    borderColor: '#E4D3B0',
                    borderWidth: 1,
                    titleFont: { family: 'Cormorant Garamond', size: 13, style: 'italic' },
                    bodyFont: { family: 'Nunito', size: 11 },
                    callbacks : {
                        label : ctx => ' ' + ctx.dataset.label + ': ' + Number(ctx.parsed.y).toLocaleString() + ' KHR',
                    },
                },
            },
            scales : {
                y : {
                    beginAtZero : true,
                    grid: { color: 'rgba(200, 146, 42, 0.1)' },
                    ticks : {
                        font     : { family: 'Nunito', size: 10 },
                        color    : '#7A6347',
                        callback : v => v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v,
                    },
                    border: { color: 'rgba(200, 146, 42, 0.2)' },
                },
                x : {
                    grid: { color: 'rgba(200, 146, 42, 0.08)' },
                    ticks : {
                        font     : { family: 'Nunito', size: 10 },
                        color    : '#7A6347',
                    },
                    border: { color: 'rgba(200, 146, 42, 0.2)' },
                },
            },
        },
    });
})();
</script>
@endpush

@endif
