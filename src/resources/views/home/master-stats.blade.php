@if(auth()->user()->isMaster() && isset($staffStats) && $staffStats->isNotEmpty())
<div x-data="{ open: true }" class="bg-white border-b" style="border-color:#D4A017;">
    <button @click="open=!open" type="button"
            class="w-full flex items-center justify-between px-4 py-2 text-xs font-bold"
            style="color:#DC143C;">
        <span>Today's Staff Summary — {{ today()->format('d M Y') }}</span>
        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180':''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr style="background-color:#DC143C;color:#fff;">
                        <th class="px-4 py-2 text-left">Staff</th>
                        <th class="px-4 py-2 text-center">Tickets</th>
                        <th class="px-4 py-2 text-right">Amount (KHR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffStats as $i => $staff)
                    <tr style="background-color:{{ $i % 2 === 0 ? '#FFF8DC' : '#fff' }};">
                        <td class="px-4 py-2 font-medium" style="color:#DC143C;">{{ $staff->name }}</td>
                        <td class="px-4 py-2 text-center text-gray-700">{{ $staff->tickets_today }}</td>
                        <td class="px-4 py-2 text-right font-bold" style="color:#D4A017;">{{ number_format($staff->amount_today) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color:#D4A017;color:#fff;">
                        <td class="px-4 py-2 font-bold">Total</td>
                        <td class="px-4 py-2 text-center font-bold">{{ $staffStats->sum('tickets_today') }}</td>
                        <td class="px-4 py-2 text-right font-bold">{{ number_format($staffStats->sum('amount_today')) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif
