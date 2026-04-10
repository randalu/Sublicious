<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="text-center">
                <a href="/" class="text-3xl font-bold text-gray-900">🍽 Sublicious</a>
                <p class="mt-1 text-sm text-gray-500">Restaurant & Delivery Management</p>
            </div>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full {{ $wide ?? false ? 'sm:max-w-2xl' : 'sm:max-w-md' }}">
            <div class="bg-white py-8 px-4 shadow-sm ring-1 ring-gray-900/5 sm:rounded-lg sm:px-10">
                {{ $slot }}
            </div>
            @isset($footer)
                <div class="mt-4 text-center text-sm text-gray-500">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>

    @livewireScripts
</body>
</html>
