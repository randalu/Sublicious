<?php

namespace App\Livewire\App\Expenses;

use Livewire\Component;

class ExpenseList extends Component
{
    public function render()
    {
        return view('livewire.app.expenses.expense-list')
            ->layout('layouts.app', ['heading' => ' Expense List']);
    }
}
