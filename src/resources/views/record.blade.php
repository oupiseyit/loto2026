<x-app-layout>
    <x-slot:title>{{ __('record') }} — HT ភ្នាក់</x-slot:title>

    @php
        $TABS = [
            ['key'=>'all',     'label'=> __('all_records')],
            ['key'=>'morning', 'label'=> __('morning')],
            ['key'=>'noon',    'label'=> __('noon')],
            ['key'=>'evening', 'label'=> __('evening')],
            ['key'=>'winning', 'label'=> __('winning')],
        ];
        $SESSION_BADGE = [
            'morning' => ['label'=> __('morning'), 'bg'=>'#FFF3CD', 'text'=>'#856404'],
            'noon'    => ['label'=> __('noon'),    'bg'=>'#D1ECF1', 'text'=>'#0C5460'],
            'evening' => ['label'=> __('evening'), 'bg'=>'#D6EAF8', 'text'=>'#154360'],
        ];
        $statusMap = [
            'pending'   => ['bg'=>'#FFF3CD','text'=>'#856404','label'=> __('pending')],
            'completed' => ['bg'=>'#D4EDDA','text'=>'#155724','label'=> __('completed')],
            'cancelled' => ['bg'=>'#F8D7DA','text'=>'#721C24','label'=> __('cancelled')],
        ];
        $user         = auth()->user();
        $selectedDate = $filters['date'] ?? '';
        $activeTab    = $filters['tab']  ?? 'all';
        $rows         = $tickets instanceof \Illuminate\Pagination\LengthAwarePaginator
                            ? $tickets->items()
                            : ($tickets ?? []);
    @endphp

    <div class="pt-12 md:pt-14 pb-16 md:pb-0 flex flex-col md:flex-row h-screen overflow-hidden" style="background-color:#f5f5f5;">

        @include('record.date-panel')

        <main class="flex-1 flex flex-col overflow-hidden">
            @include('record.record-header')
            @include('record.tickets-table')
            @include('record.footer-totals')
        </main>

    </div>
</x-app-layout>
