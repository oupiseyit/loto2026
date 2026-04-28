@php
    $user = auth()->user();
    $role = $user?->role;
    $currentRoute = request()->route()?->getName();

    $tabs = [
        ['key' => 'home',    'route' => 'home',    'roles' => null],
        ['key' => 'record',  'route' => 'record',  'roles' => null],
        ['key' => 'result',  'route' => 'result',  'roles' => null],
        ['key' => 'setting', 'route' => 'setting', 'roles' => ['admin', 'master']],
        ['key' => 'account', 'route' => 'account', 'roles' => null],
    ];

    $visibleTabs = array_filter($tabs, fn($tab) =>
        $tab['roles'] === null || in_array($role, $tab['roles'])
    );
@endphp

<!-- Top Nav Bar -->
<nav class="fixed top-0 left-0 right-0 z-40 flex items-center justify-between px-3 h-12 md:h-14"
     style="background-color: #D4A017;">

    <!-- Brand -->
    <div class="flex items-center gap-2">
        <span class="text-white font-black text-lg leading-none">HT</span>
        <span class="text-white/80 text-sm font-semibold">ភ្នាក់</span>
    </div>

    <!-- Desktop: tab links -->
    <div class="hidden md:flex items-center gap-1">
        @foreach ($visibleTabs as $tab)
            @php $isActive = $currentRoute === $tab['route']; @endphp
            <a href="{{ route($tab['route']) }}"
               class="px-3 py-1.5 text-sm font-semibold rounded transition-colors {{ $isActive ? 'bg-white/20 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                {{ __(($tab['key'])) }}
            </a>
        @endforeach
    </div>

    <!-- User + Language + Logout -->
    <div class="flex items-center gap-2">
        @if ($user)
            <span class="hidden md:block text-xs text-white/80">
                {{ $user->name }}
                <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-white/20">
                    {{ $role }}
                </span>
            </span>
        @endif

        <!-- Language selector -->
        @php
            $locales = ['en' => 'EN', 'km' => 'ខ្មែរ', 'vi' => 'VI', 'th' => 'ไทย'];
            $currentLocale = app()->getLocale();
        @endphp
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" type="button"
                    class="flex items-center gap-1 px-2 py-1 rounded-lg text-white text-xs font-semibold hover:bg-white/20 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                </svg>
                {{ $locales[$currentLocale] ?? 'EN' }}
            </button>
            <div x-show="open" @click.away="open = false" x-transition
                 class="absolute right-0 mt-1 w-24 rounded-lg shadow-lg overflow-hidden z-50 bg-white border border-gray-200">
                @foreach ($locales as $code => $label)
                    <form method="POST" action="{{ route('locale.set') }}">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $code }}">
                        <button type="submit"
                                class="w-full text-left px-3 py-2 text-sm font-medium transition-colors
                                       {{ $currentLocale === $code ? 'bg-[#D4A017] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                            {{ $label }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

        {{-- Day / Night toggle --}}
        <div x-data>
            <button type="button"
                    @click="$store.theme.toggle()"
                    class="flex flex-col items-center gap-0.5 px-2 py-1 rounded-lg text-white hover:bg-white/20 transition-colors">
                {{-- Moon (light mode) --}}
                <svg x-show="!$store.theme.isDark()" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                {{-- Sun (dark mode) --}}
                <svg x-show="$store.theme.isDark()" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex flex-col items-center gap-0.5 px-2 py-1 rounded-lg text-white hover:bg-white/20 transition-colors"
                    title="Logout">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="text-[10px] leading-none">{{ __('logout') }}</span>
            </button>
        </form>
    </div>
</nav>

<!-- Mobile: Bottom Tab Bar -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 flex"
     style="background-color: #D4A017; border-top: 2px solid #B8860B;">
    @foreach ($visibleTabs as $tab)
        @php $isActive = $currentRoute === $tab['route']; @endphp
        <a href="{{ route($tab['route']) }}"
           class="flex-1 flex flex-col items-center justify-center py-2 gap-0.5 text-[10px] font-semibold transition-colors
                  {{ $isActive ? 'text-white bg-white/20' : 'text-white/60 hover:text-white' }}">
            @if ($tab['route'] === 'home')
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            @elseif ($tab['route'] === 'record')
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            @elseif ($tab['route'] === 'result')
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
            @elseif ($tab['route'] === 'setting')
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            @elseif ($tab['route'] === 'account')
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            @endif
            <span>{{ __($tab['key']) }}</span>
        </a>
    @endforeach
</nav>
