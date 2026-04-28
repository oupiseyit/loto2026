<x-app-layout>
    <x-slot:title>Setting — HT ភ្នាក់</x-slot:title>

    @php $user = auth()->user(); $isAdmin = $user->role === 'admin'; @endphp

    <div class="pt-14 min-h-screen pb-20 md:pb-0" style="background-color:#f5f5f5;">
        <div class="max-w-lg mx-auto px-4 py-6 space-y-6">

            <form method="POST" action="{{ route('setting.update') }}" class="space-y-6"
                  x-data="{ commissionMode: '{{ old('commission_mode', $setting->commission_mode ?? 'default') }}' }">
                @csrf
                @include('setting.printer-section')
                <button type="submit"
                        class="w-full py-3 rounded-xl text-white font-bold text-base shadow-md hover:opacity-80"
                        style="background-color:#D4A017;">
                    Save Settings
                </button>
            </form>

            @if ($isAdmin)
                @livewire('currency-manager')
            @endif

            @include('setting.about-section')

        </div>
    </div>
</x-app-layout>
