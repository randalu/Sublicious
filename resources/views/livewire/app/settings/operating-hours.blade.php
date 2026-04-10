<div>
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-5">Weekly Schedule</h2>

        <form wire:submit="save" class="space-y-3">
            @foreach($days as $day => $name)
                <div class="grid grid-cols-12 gap-4 items-center py-3 border-b border-gray-100 last:border-0">
                    {{-- Day name --}}
                    <div class="col-span-3">
                        <span class="text-sm font-medium text-gray-700">{{ $name }}</span>
                    </div>

                    {{-- Closed toggle --}}
                    <div class="col-span-3 flex items-center gap-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   wire:model="hours.{{ $day }}.is_closed"
                                   class="sr-only peer">
                            <div class="w-10 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary-300 rounded-full peer
                                        peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full
                                        peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5
                                        after:start-[2px] after:bg-white after:border-gray-300 after:border
                                        after:rounded-full after:h-4 after:w-4 after:transition-all
                                        peer-checked:bg-red-500"></div>
                        </label>
                        <span class="text-xs text-gray-500">
                            {{ isset($hours[$day]['is_closed']) && $hours[$day]['is_closed'] ? 'Closed' : 'Open' }}
                        </span>
                    </div>

                    {{-- Times --}}
                    @if(!isset($hours[$day]['is_closed']) || !$hours[$day]['is_closed'])
                        <div class="col-span-3">
                            <label class="block text-xs text-gray-500 mb-1">Opens at</label>
                            <input wire:model="hours.{{ $day }}.open_time"
                                   type="time"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div class="col-span-3">
                            <label class="block text-xs text-gray-500 mb-1">Closes at</label>
                            <input wire:model="hours.{{ $day }}.close_time"
                                   type="time"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    @else
                        <div class="col-span-6 flex items-center">
                            <span class="text-sm text-red-500 font-medium">Closed all day</span>
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="flex justify-end pt-4">
                <button type="submit"
                        class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                    Save Hours
                </button>
            </div>
        </form>
    </div>
</div>
