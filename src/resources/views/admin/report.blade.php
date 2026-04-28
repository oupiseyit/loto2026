<x-admin-layout>
    <x-slot:title>Report — HT ភ្នាក់ Admin</x-slot:title>
    <x-slot:pageTitle>Reports</x-slot:pageTitle>
    <x-slot:breadcrumb>Reports</x-slot:breadcrumb>

    @php
        $SESSION_LABEL = ['morning' => 'ព្រឹក', 'noon' => 'ថ្ងៃ', 'evening' => 'ល្ងាច'];
        $user          = auth()->user();
        $rowItems      = $rows instanceof \Illuminate\Pagination\LengthAwarePaginator
                            ? $rows->items()
                            : ($rows ?? []);
    @endphp

    {{-- Filter form in a card --}}
    <div class="card card-outline mb-3" style="border-top:3px solid #D4A017;">
        <div class="card-header" style="background-color:#D4A017;">
            <h3 class="card-title text-white font-weight-bold">
                <i class="fas fa-filter mr-1"></i> Filters
            </h3>
        </div>
        <div class="card-body py-3">
            @include('report.filter-form')
        </div>
    </div>

    {{-- Summary cards --}}
    @include('report.summary-cards')

    {{-- Data table in a card --}}
    <div class="card card-outline" style="border-top:3px solid #DC143C;">
        <div class="card-header" style="background-color:#DC143C;">
            <h3 class="card-title text-white font-weight-bold">
                <i class="fas fa-table mr-1"></i> Report Data
            </h3>
        </div>
        <div class="card-body p-0">
            @include('report.data-table')
        </div>
    </div>

</x-admin-layout>
