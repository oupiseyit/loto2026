<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <h2 class="text-base font-bold" style="color:#DC143C;">{{ __('bet_time_settings') }}</h2>
            <div class="w-20 h-px" style="background-color:#DC143C33;"></div>
        </div>
        <button wire:click="openCreate" type="button"
                class="px-4 py-1.5 rounded-lg text-white text-sm font-semibold"
                style="background-color:#DC143C;">+ {{ __('add_session') }}</button>
    </div>

    @if (session('error'))
        <div class="mb-3 px-4 py-2 rounded-lg text-sm text-white bg-red-500">
            {{ session('error') }}
        </div>
    @endif

    {{-- Sessions table --}}
    @if ($sessions->isEmpty())
        <p class="text-sm text-gray-400 text-center py-4">{{ __('no_sessions') }}</p>
    @else
        <div class="overflow-x-auto -mx-1">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-white" style="background-color:#DC143C;">
                        <th class="px-3 py-2 text-left font-semibold">{{ __('session_name') }}</th>
                        <th class="px-3 py-2 text-center font-semibold">{{ __('result_time') }}</th>
                        <th class="px-3 py-2 text-left font-semibold">{{ __('type') }}</th>
                        <th class="px-3 py-2 text-center font-semibold">{{ __('cutoff_group1') }}</th>
                        <th class="px-3 py-2 text-center font-semibold">{{ __('cutoff_group2') }}</th>
                        <th class="px-3 py-2 text-center font-semibold">{{ __('status') }}</th>
                        <th class="px-3 py-2 text-center font-semibold"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sessions as $s)
                        <tr class="border-b border-gray-100 last:border-0 hover:bg-yellow-50">
                            <td class="px-3 py-2">
                                <span class="font-semibold text-gray-800">{{ $s->session_name }}</span>
                                <span class="ml-1 text-gray-400">{{ $s->session_key }}</span>
                            </td>
                            <td class="px-3 py-2 text-center font-mono" style="color:#DC143C;">
                                {{ substr($s->result_time, 0, 5) }}
                            </td>
                            <td class="px-3 py-2">
                                @if (!empty($s->group_type))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($s->group_type as $gt)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold"
                                                  style="background-color:#fee2e2;color:#dc2626;">{{ $gt }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center">
                                <span class="font-mono text-gray-700">{{ substr($s->group1_cutoff, 0, 5) }}</span>
                                <span class="ml-1 text-gray-400">({{ implode(',', $s->group1_types) }})</span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <span class="font-mono text-gray-700">{{ substr($s->group2_cutoff, 0, 5) }}</span>
                                <span class="ml-1 text-gray-400">({{ implode(',', $s->group2_types) }})</span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button wire:click="toggleActive({{ $s->id }})" type="button"
                                        class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                        style="{{ $s->is_active ? 'background-color:#d1fae5;color:#065f46;' : 'background-color:#fee2e2;color:#991b1b;' }}">
                                    {{ $s->is_active ? __('session_open') : __('inactive') }}
                                </button>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openEdit({{ $s->id }})" type="button"
                                            class="text-xs px-2 py-1 rounded border font-medium hover:bg-gray-50"
                                            style="border-color:#D4A017;color:#D4A017;">{{ __('edit') }}</button>
                                    <button wire:click="delete({{ $s->id }})"
                                            wire:confirm="Delete session '{{ $s->session_key }}'?"
                                            type="button"
                                            class="text-xs px-2 py-1 rounded border font-medium hover:bg-red-50"
                                            style="border-color:#DC143C;color:#DC143C;">Del</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Add / Edit modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md flex flex-col" style="max-height:90vh;">

                {{-- Fixed header --}}
                <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b border-gray-100 flex-shrink-0">
                    <h3 class="text-base font-bold" style="color:#DC143C;">
                        {{ $isEditing ? __('edit') . ' Session' : __('add_session') }}
                    </h3>
                    <button wire:click="$set('showModal', false)" type="button"
                            class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                </div>

                {{-- Scrollable body --}}
                <div class="overflow-y-auto flex-1 px-6 py-4 space-y-3">

                    @if (!$isEditing)
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('session_key') }} <span class="text-red-500">*</span></label>
                            <input wire:model="session_key" type="text" placeholder="e.g. morning"
                                   class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('session_key') border-red-400 @else border-gray-200 @enderror">
                            @error('session_key') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('session_name') }} <span class="text-red-500">*</span></label>
                        <input wire:model="session_name" type="text" placeholder="e.g. Morning"
                               class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('session_name') border-red-400 @else border-gray-200 @enderror">
                        @error('session_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('result_time') }} <span class="text-red-500">*</span></label>
                        <input wire:model="result_time" type="time"
                               class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('result_time') border-red-400 @else border-gray-200 @enderror">
                        @error('result_time') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Group Type --}}
                    <div class="p-3 rounded-lg border border-gray-200">
                        <p class="text-xs font-semibold text-gray-700 mb-2">{{ __('type') }}</p>
                        <div class="flex flex-wrap gap-x-3 gap-y-1.5">
                            @foreach ($betCategories->groupBy('type') as $type => $cats)
                                <div class="w-full">
                                    <span class="text-[10px] font-bold uppercase tracking-wide px-1.5 py-0.5 rounded"
                                          style="{{ $type == 1 ? 'background:#fee2e2;color:#dc2626;' : 'background:#fef9c3;color:#ca8a04;' }}">
                                        {{ $type == 1 ? 'P' : 'LO' }}
                                    </span>
                                </div>
                                @foreach ($cats as $cat)
                                    <label class="flex items-center gap-1 text-xs cursor-pointer select-none">
                                        <input type="checkbox" wire:model="group_type" value="{{ $cat->name }}"
                                               class="accent-[#DC143C]">
                                        <span class="font-medium">{{ $cat->name }}</span>
                                    </label>
                                @endforeach
                            @endforeach
                        </div>
                        @error('group_type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Group 1 --}}
                    <div class="p-3 rounded-lg border border-gray-200">
                        <p class="text-xs font-semibold text-gray-700 mb-2">{{ __('cutoff_group1') }} <span class="text-red-500">*</span></p>
                        <div class="flex flex-wrap gap-x-3 gap-y-1.5 mb-2">
                            @foreach ($betCategories->groupBy('type') as $type => $cats)
                                <div class="w-full">
                                    <span class="text-[10px] font-bold uppercase tracking-wide px-1.5 py-0.5 rounded"
                                          style="{{ $type == 1 ? 'background:#fee2e2;color:#dc2626;' : 'background:#fef9c3;color:#ca8a04;' }}">
                                        {{ $type == 1 ? 'P' : 'LO' }}
                                    </span>
                                </div>
                                @foreach ($cats as $cat)
                                    <label class="flex items-center gap-1 text-xs cursor-pointer select-none">
                                        <input type="checkbox" wire:model="group1_types" value="{{ $cat->name }}"
                                               class="accent-[#DC143C]">
                                        <span class="font-medium">{{ $cat->name }}</span>
                                    </label>
                                @endforeach
                            @endforeach
                        </div>
                        @error('group1_types') <p class="text-xs text-red-500 mb-1">{{ $message }}</p> @enderror
                        <input wire:model="group1_cutoff" type="time"
                               class="w-full border-2 rounded-lg px-3 py-1.5 text-sm outline-none focus:border-[#D4A017] @error('group1_cutoff') border-red-400 @else border-gray-200 @enderror">
                        @error('group1_cutoff') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Group 2 --}}
                    <div class="p-3 rounded-lg border border-gray-200">
                        <p class="text-xs font-semibold text-gray-700 mb-2">{{ __('cutoff_group2') }} <span class="text-red-500">*</span></p>
                        <div class="flex flex-wrap gap-x-3 gap-y-1.5 mb-2">
                            @foreach ($betCategories->groupBy('type') as $type => $cats)
                                <div class="w-full">
                                    <span class="text-[10px] font-bold uppercase tracking-wide px-1.5 py-0.5 rounded"
                                          style="{{ $type == 1 ? 'background:#fee2e2;color:#dc2626;' : 'background:#fef9c3;color:#ca8a04;' }}">
                                        {{ $type == 1 ? 'P' : 'LO' }}
                                    </span>
                                </div>
                                @foreach ($cats as $cat)
                                    <label class="flex items-center gap-1 text-xs cursor-pointer select-none">
                                        <input type="checkbox" wire:model="group2_types" value="{{ $cat->name }}"
                                               class="accent-[#DC143C]">
                                        <span class="font-medium">{{ $cat->name }}</span>
                                    </label>
                                @endforeach
                            @endforeach
                        </div>
                        @error('group2_types') <p class="text-xs text-red-500 mb-1">{{ $message }}</p> @enderror
                        <input wire:model="group2_cutoff" type="time"
                               class="w-full border-2 rounded-lg px-3 py-1.5 text-sm outline-none focus:border-[#D4A017] @error('group2_cutoff') border-red-400 @else border-gray-200 @enderror">
                        @error('group2_cutoff') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="is_active" id="is_active_modal" class="accent-[#DC143C]">
                        <label for="is_active_modal" class="text-sm text-gray-700">Active</label>
                    </div>

                </div>

                {{-- Sticky footer --}}
                <div class="flex gap-2 px-6 py-4 border-t border-gray-100 flex-shrink-0">
                    <button wire:click="save" type="button"
                            class="flex-1 py-2.5 rounded-xl text-white font-bold text-sm"
                            style="background-color:#DC143C;">
                        <span wire:loading.remove wire:target="save">{{ __('update') }}</span>
                        <span wire:loading wire:target="save">{{ __('saving') }}</span>
                    </button>
                    <button wire:click="$set('showModal', false)" type="button"
                            class="flex-1 py-2.5 rounded-xl font-bold text-sm border-2"
                            style="border-color:#D4A017;color:#D4A017;">{{ __('clear') }}</button>
                </div>

            </div>
        </div>
    @endif
</div>
