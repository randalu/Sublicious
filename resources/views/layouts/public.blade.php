<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if(isset($googleMapsKey) && $googleMapsKey)
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&libraries=places&callback=initMap" defer></script>
    @endif
    @livewireStyles
</head>
<body class="h-full font-sans antialiased bg-gray-50">
    {{ $slot }}
    @livewireScripts
</body>
</html>
