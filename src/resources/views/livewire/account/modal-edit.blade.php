@if ($showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
            <h3 class="text-base font-bold mb-4" style="color:#DC143C;">
                Edit {{ ucfirst($editUserRole) }} Account
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Full Name</label>
                    <input type="text" wire:model="editName"
                           class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('editName') border-red-400 @else border-gray-200 @enderror">
                    @error('editName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center justify-between py-1">
                    <label class="text-sm font-medium text-gray-600">Active</label>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="editIsActive" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                            peer-checked:after:translate-x-full peer-checked:after:border-white
                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                            after:bg-white after:border-gray-300 after:border after:rounded-full
                            after:h-5 after:w-5 after:transition-all peer-checked:bg-[#DC143C]"></div>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">New Password (leave blank to keep)</label>
                    <input type="password" wire:model="editPassword"
                           class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('editPassword') border-red-400 @else border-gray-200 @enderror">
                    @error('editPassword') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Confirm Password</label>
                    <input type="password" wire:model="editConfirm"
                           class="w-full border-2 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#D4A017] @error('editConfirm') border-red-400 @else border-gray-200 @enderror">
                    @error('editConfirm') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex gap-2 mt-5">
                <button wire:click="$set('showEditModal',false)" type="button"
                        class="flex-1 py-2 rounded-lg border-2 text-sm font-semibold"
                        style="border-color:#D4A017;color:#D4A017;">Cancel</button>
                <button wire:click="updateUser" type="button"
                        class="flex-1 py-2 rounded-lg text-white text-sm font-semibold"
                        style="background-color:#DC143C;">
                    <span wire:loading.remove wire:target="updateUser">Save</span>
                    <span wire:loading wire:target="updateUser">Saving...</span>
                </button>
            </div>
        </div>
    </div>
@endif
