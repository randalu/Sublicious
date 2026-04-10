<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sublicious — Restaurant & Delivery Management</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-white">
    <nav class="flex items-center justify-between px-6 py-4 lg:px-8 border-b border-gray-100">
        <span class="text-2xl font-bold text-gray-900">🍽 Sublicious</span>
        <div class="flex items-center gap-4">
            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">Sign in</a>
            <a href="{{ route('register') }}" class="rounded-md bg-orange-600 px-4 py-2 text-sm font-medium text-white hover:bg-orange-700">
                Start for Free
            </a>
        </div>
    </nav>

    <div class="mx-auto max-w-4xl px-6 py-20 text-center">
        <h1 class="text-5xl font-bold tracking-tight text-gray-900">
            Restaurant Management<br><span class="text-orange-600">Made Simple</span>
        </h1>
        <p class="mt-6 text-lg text-gray-600 max-w-2xl mx-auto">
            Complete restaurant & delivery management in one platform. Orders, billing, delivery, staff, and reports — register your business and start for free.
        </p>
        <div class="mt-10 flex items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="rounded-lg bg-orange-600 px-8 py-3.5 text-base font-semibold text-white hover:bg-orange-700">
                Get Started Free →
            </a>
            <a href="{{ route('login') }}" class="text-base font-semibold text-gray-700 hover:text-orange-600">Sign In</a>
        </div>
    </div>

    <div class="mx-auto max-w-5xl px-6 pb-20 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['Orders & POS', 'Dine-in, delivery, takeaway and online orders from one dashboard.'],
            ['Delivery Management', 'Assign riders, track deliveries, and manage commission payouts.'],
            ['Table Live Billing', 'Open tabs per table — add items live, close and print receipt.'],
            ['Google Maps Orders', 'Customer order form with Google Maps address pinning.'],
            ['Staff & Attendance', 'Employees, attendance tracking, shifts and payroll.'],
            ['Full Reports', 'Financial, order, delivery, and employee reports with CSV/PDF export.'],
        ] as [$title, $desc])
            <div class="rounded-xl bg-gray-50 p-6 ring-1 ring-gray-200">
                <h3 class="font-semibold text-gray-900">{{ $title }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ $desc }}</p>
            </div>
        @endforeach
    </div>
</body>
</html>
