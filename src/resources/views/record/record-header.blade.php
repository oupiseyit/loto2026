{{-- Header --}}
<div class="px-3 md:px-4 py-2 md:py-3 border-b border-gray-200 flex items-center justify-between flex-shrink-0" style="background-color:#FFF8DC;">
    <div>
        <h1 class="text-sm md:text-base font-bold" style="color:#DC143C;">{{ __('records') }}</h1>
        <p class="text-xs text-gray-500 mt-0.5">
            {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('d M Y') : __('select_date') }}
        </p>
    </div>
    <div class="hidden md:block text-xs text-gray-500">
        {{ $user->name }}
        <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-white/60" style="color:#DC143C;">{{ $user->role }}</span>
    </div>
</div>

{{-- Session tabs --}}
<div class="flex border-b border-gray-200 flex-shrink-0 overflow-x-auto bg-white">
    @foreach ($TABS as $tab)
        @php $active = $tab['key'] === $activeTab; @endphp
        <a href="{{ route('record', ['date'=>$selectedDate,'tab'=>$tab['key']]) }}"
           class="px-4 py-2.5 text-sm font-semibold whitespace-nowrap border-b-2 transition-colors"
           style="{{ $active ? 'border-color:#DC143C;color:#DC143C;background-color:#FFF5F5;' : 'border-color:transparent;color:#6B7280;' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
