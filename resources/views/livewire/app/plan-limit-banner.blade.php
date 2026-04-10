@if($show)
    <div class="flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium
                {{ $isCritical ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
        @if($isCritical)
            <span class="h-1.5 w-1.5 rounded-full bg-red-500 animate-pulse"></span>
            <span>{{ $remaining }} orders left</span>
            <a href="{{ route('app.settings.subscription') }}" class="font-semibold underline">Upgrade</a>
        @else
            <span class="h-1.5 w-1.5 rounded-full bg-yellow-500"></span>
            <span>{{ $percent }}% of monthly limit used</span>
        @endif
    </div>
@endif
