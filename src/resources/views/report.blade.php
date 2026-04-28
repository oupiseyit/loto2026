<x-app-layout>
    <x-slot:title>Report — HT ភ្នាក់</x-slot:title>

    @php
        $SESSION_LABEL = ['morning' => 'ព្រឹក', 'noon' => 'ថ្ងៃ', 'evening' => 'ល្ងាច'];
        $user          = auth()->user();
        $rowItems      = $rows instanceof \Illuminate\Pagination\LengthAwarePaginator
                            ? $rows->items()
                            : ($rows ?? []);
    @endphp

    <div class="pt-12 md:pt-14 pb-20 md:pb-0 min-h-screen" style="background-color:#f5f5f5;">
        <div class="max-w-5xl mx-auto px-3 md:px-4 py-4 space-y-4">

            @include('report.filter-form')
            @include('report.summary-cards')
            @include('report.data-table')

        </div>
    </div>
</x-app-layout>
