<footer class="flex-shrink-0 border-t-2 border-gray-200 px-3 md:px-4 py-2 md:py-3 flex flex-wrap items-center gap-3 md:gap-6"
        style="background-color:#FFF8DC;">
    <div class="text-xs md:text-sm">
        <span class="text-gray-500">{{ __('total_record') }}: </span>
        <span class="font-bold" style="color:#DC143C;">{{ $totals->total_count ?? 0 }}</span>
    </div>
    <div class="text-xs md:text-sm">
        <span class="text-gray-500">{{ __('total_amount') }}: </span>
        <span class="font-bold text-gray-800">{{ number_format($totals->total_amount ?? 0) }}</span>
    </div>
    <div class="text-xs md:text-sm">
        <span class="text-gray-500">{{ __('win') }}: </span>
        <span class="font-bold" style="color:{{ ($totals->total_win ?? 0) > 0 ? '#16A34A' : '#6B7280' }};">
            {{ number_format($totals->total_win ?? 0) }}
        </span>
    </div>
</footer>
