<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tables</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $tables->where('status', 'available')->count() }} available ·
                {{ $tables->where('status', 'occupied')->count() }} occupied ·
                {{ $tables->count() }} total
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="openSectionForm()"
                    class="px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                + Section
            </button>
            <button wire:click="openTableForm()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Table
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- Status legend --}}
    <div class="flex items-center gap-4 mb-6 flex-wrap">
        @foreach(['available' => ['bg-green-100 border-green-300 text-green-800', 'Available'], 'occupied' => ['bg-orange-100 border-orange-300 text-orange-800', 'Occupied'], 'reserved' => ['bg-blue-100 border-blue-300 text-blue-800', 'Reserved'], 'cleaning' => ['bg-gray-100 border-gray-300 text-gray-600', 'Cleaning']] as $status => [$cls, $label])
            <div class="flex items-center gap-1.5">
                <div class="w-4 h-4 rounded {{ $cls }} border"></div>
                <span class="text-xs text-gray-500">{{ $label }}</span>
            </div>
        @endforeach
    </div>

    {{-- Sections filter --}}
    @if($sections->count() > 1)
        <div class="flex items-center gap-2 mb-5 overflow-x-auto pb-1">
            <button wire:click="$set('sectionFilter', '')"
                    class="px-3 py-1.5 text-xs font-medium rounded-full transition-colors whitespace-nowrap
                           {{ $sectionFilter === '' ? 'bg-primary-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:border-gray-400' }}">
                All Sections
            </button>
            @foreach($sections as $section)
                <button wire:click="$set('sectionFilter', '{{ $section->id }}')"
                        class="px-3 py-1.5 text-xs font-medium rounded-full transition-colors whitespace-nowrap
                               {{ $sectionFilter == $section->id ? 'bg-primary-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:border-gray-400' }}">
                    {{ $section->name }}
                </button>
            @endforeach
        </div>
    @endif

    {{-- Tables grid, grouped by section --}}
    @if($tables->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <p class="text-gray-500 text-sm">No tables yet. Add your first table.</p>
        </div>
    @else
        @foreach($grouped as $sectionName => $sectionTables)
            <div class="mb-8">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                    {{ $sectionName }}
                    @php $sec = $sections->first(fn($s) => $s->name === $sectionName); @endphp
                    @if($sec)
                        <button wire:click="openSectionForm({{ $sec->id }})"
                                class="normal-case text-primary-500 hover:text-primary-700 font-normal tracking-normal"
                                title="Edit section">Edit</button>
                        <button wire:click="deleteSection({{ $sec->id }})"
                                wire:confirm="Delete section '{{ $sec->name }}'?"
                                class="normal-case text-red-400 hover:text-red-600 font-normal tracking-normal"
                                title="Delete section">Delete</button>
                    @endif
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                    @foreach($sectionTables as $table)
                        @php
                            $statusStyles = [
                                'available' => 'bg-green-50 border-green-200 hover:border-green-400',
                                'occupied'  => 'bg-orange-50 border-orange-200 hover:border-orange-400',
                                'reserved'  => 'bg-blue-50 border-blue-200 hover:border-blue-400',
                                'cleaning'  => 'bg-gray-50 border-gray-200 hover:border-gray-400',
                            ][$table->status] ?? 'bg-white border-gray-200';
                        @endphp
                        <div class="relative group rounded-xl border-2 p-4 transition-all cursor-pointer {{ $statusStyles }}"
                             @if($table->status === 'occupied')
                                 wire:click="$dispatch('open-session', { tableId: {{ $table->id }} })"
                             @endif>

                            {{-- Actions top-right --}}
                            <div class="absolute top-2 right-2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-10"
                                 x-on:click.stop>
                                <button wire:click="openTableForm({{ $table->id }})"
                                        class="p-1 rounded bg-white shadow text-gray-400 hover:text-primary-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="deleteTable({{ $table->id }})"
                                        wire:confirm="Delete table {{ $table->table_number }}?"
                                        class="p-1 rounded bg-white shadow text-gray-400 hover:text-red-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-800">{{ $table->table_number }}</p>
                                @if($table->name)
                                    <p class="text-xs text-gray-500 truncate">{{ $table->name }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">{{ $table->capacity }} seats</p>

                                @if($table->status === 'occupied')
                                    <a href="{{ route('app.tables.session', $table) }}" wire:navigate
                                       class="mt-2 inline-block w-full text-xs py-1.5 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 transition-colors">
                                        View Order
                                    </a>
                                @else
                                    <div x-data="{ open: false }" class="mt-2 relative" x-on:click.stop>
                                        <button @click="open = !open"
                                                class="w-full text-xs py-1.5 border border-gray-300 text-gray-600 font-medium rounded-lg hover:bg-white transition-colors">
                                            {{ ucfirst($table->status) }}
                                        </button>
                                        <div x-show="open" x-on:click.outside="open = false" @click.stop
                                             class="absolute top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-lg border border-gray-200 z-20">
                                            @foreach(['available', 'occupied', 'reserved', 'cleaning'] as $s)
                                                @if($s !== $table->status)
                                                    <button wire:click="updateStatus({{ $table->id }}, '{{ $s }}')"
                                                            @click="open = false"
                                                            class="block w-full text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 first:rounded-t-lg last:rounded-b-lg">
                                                        {{ ucfirst($s) }}
                                                    </button>
                                                @endif
                                            @endforeach
                                            @if($table->status === 'available')
                                                <a href="{{ route('app.tables.session', $table) }}" wire:navigate
                                                   class="block w-full text-left px-3 py-2 text-xs text-primary-600 font-medium hover:bg-primary-50 rounded-b-lg">
                                                    Open New Order →
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    {{-- Table Form Modal --}}
    @if($showTableForm)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeTableForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5">{{ $editingTableId ? 'Edit Table' : 'Add Table' }}</h2>
                <form wire:submit="saveTable" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Table # <span class="text-red-500">*</span></label>
                            <input wire:model="tableNumber" type="text" placeholder="e.g. T1" autofocus
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('tableNumber') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                            <input wire:model="capacity" type="number" min="1" max="50"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Name (optional)</label>
                        <input wire:model="tableName" type="text" placeholder="e.g. Window Booth"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                        <select wire:model="sectionId"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">— No Section —</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                                class="flex-1 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
                            Save Table
                        </button>
                        <button type="button" wire:click="closeTableForm"
                                class="flex-1 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Section Form Modal --}}
    @if($showSectionForm)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeSectionForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">{{ $editingSectionId ? 'Edit Section' : 'Add Section' }}</h2>
                <form wire:submit="saveSection" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Section Name</label>
                        <input wire:model="sectionName" type="text" placeholder="e.g. Outdoor, VIP, Ground Floor" autofocus
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('sectionName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Save</button>
                        <button type="button" wire:click="closeSectionForm" class="flex-1 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
