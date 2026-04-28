<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'HT ភ្នាក់') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased" style="background-color: #DC143C; min-height: 100vh;">

    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">
        <!-- Logo -->
        <div class="mb-6 text-center">
            <div class="text-5xl font-black text-white leading-none">HT</div>
            <div class="text-lg font-semibold text-white/80 mt-1">ភ្នាក់</div>
        </div>

        <div class="w-full max-w-sm bg-white rounded-2xl shadow-xl overflow-hidden">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>
