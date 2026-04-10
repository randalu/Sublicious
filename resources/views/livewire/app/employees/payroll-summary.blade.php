<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Payroll Summary</h1>
        <div class="flex items-center gap-3">
            <input wire:model.live="month" type="month"
                   class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        </div>
    </div>

    {{-- Grand total --}}
    <div class="bg-primary-600 text-white rounded-xl p-5 mb-6">
        <p class="text-sm font-medium opacity-80">Total Payroll for {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</p>
        <p class="text-3xl font-bold mt-1">{{ number_format($grandTotal, 2) }}</p>
    </div>

    @if($payroll->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <p class="text-gray-500 text-sm">No active employees.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Present</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Absent</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Base Pay</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($payroll as $row)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ $row['employee']->name }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ ucfirst($row['employee']->role) }} ·
                                    {{ str_replace('_', ' ', $row['employee']->salary_type) }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-700">{{ $row['present_days'] }}</td>
                            <td class="px-4 py-3 text-center text-sm text-red-500">{{ $row['absent_days'] }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['total_hours'], 1) }}h</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['base_pay'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">
                                {{ $row['commission'] > 0 ? number_format($row['commission'], 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ number_format($row['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="6" class="px-4 py-3 text-sm font-semibold text-gray-700 text-right">Grand Total</td>
                        <td class="px-4 py-3 text-right text-base font-bold text-gray-900">{{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
