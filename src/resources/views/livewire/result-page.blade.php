@php
    $SESSIONS = [['key'=>'morning','label'=>__('morning')],['key'=>'noon','label'=>__('noon')],['key'=>'evening','label'=>__('evening')]];
    $canEdit  = $this->canEdit;
    $dates    = $this->dates;
@endphp

<div class="flex flex-col md:flex-row h-full overflow-hidden bg-white">

    {{-- Mobile: horizontal date scroller --}}
    <div class="flex md:hidden border-b border-gray-200 flex-shrink-0 overflow-x-auto" style="background-color:#FFF8DC;">
        @forelse ($dates as $d)
            <button wire:click="selectDate('{{ $d }}')" type="button"
                    class="flex-shrink-0 px-3 py-2 text-xs font-medium whitespace-nowrap border-b-2 transition-colors"
                    style="{{ $selectedDate === $d ? 'border-color:#D4A017;background-color:#D4A017;color:#fff;' : 'border-color:transparent;color:#374151;' }}">
                {{ \Carbon\Carbon::parse($d)->format('d M Y') }}
            </button>
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
                <button wire:click="selectDate('{{ $d }}')" type="button"
                        class="w-full text-left px-3 py-2.5 text-sm font-medium border-b border-gray-100 transition-colors"
                        style="{{ $selectedDate === $d ? 'background-color:#D4A017;color:#fff;' : 'color:#374151;' }}">
                    {{ \Carbon\Carbon::parse($d)->format('d M Y') }}
                </button>
            @empty
                <p class="p-3 text-xs text-gray-400">{{ __('no_dates') }}</p>
            @endforelse
        </div>
    </aside>

    {{-- Right panel --}}
    <main class="flex-1 flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="px-3 md:px-4 py-2 md:py-3 border-b border-gray-200 flex items-center justify-between flex-shrink-0" style="background-color:#FFF8DC;">
            <div>
                <h1 class="text-sm md:text-base font-bold" style="color:#DC143C;">{{ __('result') }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('d M Y') : __('select_date') }}
                </p>
            </div>

            {{-- Print button --}}
            <button type="button"
                    onclick="window.print()"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold text-white hover:opacity-80"
                    style="background-color:#D4A017;">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                {{ __('print') }}
            </button>
        </div>

        {{-- Session radio --}}
        <div class="px-3 md:px-4 py-2 border-b border-gray-200 flex items-center gap-4 flex-shrink-0 bg-white">
            <span class="text-xs md:text-sm font-semibold text-gray-600">{{ __('session') }}:</span>
            @foreach ($SESSIONS as $s)
                <label class="flex items-center gap-1.5 cursor-pointer text-sm font-medium">
                    <input type="radio" name="session" value="{{ $s['key'] }}"
                           wire:click="selectSession('{{ $s['key'] }}')"
                           {{ $selectedSession === $s['key'] ? 'checked' : '' }}
                           class="accent-[#DC143C]">
                    <span style="color:{{ $selectedSession === $s['key'] ? '#DC143C' : '#374151' }};">{{ $s['label'] }}</span>
                </label>
            @endforeach
        </div>

        {{-- Result table --}}
        <div class="flex-1 overflow-auto">
            <div class="max-w-sm mx-auto w-full px-4 py-4">
                <table class="w-full border-collapse rounded-lg overflow-hidden shadow-sm">
                    <thead>
                        <tr style="background-color:#DC143C;">
                            <th class="py-3 px-6 text-center text-white font-bold text-base">{{ __('letter') }}</th>
                            <th class="py-3 px-6 text-center text-white font-bold text-base">{{ __('number') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($results as $idx => $row)
                            <tr style="background-color:{{ $idx % 2 === 0 ? '#fff' : '#FFF8DC' }};" class="border-b border-gray-100">
                                <td class="py-3 px-6 text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full text-white text-lg font-black"
                                          style="background-color:#DC143C;">
                                        {{ $row['position'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    @if ($canEdit)
                                        <input type="text" inputmode="numeric" maxlength="10"
                                               wire:model="results.{{ $idx }}.number"
                                               placeholder="—"
                                               class="w-20 text-center text-2xl font-black border-2 rounded-lg py-1 outline-none transition-colors"
                                               style="border-color:{{ $row['number'] ? '#D4A017' : '#E5E7EB' }};color:#B8860B;" />
                                    @else
                                        <span class="text-2xl font-black"
                                              style="color:{{ $row['number'] ? '#B8860B' : '#D1D5DB' }};">
                                            {{ $row['number'] ?: '—' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-12 text-center text-gray-400 text-sm">{{ __('no_results_session') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($canEdit && !empty($results))
                    <div class="mt-6 flex justify-center">
                        <button wire:click="save" type="button"
                                class="px-8 py-3 rounded-xl text-white font-bold text-base shadow-md hover:opacity-80 disabled:opacity-50"
                                style="background-color:#DC143C;">
                            <span wire:loading.remove wire:target="save">{{ __('save_results') }}</span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                </svg>
                                {{ __('saving') }}
                            </span>
                        </button>
                    </div>
                @endif

                @if (!$canEdit)
                    <p class="mt-4 text-center text-xs text-gray-400">{{ __('read_only_admin') }}</p>
                @endif
            </div>
        </div>
    </main>
</div>
