@if(auth()->user()->isAdmin() && isset($masterStats) && $masterStats->isNotEmpty())

{{-- Summary table --}}
<div x-data="{ open: true }" class="bg-white border-b" style="border-color:#D4A017;">
    <button @click="open=!open" type="button"
            class="w-full flex items-center justify-between px-4 py-2 text-xs font-bold"
            style="color:#DC143C;">
        <span>Today's Master Summary — {{ today()->format('d M Y') }}</span>
        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180':''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr style="background-color:#DC143C;color:#fff;">
                        <th class="px-4 py-2 text-left">Master</th>
                        <th class="px-4 py-2 text-center">Staff</th>
                        <th class="px-4 py-2 text-center">Tickets</th>
                        <th class="px-4 py-2 text-right">Amount (KHR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($masterStats as $i => $master)
                    <tr style="background-color:{{ $i % 2 === 0 ? '#FFF8DC' : '#fff' }};">
                        <td class="px-4 py-2 font-medium" style="color:#DC143C;">{{ $master->name }}</td>
                        <td class="px-4 py-2 text-center text-gray-700">{{ $master->staff_count }}</td>
                        <td class="px-4 py-2 text-center text-gray-700">{{ $master->tickets_today }}</td>
                        <td class="px-4 py-2 text-right font-bold" style="color:#D4A017;">{{ number_format($master->amount_today) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color:#D4A017;color:#fff;">
                        <td class="px-4 py-2 font-bold">Total</td>
                        <td class="px-4 py-2 text-center font-bold">{{ $masterStats->sum('staff_count') }}</td>
                        <td class="px-4 py-2 text-center font-bold">{{ $masterStats->sum('tickets_today') }}</td>
                        <td class="px-4 py-2 text-right font-bold">{{ number_format($masterStats->sum('amount_today')) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- 7-day line chart --}}
<div class="bg-white border-b px-4 py-3" style="border-color:#D4A017;">
    <p class="text-xs font-bold mb-2" style="color:#DC143C;">Staff Bet Total — Last 7 Days</p>
    <canvas id="masterChart" height="100"></canvas>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const COLORS = [
        '#DC143C','#D4A017','#2563eb','#16a34a',
        '#9333ea','#ea580c','#0891b2','#db2777',
    ];

    const labels  = @json($chartLabels);
    const masters = @json($masterStats->map(fn($m) => ['name' => $m->name, 'data' => $m->chart_data])->values());

    const datasets = masters.map((m, i) => ({
        label           : m.name,
        data            : m.data,
        borderColor     : COLORS[i % COLORS.length],
        backgroundColor : COLORS[i % COLORS.length] + '22',
        borderWidth     : 2,
        pointRadius     : 4,
        tension         : 0.3,
        fill            : true,
    }));

    new Chart(document.getElementById('masterChart'), {
        type : 'line',
        data : { labels, datasets },
        options : {
            responsive : true,
            plugins : {
                legend  : { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip : {
                    callbacks : {
                        label : ctx => ' ' + ctx.dataset.label + ': ' + Number(ctx.parsed.y).toLocaleString() + ' KHR',
                    },
                },
            },
            scales : {
                y : {
                    beginAtZero : true,
                    ticks : {
                        font     : { size: 10 },
                        callback : v => v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v,
                    },
                },
                x : { ticks : { font : { size: 10 } } },
            },
        },
    });
})();
</script>
@endpush

@endif
