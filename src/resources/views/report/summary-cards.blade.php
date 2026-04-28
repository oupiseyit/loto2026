<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    @foreach ([
        ['Tickets', $totals->total_tickets ?? 0, false],
        ['Amount',  $totals->total_amount  ?? 0, false],
        ['Win',     $totals->total_win     ?? 0, true],
        ['Net',     $totals->net           ?? 0, false],
    ] as [$lbl, $val, $accent])
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $lbl }}</p>
            <p class="text-2xl font-black" style="color:{{ $accent ? '#DC143C' : '#B8860B' }};">
                {{ number_format($val) }}
            </p>
        </div>
    @endforeach
</div>
