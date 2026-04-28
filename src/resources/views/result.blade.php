<x-app-layout>
    <x-slot:title>Result — HT ភ្នាក់</x-slot:title>

    <div class="pt-12 md:pt-14 pb-16 md:pb-0" style="height:100vh;overflow:hidden;">
        @livewire('result-page', [
            'selectedDate'    => $filters['date'],
            'selectedSession' => $filters['session'],
            'grid'            => $grid->toArray(),
            'canEdit'         => $canEdit ?? false,
        ])
    </div>
</x-app-layout>
