{{-- Table --}}
<div class="flex-1 overflow-auto">
    <table class="w-full text-sm border-collapse">
        <thead class="sticky top-0 z-10">
            <tr style="background-color:#DC143C;">
                <th class="px-3 py-2.5 text-center text-white font-semibold w-8"></th>
                <th class="px-3 py-2.5 text-left text-white font-semibold w-10">{{ __('no') }}</th>
                <th class="px-3 py-2.5 text-left text-white font-semibold">{{ __('invoice_no') }}</th>
                @if ($user->role !== 'staff')
                    <th class="px-3 py-2.5 text-left text-white font-semibold">{{ __('agent') }}</th>
                @endif
                <th class="px-3 py-2.5 text-center text-white font-semibold">{{ __('session') }}</th>
                <th class="px-3 py-2.5 text-right text-white font-semibold">{{ __('bets') }}</th>
                <th class="px-3 py-2.5 text-right text-white font-semibold">{{ __('total_amount') }}</th>
                <th class="px-3 py-2.5 text-center text-white font-semibold">{{ __('status') }}</th>
                <th class="px-3 py-2.5 text-right text-white font-semibold">{{ __('win') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $idx => $ticket)
                @php
                    $badge = $SESSION_BADGE[$ticket->session] ?? null;
                    $isWin = (float)($ticket->win_amount ?? 0) > 0;
                    $st    = $statusMap[$ticket->status] ?? $statusMap['pending'];
                    $rowId = 'ticket-' . $ticket->id;
                @endphp
                <tr class="border-b border-gray-100 hover:bg-yellow-50 transition-colors cursor-pointer"
                    style="background-color:{{ $idx % 2 === 0 ? '#fff' : '#FFF8DC' }};"
                    onclick="document.getElementById('{{ $rowId }}').classList.toggle('hidden')">
                    <td class="px-3 py-2.5 text-center text-gray-400">
                        <svg class="w-4 h-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </td>
                    <td class="px-3 py-2.5 text-gray-500 text-xs">{{ $idx + 1 }}</td>
                    <td class="px-3 py-2.5 font-mono text-xs font-semibold" style="color:#B8860B;">
                        {{ $ticket->invoice_number }}
                    </td>
                    @if ($user->role !== 'staff')
                        <td class="px-3 py-2.5 text-gray-700 max-w-[120px] truncate">
                            {{ $ticket->user->name ?? '—' }}
                        </td>
                    @endif
                    <td class="px-3 py-2.5 text-center">
                        @if ($badge)
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                  style="background-color:{{ $badge['bg'] }};color:{{ $badge['text'] }};">
                                {{ $badge['label'] }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-right text-gray-600">{{ $ticket->bets_count ?? 0 }}</td>
                    <td class="px-3 py-2.5 text-right font-semibold text-gray-800">
                        {{ number_format($ticket->total_amount) }}
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        @if ($isWin)
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">{{ __('win') }}</span>
                        @else
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                  style="background-color:{{ $st['bg'] }};color:{{ $st['text'] }};">
                                {{ $st['label'] }}
                            </span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-right font-semibold"
                        style="color:{{ $isWin ? '#16A34A' : '#6B7280' }};">
                        {{ $isWin ? number_format($ticket->win_amount) : '—' }}
                    </td>
                </tr>

                {{-- Expandable bets detail row --}}
                <tr id="{{ $rowId }}" class="hidden">
                    <td colspan="9" class="px-0 py-0">
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            @if ($ticket->bets && count($ticket->bets) > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                    @foreach ($ticket->bets as $bet)
                                        @php
                                            $betWon = (bool) $bet->is_winner;
                                            $winAmt = (float) ($bet->win_amount ?? 0);
                                        @endphp
                                        <div class="border rounded-lg p-3 {{ $betWon ? 'bg-green-50 border-green-300' : 'bg-white border-gray-200' }}">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-semibold text-gray-500">{{ $bet->letter }}</span>
                                                <span class="text-xs px-1.5 py-0.5 rounded font-semibold"
                                                      style="background-color:{{ $betWon ? '#dcfce7' : '#f3f4f6' }};color:{{ $betWon ? '#16a34a' : '#6b7280' }};">
                                                    {{ $betWon ? '✓ Win' : '✗ Loss' }}
                                                </span>
                                            </div>
                                            <div class="text-lg font-bold text-gray-800 mb-1">{{ $bet->number }}</div>
                                            <div class="text-xs text-gray-600 mb-1">
                                                <span class="font-semibold">Type:</span> {{ $bet->bet_type }}
                                            </div>
                                            <div class="text-xs text-gray-600 mb-1">
                                                <span class="font-semibold">Pos:</span> {{ $bet->position }}
                                            </div>
                                            <div class="border-t border-gray-200 pt-2 mt-2">
                                                <div class="flex justify-between text-xs mb-1">
                                                    <span class="text-gray-600">Bet:</span>
                                                    <span class="font-semibold">{{ number_format($bet->amount) }}</span>
                                                </div>
                                                @if ($betWon)
                                                    <div class="flex justify-between text-xs">
                                                        <span class="text-gray-600">Win:</span>
                                                        <span class="font-bold" style="color:#16a34a;">{{ number_format($winAmt) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-400 text-sm">No bets found</p>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="py-16 text-center">
                        <div class="flex flex-col items-center gap-3 text-gray-400">
                            <div class="w-20 h-20 rounded-full flex items-center justify-center opacity-10 text-5xl font-black text-white"
                                 style="background-color:#D4A017;">HT</div>
                            <p class="text-sm">{{ __('no_records') }}</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if ($tickets instanceof \Illuminate\Pagination\LengthAwarePaginator && $tickets->hasPages())
    <div class="flex-shrink-0 px-4 py-2 border-t border-gray-100">
        {{ $tickets->links() }}
    </div>
@endif
