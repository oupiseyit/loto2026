<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'HT ភ្នាក់') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,400i,500,600i|nunito:400,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Apply saved theme immediately — prevents flash of wrong theme --}}
    <script>
        (function () {
            var t = localStorage.getItem('ht-theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body class="font-nunito antialiased" style="background-color: var(--ht-bg);">

    <x-navbar />

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="fixed top-14 left-0 right-0 z-50 mx-3 mt-1 px-4 py-2 rounded text-sm text-white bg-green-500 shadow">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="fixed top-14 left-0 right-0 z-50 mx-3 mt-1 px-4 py-2 rounded text-sm text-white bg-red-500 shadow">
            {{ session('error') }}
        </div>
    @endif

    {{ $slot }}

    @livewireScripts
    @stack('scripts')

    {{-- Alpine store for theme toggle --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                current: localStorage.getItem('ht-theme') || 'light',
                toggle() {
                    this.current = this.current === 'light' ? 'dark' : 'light';
                    localStorage.setItem('ht-theme', this.current);
                    document.documentElement.setAttribute('data-theme', this.current);
                },
                isDark() { return this.current === 'dark'; }
            });
        });
    </script>
</body>
</html>
