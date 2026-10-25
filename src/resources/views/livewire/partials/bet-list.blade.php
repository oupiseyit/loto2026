{{-- Bet list table: ប៉ុស្តិ៍ | លេខ | តម្លៃ | សរុប | លុប --}}
@php $cols = $compact ? 'grid-template-columns:1fr 1.4fr 1fr 1fr 1.8rem;' : 'grid-template-columns:1fr 1.6fr 1fr 1fr 2.5rem;'; @endphp
<div class="grid text-center font-bold text-white {{ $compact ? 'text-[10px] py-1' : 'text-xs py-1.5' }}" style="background-color:#2A5F47;{{ $cols }}">
    <span>{{ __('post') }}</span><span>{{ __('number') }}</span><span>{{ __('price') }}</span><span>{{ __('total') }}</span><span>{{ __('delete') }}</span>
</div>
<div class="{{ $compact ? '' : 'flex-1 overflow-y-auto' }}">
    @forelse ($bets as $idx => $bet)
        <div wire:key="{{ $compact ? 'm' : 'd' }}-{{ $idx }}-{{ $bet['ids'][0] ?? '' }}"
             class="grid text-center border-b items-center {{ $compact ? 'text-xs py-1' : 'text-sm py-1.5' }}"
             style="background-color:{{ $idx % 2 === 0 ? '#FAF7F2' : '#fff' }};border-color:#e9e4dc;{{ $cols }}">
            <span class="font-bold" style="color:#2A5F47;">{{ $bet['letter'] }}</span>
            <span class="font-bold {{ $compact ? '' : 'font-cormorant text-lg' }}" style="color:#2C2826;">{{ $bet['number'] }}</span>
            <span style="color:#7A6E64;">{{ number_format($bet['amount']) }}{{ $bet['count'] > 1 ? ' ×'.$bet['count'] : '' }}</span>
            <span class="font-bold" style="color:#D4A017;">{{ number_format($bet['amount'] * $bet['count']) }}</span>
            <span class="flex items-center justify-center">
                <button type="button" @click="confirmDelete({{ $idx }})"
                        class="rounded-full flex items-center justify-center font-bold {{ $compact ? 'w-4 h-4 text-[8px]' : 'w-6 h-6 text-xs' }}"
                        style="background-color:#9B2335;color:#fff;">✕</button>
            </span>
        </div>
    @empty
        <p class="text-center text-xs text-gray-400 py-3">{{ __('no_bets_found') }}</p>
    @endforelse
</div>
