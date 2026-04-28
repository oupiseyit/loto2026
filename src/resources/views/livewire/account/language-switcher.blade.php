<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center gap-3 mb-4">
        <h2 class="text-base font-bold" style="color:#DC143C;">{{ __('language') }}</h2>
        <div class="flex-1 h-px" style="background-color:#DC143C33;"></div>
    </div>
    <div class="grid grid-cols-2 gap-2">
        @foreach ([
            'en' => ['label' => 'English',    'flag' => '🇺🇸'],
            'km' => ['label' => 'ខ្មែរ',       'flag' => '🇰🇭'],
            'vi' => ['label' => 'Tiếng Việt', 'flag' => '🇻🇳'],
            'th' => ['label' => 'ภาษาไทย',    'flag' => '🇹🇭'],
        ] as $code => $lang)
            @php $active = (session('locale', 'en') === $code); @endphp
            <form method="POST" action="{{ route('locale.set') }}">
                @csrf
                <input type="hidden" name="locale" value="{{ $code }}">
                <button type="submit"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border-2 text-sm font-semibold transition-all"
                        style="{{ $active
                            ? 'background-color:#DC143C;color:#fff;border-color:#DC143C;'
                            : 'background-color:#fff;color:#374151;border-color:#e5e7eb;' }}">
                    <span class="text-xl">{{ $lang['flag'] }}</span>
                    <span>{{ $lang['label'] }}</span>
                    @if ($active)
                        <svg class="ml-auto w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </button>
            </form>
        @endforeach
    </div>
</div>
