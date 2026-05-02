<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center gap-3 mb-4">
        <h2 class="text-base font-bold" style="color:#DC143C;">{{ __('change_password') }}</h2>
        <div class="flex-1 h-px" style="background-color:#DC143C33;"></div>
    </div>
    <div class="space-y-3">
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('current_password') }}</label>
            <input type="password" wire:model="currentPassword"
                   class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('currentPassword') border-red-400 @else border-gray-200 @enderror">
            @error('currentPassword') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('new_password') }}</label>
            <input type="password" wire:model="newPassword"
                   class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('newPassword') border-red-400 @else border-gray-200 @enderror">
            @error('newPassword') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('confirm_password') }}</label>
            <input type="password" wire:model="confirmPassword"
                   class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('confirmPassword') border-red-400 @else border-gray-200 @enderror">
            @error('confirmPassword') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <button wire:click="changePassword" type="button"
                class="w-full py-2.5 rounded-xl text-white font-bold text-sm"
                style="background-color:#D4A017;">
            <span wire:loading.remove wire:target="changePassword">{{ __('change_password') }}</span>
            <span wire:loading wire:target="changePassword">{{ __('saving') }}</span>
        </button>
    </div>
</div>
