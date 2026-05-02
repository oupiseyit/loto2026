<form method="GET" action="{{ route('report') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
    <div class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('from') }}</label>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"
                   class="border-2 rounded-lg px-3 py-1.5 text-sm outline-none focus:border-[#D4A017] border-gray-200">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('to') }}</label>
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}"
                   class="border-2 rounded-lg px-3 py-1.5 text-sm outline-none focus:border-[#D4A017] border-gray-200">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('staff') }}</label>
            <select name="staff_id"
                    class="border-2 rounded-lg px-3 py-1.5 text-sm outline-none focus:border-[#D4A017] border-gray-200">
                <option value="">{{ __('all_staff') }}</option>
                @foreach ($staff_list as $s)
                    <option value="{{ $s->id }}" {{ ($filters['staffId'] ?? '') == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('session') }}</label>
            <select name="session"
                    class="border-2 rounded-lg px-3 py-1.5 text-sm outline-none focus:border-[#D4A017] border-gray-200">
                <option value="">{{ __('all') }}</option>
                @foreach (['morning' => __('morning'), 'noon' => __('noon'), 'evening' => __('evening')] as $val => $lbl)
                    <option value="{{ $val }}" {{ ($filters['session'] ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="px-5 py-1.5 rounded-lg text-white font-semibold text-sm"
                style="background-color:#D4A017;">{{ __('filter') }}</button>
        @if (!empty(array_filter([$filters['from'] ?? null, $filters['to'] ?? null, $filters['staffId'] ?? null, $filters['session'] ?? null])))
            <a href="{{ route('report') }}"
               class="px-5 py-1.5 rounded-lg font-semibold text-sm border-2 transition-colors"
               style="border-color:#D4A017;color:#D4A017;">{{ __('reset') }}</a>
        @endif
    </div>
</form>
