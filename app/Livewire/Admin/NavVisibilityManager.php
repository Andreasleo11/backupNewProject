<?php

namespace App\Livewire\Admin;

use App\Models\NavMenuAssignment;
use App\Models\NavUserGroup;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Admin UI for managing per-entity (user / role / permission / user-group)
 * navigation visibility overrides.
 *
 * Usage:  /admin/nav-visibility  (route: admin.nav-visibility.index)
 */
class NavVisibilityManager extends Component
{
    use WithPagination;

    // ── Selected menu item ───────────────────────────────────────────────
    public ?string $selectedRoute = null;
    public string  $selectedLabel = '';

    // ── Add-assignment form ──────────────────────────────────────────────
    public string $subjectType = 'role';   // user | role | permission | group
    public string $subjectSearch = '';
    public ?int   $subjectId = null;

    // ── User Group management ────────────────────────────────────────────
    public string $tab = 'menu';           // menu | groups
    public string $newGroupName = '';
    public string $newGroupDesc = '';
    public ?int   $editingGroupId = null;
    public string $memberSearch = '';
    public ?int   $memberAddUserId = null;

    // ── Helpers shared across render ─────────────────────────────────────
    protected $listeners = ['assignmentAdded' => '$refresh'];

    public function selectRoute(string $routeName, string $label): void
    {
        $this->selectedRoute  = $routeName;
        $this->selectedLabel  = $label;
        $this->subjectSearch  = '';
        $this->subjectId      = null;
        $this->resetErrorBag();
    }

    public function updatedSubjectType(): void
    {
        $this->subjectSearch = '';
        $this->subjectId     = null;
    }

    public function searchSubjects(): Collection
    {
        $q = '%' . $this->subjectSearch . '%';

        return match ($this->subjectType) {
            'user'       => User::where('name', 'like', $q)->orWhere('email', 'like', $q)->limit(8)->get()->map(fn($u) => ['id' => $u->id, 'label' => "{$u->name} ({$u->email})"]),
            'role'       => Role::where('name', 'like', $q)->limit(8)->get()->map(fn($r) => ['id' => $r->id, 'label' => $r->name]),
            'permission' => Permission::where('name', 'like', $q)->limit(8)->get()->map(fn($p) => ['id' => $p->id, 'label' => $p->name]),
            'group'      => NavUserGroup::where('name', 'like', $q)->limit(8)->get()->map(fn($g) => ['id' => $g->id, 'label' => $g->name]),
            default      => collect(),
        };
    }

    public function addAssignment(): void
    {
        $this->validate([
            'selectedRoute' => 'required|string',
            'subjectType'   => 'required|in:user,role,permission,group',
            'subjectId'     => 'required|integer',
        ]);

        $morphType = match ($this->subjectType) {
            'user'       => (new User)->getMorphClass(),
            'role'       => (new Role)->getMorphClass(),
            'permission' => (new Permission)->getMorphClass(),
            'group'      => (new NavUserGroup)->getMorphClass(),
        };

        NavMenuAssignment::firstOrCreate([
            'route_name'   => $this->selectedRoute,
            'subject_type' => $morphType,
            'subject_id'   => $this->subjectId,
        ]);

        $this->subjectSearch = '';
        $this->subjectId     = null;
    }

    public function removeAssignment(int $id): void
    {
        NavMenuAssignment::findOrFail($id)->delete();
    }

    // ── User Group methods ───────────────────────────────────────────────

    public function createGroup(): void
    {
        $this->validate(['newGroupName' => 'required|string|unique:nav_user_groups,name|max:80']);
        NavUserGroup::create(['name' => $this->newGroupName, 'description' => $this->newGroupDesc]);
        $this->newGroupName = '';
        $this->newGroupDesc = '';
    }

    public function deleteGroup(int $id): void
    {
        NavUserGroup::findOrFail($id)->delete();
        if ($this->editingGroupId === $id) {
            $this->editingGroupId = null;
        }
    }

    public function editGroup(int $id): void
    {
        $this->editingGroupId = ($this->editingGroupId === $id) ? null : $id;
        $this->memberSearch   = '';
        $this->memberAddUserId = null;
    }

    public function searchMemberCandidates(): Collection
    {
        if (! $this->editingGroupId || strlen($this->memberSearch) < 2) {
            return collect();
        }
        $q       = '%' . $this->memberSearch . '%';
        $current = NavUserGroup::findOrFail($this->editingGroupId)->users()->pluck('users.id');

        return User::where(fn($q2) => $q2->where('name', 'like', $q)->orWhere('email', 'like', $q))
            ->whereNotIn('id', $current)
            ->limit(8)
            ->get();
    }

    public function addMember(int $userId): void
    {
        NavUserGroup::findOrFail($this->editingGroupId)->users()->syncWithoutDetaching([$userId]);
        $this->memberSearch   = '';
    }

    public function removeMember(int $groupId, int $userId): void
    {
        NavUserGroup::findOrFail($groupId)->users()->detach($userId);
    }

    // ── Render ───────────────────────────────────────────────────────────

    // ── Search ──────────────────────────────────────────────────────────
    public string $menuSearch = '';

    public function getAllMenuItems(): array
    {
        // 1. Get base menu
        $nav = \App\Services\NavigationService::getPersonalizedMenu();

        // 2. Flatten into displayable items
        $items = [];
        foreach ($nav as $item) {
            // Single items
            if (($item['type'] ?? '') === 'single' && isset($item['route'])) {
                $items[] = [
                    'route' => $item['route'],
                    'label' => $item['label'],
                    'group' => 'General', // Default group for top-level singles
                    'icon'  => $item['icon'] ?? 'circle',
                ];
            }
            // Groups
            if (($item['type'] ?? '') === 'group') {
                foreach ($item['children'] ?? [] as $child) {
                    if (isset($child['route'])) {
                        $items[] = [
                            'route' => $child['route'],
                            'label' => $child['label'],
                            'group' => $item['label'],
                            'icon'  => $child['icon'] ?? 'circle',
                        ];
                    }
                }
            }
        }

        $collection = collect($items);

        // 3. Apply Search
        if ($this->menuSearch) {
            $term = strtolower($this->menuSearch);
            $collection = $collection->filter(fn($i) => 
                str_contains(strtolower($i['label']), $term) || 
                str_contains(strtolower($i['route']), $term) ||
                str_contains(strtolower($i['group']), $term)
            );
        }

        // 4. Attach Assignment Counts
        // Fetch counts for all routes in one query
        $counts = NavMenuAssignment::selectRaw('route_name, count(*) as count')
            ->groupBy('route_name')
            ->pluck('count', 'route_name');

        return $collection->map(function ($item) use ($counts) {
            $item['assignment_count'] = $counts[$item['route']] ?? 0;
            return $item;
        })->values()->toArray();
    }

    public function render()
    {
        $menuItems   = $this->getAllMenuItems();
        $assignments = $this->selectedRoute
            ? NavMenuAssignment::where('route_name', $this->selectedRoute)->get()->map(function ($a) {
                $label = match (true) {
                    str_ends_with($a->subject_type, 'User')          => optional(User::find($a->subject_id))->name . ' (user)',
                    str_ends_with($a->subject_type, 'Role')          => optional(Role::find($a->subject_id))->name . ' (role)',
                    str_ends_with($a->subject_type, 'Permission')    => optional(Permission::find($a->subject_id))->name . ' (permission)',
                    str_ends_with($a->subject_type, 'NavUserGroup')  => optional(NavUserGroup::find($a->subject_id))->name . ' (group)',
                    default => "#{$a->subject_id}",
                };
                return ['id' => $a->id, 'label' => $label];
            })
            : collect();

        $subjectSuggestions = $this->subjectSearch ? $this->searchSubjects() : collect();

        $groups = NavUserGroup::withCount('users')->orderBy('name')->get();

        $editingGroup   = $this->editingGroupId ? NavUserGroup::with('users')->find($this->editingGroupId) : null;
        $memberCandidates = $this->searchMemberCandidates();

        // Managed routes (have at least one assignment)
        $managedRoutes = NavMenuAssignment::distinct()->pluck('route_name')->toArray();

        return view('livewire.admin.nav-visibility-manager', compact(
            'menuItems', 'assignments', 'subjectSuggestions',
            'groups', 'editingGroup', 'memberCandidates', 'managedRoutes'
        ))->layout('new.layouts.app', ['title' => 'Nav Visibility Manager']);
    }
}
