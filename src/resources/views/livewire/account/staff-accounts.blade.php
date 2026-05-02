<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <h2 class="text-base font-bold" style="color:#DC143C;">{{ __('staff') }}</h2>
            <div class="w-20 h-px" style="background-color:#DC143C33;"></div>
        </div>
        <button wire:click="openCreate('staff')" type="button"
                class="px-4 py-1.5 rounded-lg text-white text-sm font-semibold"
                style="background-color:#DC143C;">+ {{ __('add_staff') }}</button>
    </div>

    @if ($staff && count($staff) > 0)
        <div class="space-y-2">
            @foreach ($staff as $s)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                    <div>
                        <span class="font-semibold text-sm text-gray-800">{{ $s['name'] }}</span>
                        <span class="ml-2 text-xs text-gray-400">{{ '@' }}{{ $s['username'] }}</span>
                        @if (!($s['is_active'] ?? true))
                            <span class="ml-2 text-xs px-1.5 py-0.5 rounded bg-red-100 text-red-600">{{ __('inactive') }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">{{ number_format($s['today_amount'] ?? 0) }}</span>
                        <button wire:click="openEdit({{ $s['id'] }})" type="button"
                                class="text-xs px-2 py-1 rounded border font-medium hover:bg-gray-50"
                                style="border-color:#D4A017;color:#D4A017;">{{ __('edit') }}</button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-400 text-center py-4">{{ __('no_staff_accounts') }}</p>
    @endif
</div>
