<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center gap-3 mb-4">
        <h2 class="text-base font-bold" style="color:#DC143C;">About</h2>
        <div class="flex-1 h-px" style="background-color:#DC143C33;"></div>
    </div>
    <div class="space-y-2 text-sm text-gray-600">
        <div class="flex justify-between"><span>App Name</span><span class="font-semibold text-gray-800">HT ភ្នាក់</span></div>
        <div class="flex justify-between"><span>Version</span><span class="font-semibold text-gray-800">1.0.0</span></div>
        <div class="flex justify-between"><span>Stack</span><span class="font-semibold text-gray-800">Laravel 13 + Livewire 3</span></div>
        <div class="flex justify-between">
            <span>Role</span>
            <span class="font-semibold capitalize" style="color:#DC143C;">{{ $user->role }}</span>
        </div>
    </div>
</div>
