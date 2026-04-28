<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <h2 class="text-base font-bold" style="color:#DC143C;">Master Accounts</h2>
            <div class="w-20 h-px" style="background-color:#DC143C33;"></div>
        </div>
        <button wire:click="openCreate('master')" type="button"
                class="px-4 py-1.5 rounded-lg text-white text-sm font-semibold"
                style="background-color:#DC143C;">+ Add Master</button>
    </div>

    @if ($masters && count($masters) > 0)
        <div class="space-y-2">
            @foreach ($masters as $m)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                    <div>
                        <span class="font-semibold text-sm text-gray-800">{{ $m['name'] }}</span>
                        <span class="ml-2 text-xs text-gray-400">{{ '@' }}{{ $m['username'] }}</span>
                        @if (!($m['is_active'] ?? true))
                            <span class="ml-2 text-xs px-1.5 py-0.5 rounded bg-red-100 text-red-600">Inactive</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">{{ $m['staff_count'] }} staff</span>
                        <button wire:click="openEdit({{ $m['id'] }})" type="button"
                                class="text-xs px-2 py-1 rounded border font-medium hover:bg-gray-50"
                                style="border-color:#D4A017;color:#D4A017;">Edit</button>
                        <button wire:click="deleteUser({{ $m['id'] }})"
                                wire:confirm="Delete master {{ $m['name'] }}? This will also affect their staff."
                                type="button"
                                class="text-xs px-2 py-1 rounded border font-medium hover:bg-red-50"
                                style="border-color:#DC143C;color:#DC143C;">Del</button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-400 text-center py-4">No master accounts yet.</p>
    @endif
</div>
