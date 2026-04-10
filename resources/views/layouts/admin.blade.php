<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin — ' . config('app.name') }}</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased">
    <div class="flex h-full" x-data="{ sidebarOpen: false }">
        {{-- Mobile backdrop --}}
        <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-gray-900/80 lg:hidden" @click="sidebarOpen = false"></div>

        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-slate-900 lg:static"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               style="transition: transform 0.3s ease">
            <div class="flex h-16 items-center px-6 border-b border-slate-700">
                <span class="text-xl font-bold text-white">🍽 Sublicious</span>
                <span class="ml-2 rounded bg-orange-500 px-1.5 py-0.5 text-xs font-bold text-white">ADMIN</span>
            </div>

            <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1">
                @php
                    $adminNav = [
                        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
                        ['route' => 'admin.businesses', 'label' => 'Businesses', 'icon' => 'building-storefront'],
                        ['route' => 'admin.plans', 'label' => 'Plans', 'icon' => 'banknotes'],
                        ['route' => 'admin.subscriptions', 'label' => 'Subscriptions', 'icon' => 'document-text'],
                        ['route' => 'admin.logs', 'label' => 'Audit Logs', 'icon' => 'clipboard-document-list'],
                        ['route' => 'admin.settings.api-keys', 'label' => 'API Settings', 'icon' => 'cog-6-tooth'],
                    ];
                @endphp
                @foreach($adminNav as $item)
                    @php $isActive = request()->routeIs($item['route'] . '*'); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="group flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors
                              {{ $isActive ? 'bg-orange-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        @include('layouts.partials.icon', ['name' => $item['icon'], 'class' => 'h-5 w-5 shrink-0'])
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-slate-700 p-4">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full bg-orange-500 flex items-center justify-center">
                        <span class="text-sm font-bold text-white">SA</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-400">Super Admin</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="flex h-16 items-center border-b border-gray-200 bg-white px-4 shadow-sm">
                <button @click="sidebarOpen = true" class="lg:hidden -m-2.5 p-2.5 text-gray-700 mr-2">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-900">{{ $heading ?? '' }}</h1>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
