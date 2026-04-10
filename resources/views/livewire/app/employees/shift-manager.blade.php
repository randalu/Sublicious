<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Shift Manager</h1>
        <div class="flex items-center gap-3">
            <button wire:click="prevWeek" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <span class="text-sm font-medium text-gray-700">
                Week of {{ \Carbon\Carbon::parse($weekStart)->format('d M Y') }}
            </span>
            <button wire:click="nextWeek" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <button wire:click="openForm()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Shift
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Weekly schedule grid --}}
    @if($employees->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <p class="text-gray-500 text-sm">No employees. <a href="{{ route('app.employees') }}" wire:navigate class="text-primary-600 hover:underline">Add employees first.</a></p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Employee</th>
                        @foreach($weekDays as $day)
                            <th class="px-2 py-3 text-center text-xs font-medium tracking-wider
                                       {{ $day === today()->format('Y-m-d') ? 'text-primary-600 bg-primary-50' : 'text-gray-500' }}">
                                <p>{{ \Carbon\Carbon::parse($day)->format('D') }}</p>
                                <p class="font-normal">{{ \Carbon\Carbon::parse($day)->format('d') }}</p>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($employees as $emp)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ $emp->name }}</p>
                                <p class="text-xs text-gray-400">{{ ucfirst($emp->role) }}</p>
                            </td>
                            @foreach($weekDays as $day)
                                @php
                                    $dayShifts = ($shifts[$emp->id] ?? collect())->filter(fn ($s) => $s->date->format('Y-m-d') === $day);
                                @endphp
                                <td class="px-1 py-2 text-center align-top
                                           {{ $day === today()->format('Y-m-d') ? 'bg-primary-50/30' : '' }}">
                                    @foreach($dayShifts as $shift)
                                        <div class="text-xs bg-primary-100 text-primary-800 rounded px-1 py-0.5 mb-1 flex items-center justify-between gap-1">
                                            <span>{{ $shift->start_time }}-{{ $shift->end_time }}</span>
                                            <button wire:click="delete({{ $shift->id }})"
                                                    wire:confirm="Remove this shift?"
                                                    class="text-primary-400 hover:text-red-600 text-xs leading-none">✕</button>
                                        </div>
                                    @endforeach
                                    <button wire:click="openForm(); $nextTick(() => { $wire.set('employeeId', {{ $emp->id }}); $wire.set('date', '{{ $day }}') })"
                                            class="text-gray-300 hover:text-primary-500 text-lg leading-none">+</button>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Shift Form Modal --}}
    @if($showForm)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5">{{ $editingId ? 'Edit Shift' : 'Add Shift' }}</h2>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee <span class="text-red-500">*</span></label>
                        <select wire:model="employeeId"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">— Select —</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                        @error('employeeId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                        <input wire:model="date" type="date"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                            <input wire:model="startTime" type="time"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                            <input wire:model="endTime" type="time"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <input wire:model="notes" type="text"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Save</button>
                        <button type="button" wire:click="closeForm" class="flex-1 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
