<x-app-layout>
    <x-slot:title>Home — HT ភ្នាក់</x-slot:title>

    <div class="pt-12 md:pt-14 pb-16 md:pb-0 min-h-screen flex flex-col" style="background-color: var(--ht-bg);">

        {{-- Date banner --}}
        <div class="px-4 pt-3 pb-1">
            <p class="font-cormorant italic text-sm" style="color: var(--ht-text-muted);">
                {{ today()->translatedFormat('l, d F Y') }}
            </p>
        </div>

        @if(auth()->user()->isStaff())
            @livewire('bet-form', ['today' => $today])
        @else
            @include('home.master-stats')
            @include('home.admin-stats')
        @endif

    </div>
</x-app-layout>
