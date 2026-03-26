<?php

namespace App\Livewire\Admin;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccessOverviewDashboard extends Component
{
    public string $userSearch = '';

    public $selectedUser = null;

    public array $userPermissions = [];

    public array $userRoles = [];

    protected $queryString = [
        'userSearch' => ['except' => ''],
    ];

    public function mount()
    {
        if ($this->userSearch) {
            $this->lookupUser();
        }
    }

    public function updatedUserSearch()
    {
        if (strlen($this->userSearch) < 2) {
            $this->selectedUser = null;

            return;
        }
        $this->lookupUser();
    }

    public function lookupUser()
    {
        $user = User::where('name', 'like', "%{$this->userSearch}%")
            ->orWhere('email', 'like', "%{$this->userSearch}%")
            ->first();

        if ($user) {
            $this->selectedUser = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'active' => $user->is_active ?? true,
            ];
            $this->userRoles = $user->getRoleNames()->toArray();
            $this->userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
        } else {
            $this->selectedUser = null;
        }
    }

    public function clearSearch()
    {
        $this->userSearch = '';
        $this->selectedUser = null;
        $this->userRoles = [];
        $this->userPermissions = [];
    }

    public function getStatsProperty()
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
        ];
    }

    public function getRoleDistributionProperty()
    {
        return DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('count(*) as count'))
            ->groupBy('roles.name')
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.access-overview-dashboard', [
            'stats' => $this->stats,
            'roleDistribution' => $this->roleDistribution,
        ])->layout('new.layouts.app');
    }
}
