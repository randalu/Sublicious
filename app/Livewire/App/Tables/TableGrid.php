<?php

namespace App\Livewire\App\Tables;

use App\Models\RestaurantTable;
use App\Models\TableSection;
use Livewire\Component;

class TableGrid extends Component
{
    // Table form
    public bool   $showTableForm  = false;
    public ?int   $editingTableId = null;
    public string $tableNumber    = '';
    public string $tableName      = '';
    public int    $capacity       = 4;
    public ?int   $sectionId      = null;

    // Section form
    public bool   $showSectionForm  = false;
    public ?int   $editingSectionId = null;
    public string $sectionName      = '';

    // Filter
    public string $sectionFilter = '';

    protected function tableRules(): array
    {
        return [
            'tableNumber' => 'required|string|max:20',
            'tableName'   => 'nullable|string|max:100',
            'capacity'    => 'required|integer|min:1|max:50',
            'sectionId'   => 'nullable|integer|exists:table_sections,id',
        ];
    }

    public function openTableForm(?int $id = null): void
    {
        $this->resetTableForm();
        $this->showTableForm  = true;
        $this->editingTableId = $id;

        if ($id) {
            $t = RestaurantTable::findOrFail($id);
            $this->tableNumber = $t->table_number;
            $this->tableName   = $t->name ?? '';
            $this->capacity    = $t->capacity;
            $this->sectionId   = $t->section_id;
        }
    }

    public function closeTableForm(): void
    {
        $this->showTableForm  = false;
        $this->editingTableId = null;
        $this->resetTableForm();
    }

    private function resetTableForm(): void
    {
        $this->tableNumber = '';
        $this->tableName   = '';
        $this->capacity    = 4;
        $this->sectionId   = null;
    }

    public function saveTable(): void
    {
        $this->validate($this->tableRules());

        $data = [
            'table_number' => $this->tableNumber,
            'name'         => $this->tableName ?: null,
            'capacity'     => $this->capacity,
            'section_id'   => $this->sectionId,
        ];

        if ($this->editingTableId) {
            RestaurantTable::findOrFail($this->editingTableId)->update($data);
        } else {
            $data['qr_code_token'] = \Illuminate\Support\Str::random(32);
            RestaurantTable::create($data);
        }

        $this->closeTableForm();
        session()->flash('success', 'Table saved.');
    }

    public function deleteTable(int $id): void
    {
        $table = RestaurantTable::withCount(['orders' => fn ($q) => $q->where('payment_status', 'unpaid')])->findOrFail($id);
        if ($table->orders_count > 0) {
            session()->flash('error', 'Table has an open unpaid order. Close it first.');
            return;
        }
        $table->delete();
        session()->flash('success', 'Table deleted.');
    }

    public function updateStatus(int $id, string $status): void
    {
        RestaurantTable::findOrFail($id)->update(['status' => $status]);
    }

    // Section CRUD
    public function openSectionForm(?int $id = null): void
    {
        $this->showSectionForm  = true;
        $this->editingSectionId = $id;
        $this->sectionName      = $id ? TableSection::findOrFail($id)->name : '';
    }

    public function closeSectionForm(): void
    {
        $this->showSectionForm  = false;
        $this->editingSectionId = null;
        $this->sectionName      = '';
    }

    public function saveSection(): void
    {
        $this->validate(['sectionName' => 'required|string|max:100']);
        if ($this->editingSectionId) {
            TableSection::findOrFail($this->editingSectionId)->update(['name' => trim($this->sectionName)]);
        } else {
            TableSection::create(['name' => trim($this->sectionName)]);
        }
        $this->closeSectionForm();
    }

    public function deleteSection(int $id): void
    {
        $section = TableSection::withCount('tables')->findOrFail($id);
        if ($section->tables_count > 0) {
            session()->flash('error', 'Section has tables. Reassign them first.');
            return;
        }
        $section->delete();
    }

    public function render()
    {
        $query = RestaurantTable::with('section')
            ->withCount(['orders' => fn ($q) => $q->where('payment_status', 'unpaid')]);

        if ($this->sectionFilter) {
            $query->where('section_id', $this->sectionFilter);
        }

        $tables   = $query->orderBy('table_number')->get();
        $sections = TableSection::orderBy('sort_order')->orderBy('name')->get();

        // Group tables by section
        $grouped = $tables->groupBy(fn ($t) => $t->section?->name ?? 'No Section');

        return view('livewire.app.tables.table-grid', compact('tables', 'grouped', 'sections'))
            ->layout('layouts.app', ['heading' => 'Tables']);
    }
}
