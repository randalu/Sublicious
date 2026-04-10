<?php

namespace App\Livewire\App\Settings;

use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;

class Users extends Component
{
    public bool    $showForm      = false;
    public ?int    $editingId     = null;

    // Form fields
    public string  $name     = '';
    public string  $email    = '';
    public string  $phone    = '';
    public string  $role     = 'cashier';

    // Generated password shown after creation
    public ?string $newPassword = null;

    public const ROLES = [
        'manager' => 'Manager',
        'cashier' => 'Cashier',
        'kitchen' => 'Kitchen',
        'rider'   => 'Rider',
    ];

    protected function rules(): array
    {
        $emailRule = 'required|email|max:200|unique:users,email';
        if ($this->editingId) {
            $emailRule .= ',' . $this->editingId;
        }

        return [
            'name'  => 'required|string|max:200',
            'email' => $emailRule,
            'phone' => 'nullable|string|max:30',
            'role'  => 'required|in:manager,cashier,kitchen,rider',
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetForm();
        $this->showForm  = true;
        $this->editingId = $id;
        $this->newPassword = null;

        if ($id) {
            $user        = User::findOrFail($id);
            $this->name  = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->role  = $user->role;
        }
    }

    public function closeForm(): void
    {
        $this->showForm    = false;
        $this->editingId   = null;
        $this->newPassword = null;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = $this->email = $this->phone = '';
        $this->role = 'cashier';
    }

    public function save(): void
    {
        $this->validate();

        $business = auth()->user()->business;

        if ($this->editingId) {
            User::findOrFail($this->editingId)->update([
                'name'  => trim($this->name),
                'phone' => trim($this->phone) ?: null,
                'role'  => $this->role,
            ]);
            $this->closeForm();
            session()->flash('success', 'User updated.');
        } else {
            $tempPassword = Str::random(12);

            User::create([
                'business_id' => $business->id,
                'name'        => trim($this->name),
                'email'       => trim($this->email),
                'phone'       => trim($this->phone) ?: null,
                'role'        => $this->role,
                'password'    => $tempPassword,
                'is_active'   => true,
            ]);

            $this->newPassword = $tempPassword;
            $this->showForm    = false;
        }
    }

    public function toggleActive(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'You cannot deactivate your own account.');
            return;
        }

        $user = User::findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);
    }

    public function dismissPassword(): void
    {
        $this->newPassword = null;
    }

    public function render()
    {
        $business = auth()->user()->business;

        $users = User::where('business_id', $business->id)
            ->where('role', '!=', 'super_admin')
            ->orderBy('name')
            ->get();

        return view('livewire.app.settings.users', [
            'users' => $users,
            'roles' => self::ROLES,
        ])->layout('layouts.app', ['heading' => 'Team Members']);
    }
}
