<x-app-layout>
    <x-slot:title>{{ __('record') }} — HT ភ្នាក់</x-slot:title>

    @php
        $TABS = [
            ['key'=>'all',     'label'=> __('all_records')],
            ...array_map(fn ($t) => ['key' => $t, 'label' => $t], \App\Models\BetTimeSetting::lotteryTypes()),
            ['key'=>'winning', 'label'=> __('winning')],
        ];
        $TYPE_COLOR = ['KH5D' => ['#FFF3CD', '#856404'], 'VN5D' => ['#D1ECF1', '#0C5460'], 'VIP5D' => ['#F8D7DA', '#721C24']];
        $SESSION_BADGE = \App\Models\BetTimeSetting::all()->mapWithKeys(fn ($s) => [$s->session_key => [
            'label' => \App\Models\BetTimeSetting::label($s->session_key),
            'bg'    => ($TYPE_COLOR[$s->lottery_type] ?? ['#EEE', '#555'])[0],
            'text'  => ($TYPE_COLOR[$s->lottery_type] ?? ['#EEE', '#555'])[1],
        ]])->all();
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
