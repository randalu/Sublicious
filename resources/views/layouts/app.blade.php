<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-public-key" content="{{ env('VAPID_PUBLIC_KEY') }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased">
    <div class="flex h-full" x-data="{ sidebarOpen: false }">

        {{-- Mobile sidebar backdrop --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-gray-900/80 lg:hidden"
             @click="sidebarOpen = false"></div>

        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-gray-900 lg:static lg:flex"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               style="transition: transform 0.3s ease">
            {{-- Logo --}}
            <div class="flex h-16 shrink-0 items-center px-6 border-b border-gray-700">
                <a href="{{ route('app.dashboard') }}" class="flex items-center gap-2">
                    <span class="text-xl font-bold text-white">🍽 Sublicious</span>
                </a>
            </div>

            {{-- Business name --}}
            <div class="px-6 py-3 border-b border-gray-700">
                <p class="text-xs text-gray-400 uppercase tracking-wider">Business</p>
                <p class="text-sm font-medium text-white truncate">{{ auth()->user()->business->name ?? 'N/A' }}</p>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1">
                @include('layouts.partials.app-nav')
            </nav>

            {{-- User menu --}}
            <div class="border-t border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full bg-primary-500 flex items-center justify-center">
                        <span class="text-sm font-medium text-white">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-white" title="Logout">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            {{-- Top bar --}}
            <header class="flex h-16 shrink-0 items-center border-b border-gray-200 bg-white px-4 shadow-sm">
                <button type="button" @click="sidebarOpen = true" class="lg:hidden -m-2.5 p-2.5 text-gray-700 mr-2">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <div class="flex flex-1 items-center justify-between">
                    <h1 class="text-lg font-semibold text-gray-900">{{ $heading ?? '' }}</h1>
                    <div class="flex items-center gap-3">
                        @livewire('app.plan-limit-banner')
                        @livewire('app.notification-bell')
                    </div>
                </div>
            </header>

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mx-6 mt-4 rounded-md bg-green-50 p-4 border border-green-200">
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="mx-6 mt-4 rounded-md bg-red-50 p-4 border border-red-200">
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
    <script src="{{ asset('js/pwa.js') }}" defer></script>
</body>
</html>
