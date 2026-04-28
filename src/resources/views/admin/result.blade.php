<x-admin-layout>
    <x-slot:title>Results — HT ភ្នាក់ Admin</x-slot:title>
    <x-slot:pageTitle>Results</x-slot:pageTitle>
    <x-slot:breadcrumb>Results</x-slot:breadcrumb>

    <div class="card p-0" style="min-height:60vh;">
        <div class="card-body p-0">
            @livewire('result-page', [
                'selectedDate'    => $filters['date'],
                'selectedSession' => $filters['session'],
                'grid'            => $grid->toArray(),
                'canEdit'         => $canEdit,
            ])
        </div>
    </div>

</x-admin-layout>
