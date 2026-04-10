<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Employees</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('app.employees.attendance') }}" wire:navigate
               class="px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                Attendance
            </a>
            <a href="{{ route('app.employees.shifts') }}" wire:navigate
               class="px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                Shifts
            </a>
            <button wire:click="openForm()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Employee
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap gap-3 mb-5">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search employees…"
                   class="pl-9 w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        </div>
        <select wire:model.live="roleFilter"
                class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">All Roles</option>
            @foreach(['admin', 'manager', 'cashier', 'kitchen', 'rider'] as $r)
                <option value="{{ $r }}">{{ ucfirst($r) }}</option>
            @endforeach
        </select>
    </div>

    @if($employees->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <p class="text-gray-500 text-sm">No employees yet.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salary</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Since</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($employees as $emp)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ $emp->name }}</p>
                                @if($emp->phone) <p class="text-xs text-gray-400">{{ $emp->phone }}</p> @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700">{{ ucfirst($emp->role) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ number_format($emp->salary_amount, 2) }}
                                <span class="text-xs text-gray-400">/ {{ str_replace('_', ' ', $emp->salary_type) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $emp->hire_date?->format('d M Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <button wire:click="toggleActive({{ $emp->id }})"
                                        class="inline-flex items-center justify-center w-8 h-5 rounded-full transition-colors
                                               {{ $emp->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                    <span class="inline-block w-3 h-3 rounded-full bg-white shadow transition-transform
                                                 {{ $emp->is_active ? 'translate-x-1.5' : '-translate-x-1.5' }}"></span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openForm({{ $emp->id }})"
                                            class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $emp->id }})"
                                            wire:confirm="Delete {{ $emp->name }}?"
                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
            <div class="mt-4">{{ $employees->links() }}</div>
        @endif
    @endif

    {{-- Form Modal --}}
    @if($showForm)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-bold text-gray-900 mb-5">{{ $editingId ? 'Edit Employee' : 'Add Employee' }}</h2>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" autofocus
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input wire:model="phone" type="tel"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input wire:model="email" type="email"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select wire:model="role"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                @foreach(['admin' => 'Admin', 'manager' => 'Manager', 'cashier' => 'Cashier', 'kitchen' => 'Kitchen', 'rider' => 'Rider'] as $val => $lbl)
                                    <option value="{{ $val }}">{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
                            <input wire:model="hireDate" type="date"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salary Type</label>
                            <select wire:model="salaryType"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="monthly">Monthly</option>
                                <option value="hourly">Hourly</option>
                                <option value="commission_only">Commission Only</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                            <input wire:model="salaryAmount" type="number" step="0.01" min="0"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea wire:model="notes" rows="2"
                                  class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
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
