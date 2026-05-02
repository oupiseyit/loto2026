<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center gap-3 mb-4">
        <h2 class="text-base font-bold" style="color:#DC143C;">{{ __('today_sales') }}</h2>
        <div class="flex-1 h-px" style="background-color:#DC143C33;"></div>
    </div>
    <div class="flex flex-wrap gap-4">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Total</p>
            <p class="text-2xl font-black" style="color:#D4A017;">{{ number_format($todaySales) }}</p>
        </div>
        @foreach (['morning' => 'ព្រឹក', 'noon' => 'ថ្ងៃ', 'evening' => 'ល្ងាច'] as $session => $label)
            @if (isset($breakdown[$session]))
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">{{ $label }}</p>
                    <p class="text-lg font-bold" style="color:#B8860B;">
                        {{ number_format($breakdown[$session]['amount'] ?? 0) }}
                        <span class="text-xs text-gray-400">({{ $breakdown[$session]['count'] ?? 0 }})</span>
                    </p>
                </div>
            @endif
        @endforeach
    </div>
</div>
