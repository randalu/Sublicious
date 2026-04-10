<div>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Employee Report</h1>
        <div class="flex items-center gap-3 flex-wrap">
            <input wire:model.live="dateFrom" type="date" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <span class="text-gray-400 text-sm">to</span>
            <input wire:model.live="dateTo" type="date" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <button wire:click="exportCsv"
                    class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['Total Staff', $this->summary['total'], 'blue'],
            ['Present Today', $this->summary['present'], 'green'],
            ['Absent Today', $this->summary['absent'], 'red'],
            ['Total Hours (period)', number_format($this->summary['totalHours'], 1) . 'h', 'purple'],
        ] as [$label, $value, $color])
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $label }}</p>
                <p class="text-2xl font-bold text-{{ $color }}-600 mt-1">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Attendance Summary for Period</h2>
        </div>
        @if(empty($this->employeeData))
            <div class="p-8 text-center text-gray-400 text-sm">No employees.</div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Present Days</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Absent Days</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Hours</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($this->employeeData as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm">
                                <p class="font-medium text-gray-900">{{ $row['employee']['name'] }}</p>
                                <p class="text-xs text-gray-400">{{ ucfirst($row['employee']['role']) }}</p>
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-green-600 font-medium">{{ $row['present_days'] }}</td>
                            <td class="px-4 py-3 text-center text-sm text-red-500">{{ $row['absent_days'] }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-gray-700">{{ $row['total_hours'] }}h</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
