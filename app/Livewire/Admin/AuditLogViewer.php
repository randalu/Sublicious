<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class AuditLogViewer extends Component
{
    public function render()
    {
        return view('livewire.admin.audit-log-viewer')
            ->layout('layouts.admin', ['heading' => ' Audit Log Viewer']);
    }
}
