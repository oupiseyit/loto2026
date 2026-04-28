<x-app-layout>
    <x-slot:title>Home — HT ភ្នាក់</x-slot:title>

    <div class="pt-12 md:pt-14 pb-16 md:pb-0 min-h-screen flex flex-col" style="background-color:#f5f5f5;">

        @include('home.master-stats')
        @include('home.admin-stats')

        @if(auth()->user()->isStaff())
            @livewire('bet-form', ['today' => $today])
        @endif

    </div>
</x-app-layout>
