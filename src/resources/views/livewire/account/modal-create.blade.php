@if ($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
            <h3 class="text-base font-bold mb-4" style="color:#DC143C;">
                Create {{ ucfirst($newRole) }} Account
            </h3>
            <div class="space-y-3">
                @if ($newRole === 'staff')
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Full Name</label>
                        <input type="text" wire:model="newName"
                               class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('newName') border-red-400 @else border-gray-200 @enderror">
                        @error('newName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Username</label>
                    <input type="text" wire:model="newUsername"
                           class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('newUsername') border-red-400 @else border-gray-200 @enderror">
                    @error('newUsername') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Password</label>
                    <input type="password" wire:model="newStaffPass"
                           class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('newStaffPass') border-red-400 @else border-gray-200 @enderror">
                    @error('newStaffPass') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Confirm Password</label>
                    <input type="password" wire:model="newStaffConfirm"
                           class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('newStaffConfirm') border-red-400 @else border-gray-200 @enderror">
                    @error('newStaffConfirm') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                @if ($newRole === 'master')
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Language</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['en' => '🇺🇸 English', 'km' => '🇰🇭 ខ្មែរ', 'vi' => '🇻🇳 Tiếng Việt', 'th' => '🇹🇭 ภาษาไทย'] as $code => $label)
                                <label class="flex items-center gap-2 px-3 py-2 rounded-lg border-2 cursor-pointer text-sm transition-all
                                    {{ $newLocale === $code ? 'border-[#DC143C] bg-red-50' : 'border-gray-200' }}">
                                    <input type="radio" wire:model="newLocale" value="{{ $code }}" class="accent-[#DC143C]">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Currency</label>
                        <select wire:model="newCurrencyId"
                                class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] border-gray-200 bg-white">
                            @foreach ($currencies as $currency)
                                <option value="{{ $currency->id }}">
                                    {{ $currency->symbol }} {{ $currency->name }} ({{ $currency->country_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
            <div class="flex gap-2 mt-5">
                <button wire:click="$set('showCreateModal',false)" type="button"
                        class="flex-1 py-2 rounded-lg border-2 text-sm font-semibold"
                        style="border-color:#D4A017;color:#D4A017;">Cancel</button>
                <button wire:click="createUser" type="button"
                        class="flex-1 py-2 rounded-lg text-white text-sm font-semibold"
                        style="background-color:#DC143C;">
                    <span wire:loading.remove wire:target="createUser">Create</span>
                    <span wire:loading wire:target="createUser">Creating...</span>
                </button>
            </div>
        </div>
    </div>
@endif
