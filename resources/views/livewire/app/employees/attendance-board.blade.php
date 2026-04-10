<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Attendance</h1>
        <div class="flex items-center gap-3">
            <button wire:click="$set('date', '{{ \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d') }}')"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <input wire:model.live="date" type="date"
                   class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <button wire:click="$set('date', '{{ \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d') }}')"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <button wire:click="$set('date', '{{ today()->format('Y-m-d') }}')"
                    class="px-3 py-2 text-xs font-medium border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50">
                Today
            </button>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @foreach(['present' => ['green', 'Present'], 'absent' => ['red', 'Absent'], 'late' => ['yellow', 'Late'], 'not_marked' => ['gray', 'Not Marked']] as $key => [$color, $label])
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-{{ $color }}-600">{{ $summary[$key] }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    {{-- Attendance table --}}
    @if($employees->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <p class="text-gray-500 text-sm">No active employees. <a href="{{ route('app.employees') }}" wire:navigate class="text-primary-600 hover:underline">Add employees first.</a></p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock In</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock Out</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($employees as $emp)
                        @php $att = $attendances->get($emp->id); @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ $emp->name }}</p>
                                <p class="text-xs text-gray-400">{{ ucfirst($emp->role) }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if($att)
                                    @php $statColors = ['present' => 'green', 'absent' => 'red', 'late' => 'yellow', 'half_day' => 'orange']; @endphp
                                    <span class="text-xs px-2 py-1 rounded-full bg-{{ $statColors[$att->status] ?? 'gray' }}-100 text-{{ $statColors[$att->status] ?? 'gray' }}-700">
                                        {{ ucfirst($att->status) }}
                                    </span>
                                @else
                                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-400">Not marked</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $att?->in_time?->format('H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $att?->out_time?->format('H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-medium text-gray-700">
                                {{ $att?->hours_worked ? number_format($att->hours_worked, 1) . 'h' : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if(! $att)
                                        <button wire:click="clockIn({{ $emp->id }})"
                                                class="px-2 py-1 text-xs bg-green-100 text-green-700 font-medium rounded-lg hover:bg-green-200 transition-colors">
                                            Clock In
                                        </button>
                                        <button wire:click="markAbsent({{ $emp->id }})"
                                                class="px-2 py-1 text-xs bg-red-50 text-red-600 font-medium rounded-lg hover:bg-red-100 transition-colors">
                                            Absent
                                        </button>
                                    @elseif($att && !$att->out_time && $att->status !== 'absent')
                                        <button wire:click="clockOut({{ $att->id }})"
                                                class="px-2 py-1 text-xs bg-orange-100 text-orange-700 font-medium rounded-lg hover:bg-orange-200 transition-colors">
                                            Clock Out
                                        </button>
                                    @endif
                                    @if($att)
                                        <select wire:change="markStatus({{ $att->id }}, $event.target.value)"
                                                class="text-xs rounded border-gray-200 py-1 focus:ring-primary-500 focus:border-primary-500">
                                            @foreach(['present', 'absent', 'late', 'half_day'] as $s)
                                                <option value="{{ $s }}" @selected($att->status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
