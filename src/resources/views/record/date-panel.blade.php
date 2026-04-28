{{-- Mobile: horizontal date scroller --}}
<div class="flex md:hidden border-b border-gray-200 flex-shrink-0 overflow-x-auto" style="background-color:#FFF8DC;">
    @forelse ($dates as $d)
        @php $sel = $d === $selectedDate; @endphp
        <a href="{{ route('record', ['date'=>$d,'tab'=>$activeTab]) }}"
           class="flex-shrink-0 px-3 py-2 text-xs font-medium whitespace-nowrap border-b-2 transition-colors"
           style="{{ $sel ? 'border-color:#D4A017;background-color:#D4A017;color:#fff;' : 'border-color:transparent;color:#374151;' }}">
            {{ \Carbon\Carbon::parse($d)->format('d M Y') }}
        </a>
    @empty
        <p class="p-2 text-xs text-gray-400">{{ __('no_dates') }}</p>
    @endforelse
</div>

{{-- Desktop: left panel date list --}}
<aside class="hidden md:flex w-40 flex-shrink-0 border-r border-gray-200 flex-col" style="background-color:#FFF8DC;">
    <div class="px-3 py-2 border-b border-gray-200">
        <h2 class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('dates') }}</h2>
    </div>
    <div class="flex-1 overflow-y-auto">
        @forelse ($dates as $d)
            @php $sel = $d === $selectedDate; @endphp
            <a href="{{ route('record', ['date'=>$d,'tab'=>$activeTab]) }}"
               class="block w-full text-left px-3 py-2.5 text-sm font-medium border-b border-gray-100 transition-colors"
               style="{{ $sel ? 'background-color:#D4A017;color:#fff;' : 'color:#374151;' }}">
                {{ \Carbon\Carbon::parse($d)->format('d M Y') }}
            </a>
        @empty
            <p class="p-3 text-xs text-gray-400">{{ __('no_dates') }}</p>
        @endforelse
    </div>
</aside>
