<x-admin-layout>
    <x-slot:title>Records — HT ភ្នាក់ Admin</x-slot:title>
    <x-slot:pageTitle>Records</x-slot:pageTitle>
    <x-slot:breadcrumb>Records</x-slot:breadcrumb>

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

    <div class="card p-0" style="min-height:70vh;">
        <div class="card-body p-0">
            <div class="d-flex" style="min-height:70vh;">

                {{-- Date panel (sidebar) --}}
                <div class="flex-shrink-0 border-right" style="width:160px;background-color:#FFF8DC;overflow-y:auto;max-height:80vh;">
                    <div class="px-3 py-2 border-bottom">
                        <h6 class="mb-0 font-weight-bold text-uppercase text-muted" style="font-size:.7rem;letter-spacing:.05em;">
                            {{ __('dates') }}
                        </h6>
                    </div>
                    @forelse ($dates as $d)
                        @php $sel = $d === $selectedDate; @endphp
                        <a href="{{ route('record', ['date'=>$d,'tab'=>$activeTab]) }}"
                           class="d-block px-3 py-2 border-bottom text-decoration-none"
                           style="font-size:.8rem;font-weight:500;{{ $sel ? 'background-color:#D4A017;color:#fff;' : 'color:#374151;' }}">
                            {{ \Carbon\Carbon::parse($d)->format('d M Y') }}
                        </a>
                    @empty
                        <p class="p-3 text-muted" style="font-size:.75rem;">{{ __('no_dates') }}</p>
                    @endforelse
                </div>

                {{-- Main content --}}
                <div class="flex-grow-1 d-flex flex-column overflow-hidden">

                    {{-- Record header --}}
                    <div class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between flex-shrink-0"
                         style="background-color:#FFF8DC;">
                        <div>
                            <h6 class="mb-0 font-weight-bold" style="color:#DC143C;">{{ __('records') }}</h6>
                            <small class="text-muted">
                                {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('d M Y') : __('select_date') }}
                            </small>
                        </div>
                        <span class="badge px-2 py-1" style="background-color:#DC143C;color:#fff;font-size:.75rem;">
                            {{ strtoupper($user->role) }}
                        </span>
                    </div>

                    {{-- Session tabs --}}
                    <div class="d-flex border-bottom bg-white flex-shrink-0" style="overflow-x:auto;">
                        @foreach ($TABS as $tab)
                            @php $active = $tab['key'] === $activeTab; @endphp
                            <a href="{{ route('record', ['date'=>$selectedDate,'tab'=>$tab['key']]) }}"
                               class="px-3 py-2 text-decoration-none flex-shrink-0"
                               style="font-size:.85rem;font-weight:600;white-space:nowrap;border-bottom:2px solid {{ $active ? '#DC143C' : 'transparent' }};color:{{ $active ? '#DC143C' : '#6B7280' }};{{ $active ? 'background-color:#FFF5F5;' : '' }}">
                                {{ $tab['label'] }}
                            </a>
                        @endforeach
                    </div>

                    {{-- Tickets table --}}
                    @include('record.tickets-table')

                    {{-- Footer totals --}}
                    @include('record.footer-totals')

                </div>
            </div>
        </div>
    </div>

</x-admin-layout>
