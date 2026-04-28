<div>
    {{-- Currency Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <h2 class="text-base font-bold" style="color:#DC143C;">Currency</h2>
                <div class="w-20 h-px" style="background-color:#DC143C33;"></div>
            </div>
            <button wire:click="openCreate" type="button"
                    class="px-4 py-1.5 rounded-lg text-white text-sm font-semibold"
                    style="background-color:#DC143C;">+ Add</button>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr style="background-color:#DC143C;color:#fff;">
                    <th class="px-3 py-2 text-left rounded-tl-lg">Country</th>
                    <th class="px-3 py-2 text-left">Name</th>
                    <th class="px-3 py-2 text-left">Symbol</th>
                    <th class="px-3 py-2 text-center">Status</th>
                    <th class="px-3 py-2 text-right rounded-tr-lg">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($currencies as $i => $currency)
                    <tr style="background-color:{{ $i % 2 === 0 ? '#FFF8DC' : '#fff' }};">
                        <td class="px-3 py-2 text-gray-500">{{ $currency->country_name }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $currency->name }}</td>
                        <td class="px-3 py-2 font-bold" style="color:#D4A017;">{{ $currency->symbol }}</td>
                        <td class="px-3 py-2 text-center">
                            <button wire:click="toggleActive({{ $currency->id }})" type="button"
                                    class="relative inline-flex items-center h-6 w-11 rounded-full transition-colors focus:outline-none"
                                    style="background-color:{{ $currency->is_active ? '#DC143C' : '#d1d5db' }};">
                                <span class="inline-block w-5 h-5 bg-white rounded-full shadow transform transition-transform
                                    {{ $currency->is_active ? 'translate-x-5' : 'translate-x-1' }}"></span>
                            </button>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <div class="flex justify-end gap-2">
                                <button wire:click="openEdit({{ $currency->id }})" type="button"
                                        class="text-xs px-2 py-1 rounded border font-medium hover:bg-gray-50"
                                        style="border-color:#D4A017;color:#D4A017;">Edit</button>
                                <button wire:click="delete({{ $currency->id }})"
                                        wire:confirm="Delete {{ $currency->name }}?"
                                        type="button"
                                        class="text-xs px-2 py-1 rounded border font-medium hover:bg-red-50"
                                        style="border-color:#DC143C;color:#DC143C;">Del</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-sm text-gray-400">No currencies yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create Modal --}}
    @if ($showCreate)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
                <h3 class="text-base font-bold mb-4" style="color:#DC143C;">Add Currency</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Country Name</label>
                        <input type="text" wire:model="newCountryName" placeholder="e.g. Cambodia"
                               class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('newCountryName') border-red-400 @else border-gray-200 @enderror">
                        @error('newCountryName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Currency Name</label>
                        <input type="text" wire:model="newName" placeholder="e.g. KHR"
                               class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('newName') border-red-400 @else border-gray-200 @enderror">
                        @error('newName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Symbol</label>
                        <input type="text" wire:model="newSymbol" placeholder="e.g. ៛"
                               class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('newSymbol') border-red-400 @else border-gray-200 @enderror">
                        @error('newSymbol') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex gap-2 mt-5">
                    <button wire:click="$set('showCreate',false)" type="button"
                            class="flex-1 py-2 rounded-lg border-2 text-sm font-semibold"
                            style="border-color:#D4A017;color:#D4A017;">Cancel</button>
                    <button wire:click="store" type="button"
                            class="flex-1 py-2 rounded-lg text-white text-sm font-semibold"
                            style="background-color:#DC143C;">
                        <span wire:loading.remove wire:target="store">Create</span>
                        <span wire:loading wire:target="store">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Modal --}}
    @if ($showEdit)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
                <h3 class="text-base font-bold mb-4" style="color:#DC143C;">
                    Edit Currency — <span class="uppercase">{{ $editLocale }}</span>
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Country Name</label>
                        <input type="text" wire:model="editCountryName"
                               class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('editCountryName') border-red-400 @else border-gray-200 @enderror">
                        @error('editCountryName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Currency Name</label>
                        <input type="text" wire:model="editName"
                               class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('editName') border-red-400 @else border-gray-200 @enderror">
                        @error('editName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Symbol</label>
                        <input type="text" wire:model="editSymbol"
                               class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('editSymbol') border-red-400 @else border-gray-200 @enderror">
                        @error('editSymbol') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex gap-2 mt-5">
                    <button wire:click="$set('showEdit',false)" type="button"
                            class="flex-1 py-2 rounded-lg border-2 text-sm font-semibold"
                            style="border-color:#D4A017;color:#D4A017;">Cancel</button>
                    <button wire:click="update" type="button"
                            class="flex-1 py-2 rounded-lg text-white text-sm font-semibold"
                            style="background-color:#DC143C;">
                        <span wire:loading.remove wire:target="update">Save</span>
                        <span wire:loading wire:target="update">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
