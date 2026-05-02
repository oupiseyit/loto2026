<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm border-collapse">
        <thead>
            <tr style="background-color:#DC143C;">
                <th class="px-3 py-2.5 text-left text-white font-semibold">{{ __('staff') }}</th>
                <th class="px-3 py-2.5 text-center text-white font-semibold">{{ __('session') }}</th>
                <th class="px-3 py-2.5 text-right text-white font-semibold">{{ __('tickets') }}</th>
                <th class="px-3 py-2.5 text-right text-white font-semibold">{{ __('amount') }}</th>
                <th class="px-3 py-2.5 text-right text-white font-semibold">{{ __('win') }}</th>
                <th class="px-3 py-2.5 text-right text-white font-semibold">{{ __('net') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rowItems as $idx => $row)
                <tr class="border-b border-gray-100 hover:bg-yellow-50"
                    style="background-color:{{ $idx % 2 === 0 ? '#fff' : '#FFF8DC' }};">
                    <td class="px-3 py-2.5 font-medium text-gray-800">{{ $row->user_name }}</td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="text-xs font-semibold">{{ $SESSION_LABEL[$row->session] ?? $row->session }}</span>
                    </td>
                    <td class="px-3 py-2.5 text-right text-gray-600">{{ $row->ticket_count }}</td>
                    <td class="px-3 py-2.5 text-right font-semibold text-gray-800">{{ number_format($row->total_amount) }}</td>
                    <td class="px-3 py-2.5 text-right font-semibold" style="color:#DC143C;">{{ number_format($row->total_win) }}</td>
                    <td class="px-3 py-2.5 text-right font-semibold" style="color:#B8860B;">{{ number_format($row->net) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-gray-400 text-sm">{{ __('no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($rows instanceof \Illuminate\Pagination\LengthAwarePaginator && $rows->hasPages())
        <div class="px-4 py-2 border-t border-gray-100">
            {{ $rows->links() }}
        </div>
    @endif
</div>
