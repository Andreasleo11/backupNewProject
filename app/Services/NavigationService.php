<?php

namespace App\Services;

use App\Models\UserPageVisit;
use App\Models\UserPinnedRoute;
use Illuminate\Support\Facades\Auth;

class NavigationService
{
    /**
     * Get personalized navigation menu for the current user
     */
    public static function getPersonalizedMenu(): array
    {
        $user = Auth::user();

        if (! $user) {
            return self::getGuestMenu();
        }

        // Get base menu structure
        $menu = self::getBaseMenuStructure();

        // Apply role-based filtering
        $menu = self::applyRoleBasedFiltering($menu, $user);

        // Add Quick Access section
        $menu = self::addQuickAccessSection($menu, $user);

        // Apply smart defaults for collapsible sections
        $menu = self::applySmartDefaults($menu, $user);

        // Ensure all menu items have required keys
        $menu = self::ensureRequiredKeys($menu);

        return $menu;
    }

    /**
     * Get base menu structure (all possible items)
     */
    private static function getBaseMenuStructure(): array
    {
        return [
            // Quick Access will be inserted here

            // Core Navigation
            [
                'type' => 'single',
                'label' => 'Dashboard',
                'route' => 'home',
                'icon' => 'home',
                'active' => request()->routeIs('home'),
                'permission' => 'dashboard.view', // Available to all users with basic access
                'priority' => 100,
            ],

            // Section: Administration & Management
            ['type' => 'divider', 'label' => 'Management'],
            [
                'type' => 'group',
                'label' => 'Administration',
                'icon' => 'cog-6-tooth',
                // Removed group-level roles to allow partial access (if a user has any child permission, they'll see the group)
                'priority' => 90,
                'children' => [
                    [
                        'label' => 'Access Overview',
                        'route' => 'admin.access-overview.index',
                        'icon' => 'shield',
                        'active' => request()->routeIs('admin.access-overview.index'),
                        'permission' => 'role.view-any', // Auditors or whoever manages roles
                    ],
                    [
                        'label' => 'Users',
                        'route' => 'admin.users.index',
                        'icon' => 'user-group',
                        'active' => request()->routeIs('admin.users.index'),
                        'permission' => 'user.view-any',
                    ],
                    [
                        'label' => 'Roles',
                        'route' => 'admin.roles.index',
                        'icon' => 'key',
                        'active' => request()->routeIs('admin.roles.index'),
                        'permission' => 'role.view-any',
                    ],
                    [
                        'label' => 'Approval Rules',
                        'route' => 'admin.approval-rules.index',
                        'icon' => 'clipboard-document-check',
                        'active' => request()->routeIs('admin.approval-rules.index'),
                        'permission' => 'approval.manage-rules',
                    ],
                    [
                        'label' => 'Departments',
                        'route' => 'admin.departments.index',
                        'icon' => 'building',
                        'active' => request()->routeIs('admin.departments.*'),
                        'permission' => 'department.view-any',
                    ],
                    [
                        'label' => 'Employees',
                        'route' => 'admin.employees.index',
                        'icon' => 'users',
                        'active' => request()->routeIs('admin.employees.*'),
                        'permission' => 'employee.view-any',
                    ],
                    [
                        'label' => 'P&E Data (Monthly)',
                        'route' => 'admin.evaluation-data.index',
                        'icon' => 'table-cells',
                        'active' => request()->routeIs('admin.evaluation-data.*'),
                        'permission' => 'evaluation.view-any',
                    ],
                    [
                        'label' => 'P&E Data (Weekly)',
                        'route' => 'admin.evaluation-data-weekly.index',
                        'icon' => 'table-cells',
                        'active' => request()->routeIs('admin.evaluation-data-weekly.*'),
                        'permission' => 'evaluation.view-any',
                    ],
                ],
            ],

            // Section: Operations & Assets
            ['type' => 'divider', 'label' => 'Operations'],
            [
                'type' => 'group',
                'label' => 'Inventory & Assets',
                'icon' => 'database',
                'permission' => 'inventory.view',
                'priority' => 80,
                'children' => [
                    [
                        'label' => 'Stock Management',
                        'route' => 'mastertinta.index',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('mastertinta.index'),
                        'permission' => 'inventory.view',
                    ],
                    [
                        'label' => 'Inventory Master',
                        'route' => 'masterinventory.index',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('masterinventory.index'),
                        'permission' => 'inventory.view',
                    ],
                    [
                        'label' => 'Maintenance Inventory',
                        'route' => 'maintenance.inventory.index',
                        'icon' => 'wrench',
                        'active' => request()->routeIs('maintenance.inventory.index'),
                        'permission' => 'inventory.view',
                    ],
                    [
                        'label' => 'Type Inventory',
                        'route' => 'masterinventory.typeindex',
                        'icon' => 'cog',
                        'active' => request()->routeIs('masterinventory.typeindex'),
                        'permission' => 'inventory.manage',
                    ],
                ],
            ],
            [
                'type' => 'group',
                'label' => 'Quality Control',
                'icon' => 'beaker',
                'permission' => 'qc.view',
                'priority' => 75,
                'children' => [
                    [
                        'label' => 'Verification Reports',
                        'route' => 'qaqc.report.index',
                        'icon' => 'clipboard-document-check',
                        'active' => request()->routeIs('qaqc.report.*'),
                        'permission' => 'qc.view',
                    ],
                    [
                        'label' => 'Form Adjust',
                        'route' => 'listformadjust',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('listformadjust'),
                        'permission' => 'qc.view',
                    ],
                    [
                        'label' => 'Defect Categories',
                        'route' => 'qaqc.defectcategory',
                        'icon' => 'x-circle',
                        'active' => request()->routeIs('qaqc.defectcategory'),
                        'permission' => 'qc.manage',
                    ],
                ],
            ],
            [
                'type' => 'group',
                'label' => 'Production',
                'icon' => 'cog',
                'permission' => 'production.view',
                'priority' => 70,
                'children' => [
                    [
                        'label' => 'Form Request Trial',
                        'route' => 'pe.formlist',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('pe.*'),
                        'permission' => 'production.view',
                    ],
                ],
            ],

            // Section: Business Operations
            ['type' => 'divider', 'label' => 'Business'],
            [
                'type' => 'group',
                'label' => 'Procurement',
                'icon' => 'shopping-cart',
                'permission' => 'pr.view-any',
                'priority' => 85,
                'children' => [
                    [
                        'label' => 'Purchase Requests',
                        'route' => 'purchase-requests.index',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('purchase-requests.*'),
                        'permission' => ['pr.view-all', 'pr.view'],
                    ],
                    [
                        'label' => 'Purchase Orders',
                        'route' => 'po.dashboard',
                        'icon' => 'clipboard-document-check',
                        'active' => request()->routeIs('po.dashboard'),
                        'permission' => 'po.view-any',
                    ],
                    [
                        'label' => 'Forecast Prediction',
                        'route' => 'purchasing_home',
                        'icon' => 'chart-bar',
                        'active' => request()->routeIs('purchasing_home'),
                        'permission' => 'purchasing.view',
                    ],
                    [
                        'label' => 'Supplier Evaluation',
                        'route' => 'purchasing.evaluationsupplier.index',
                        'icon' => 'check-circle',
                        'active' => request()->routeIs('purchasing.evaluationsupplier.*'),
                        'permission' => 'purchasing.view',
                    ],
                    [
                        'label' => 'Forecast Customer Master',
                        'route' => 'fc.index',
                        'icon' => 'user-group',
                        'active' => request()->routeIs('fc.index'),
                        'permission' => 'purchasing.forecast',
                    ],
                ],
            ],

            // Section: Finance & Oversight
            ['type' => 'divider', 'label' => 'Finance'],
            [
                'type' => 'group',
                'label' => 'Finance & Accounting',
                'icon' => 'currency-dollar',
                'permission' => 'budget.view',
                'priority' => 85,
                'children' => [
                    [
                        'label' => 'Approved PRs',
                        'route' => 'purchase-requests.index',
                        'params' => ['custom_status' => 'APPROVED'],
                        'icon' => 'clipboard-document-check',
                        'active' => request()->routeIs('purchase-requests.index') && request('custom_status') === 'APPROVED',
                        'permission' => 'pr.view-all',
                    ],
                    [
                        'label' => 'Monthly Budget Reports',
                        'route' => 'monthly-budget-reports.index',
                        'icon' => 'chart-bar',
                        'active' => request()->routeIs('monthly-budget-reports.*'),
                        'permission' => 'budget.view',
                    ],
                    [
                        'label' => 'Budget Summary Reports',
                        'route' => 'monthly-budget-summary.index',
                        'icon' => 'chart-bar',
                        'active' => request()->routeIs('monthly-budget-summary.*'),
                        'permission' => 'budget.view',
                    ],
                ],
            ],
            [
                'type' => 'group',
                'label' => 'Operations',
                'icon' => 'truck',
                'permission' => 'ops.view',
                'priority' => 80,
                'children' => [
                    [
                        'label' => 'Delivery Notes',
                        'route' => 'delivery-notes.index',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('delivery-notes.*'),
                        'permission' => 'ops.view',
                    ],
                    [
                        'label' => 'Destinations',
                        'route' => 'destination.index',
                        'icon' => 'map-pin',
                        'active' => request()->routeIs('destination.*'),
                        'permission' => 'ops.view',
                    ],
                    [
                        'label' => 'Vehicles',
                        'route' => 'vehicles.index',
                        'icon' => 'truck',
                        'active' => request()->routeIs('vehicles.*') || request()->routeIs('services.*'),
                        'permission' => 'ops.view',
                    ],
                    [
                        'label' => 'SPK Management',
                        'route' => 'spk.index',
                        'icon' => 'clipboard-document-check',
                        'active' => request()->routeIs('spk.*'),
                        'permission' => 'spk.view',
                    ],
                    [
                        'label' => 'Daily Reports',
                        'route' => 'daily-reports.index',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('daily-reports.*'),
                        'permission' => 'ops.view',
                    ],
                    [
                        'label' => 'Department Expenses',
                        'route' => 'department-expenses.index',
                        'icon' => 'currency-dollar',
                        'active' => request()->routeIs('department-expenses.*'),
                        'permission' => 'expense.view',
                    ],
                ],
            ],

            // Section: Human Resources
            ['type' => 'divider', 'label' => 'Human Resources'],
            [
                'type' => 'group',
                'label' => 'Overtime',
                'icon' => 'clock',
                'permission' => 'overtime.view',
                'priority' => 65,
                'children' => [
                    [
                        'label' => 'Overtime Forms',
                        'route' => 'overtime.index',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('overtime.index') || request()->routeIs('overtime.detail'),
                        'permission' => ['overtime.view-all', 'overtime.view'],
                    ],
                    [
                        'label' => 'Overtime Summary',
                        'route' => 'overtime.summary',
                        'icon' => 'chart-bar',
                        'active' => request()->routeIs('overtime.summary'),
                        'permission' => ['overtime.view-all'],
                    ],
                    [
                        'label' => 'Import Actual',
                        'route' => 'actual.import.form',
                        'icon' => 'arrow-up-tray',
                        'active' => request()->routeIs('actual.import.form'),
                        'permission' => 'overtime.push-to-payroll',
                    ],
                ],
            ],
            [
                'type' => 'group',
                'label' => 'Personnel Forms',
                'icon' => 'clipboard-document',
                'permission' => 'dashboard.view', // Everyone can see basic forms
                'priority' => 64,
                'children' => [
                    [
                        'label' => 'Leave Forms',
                        'route' => 'formcuti',
                        'icon' => 'calendar-days',
                        'active' => request()->routeIs('formcuti*'),
                    ],
                    [
                        'label' => 'Exit Forms',
                        'route' => 'formkeluar',
                        'icon' => 'arrow-left-on-rectangle',
                        'active' => request()->routeIs('formkeluar*'),
                    ],
                ],
            ],
            [
                'type' => 'group',
                'label' => 'Personnel Management',
                'icon' => 'user-group',
                'permission' => 'personnel.view',
                'priority' => 63,
                'children' => [
                    [
                        'label' => 'Employee Trainings',
                        'route' => 'employee_trainings.index',
                        'icon' => 'academic-cap',
                        'active' => request()->routeIs('employee_trainings.*'),
                        'permission' => 'training.view',
                    ],
                    [
                        'label' => 'Important Documents',
                        'route' => 'hrd.importantDocs.index',
                        'icon' => 'folder-open',
                        'active' => request()->routeIs('hrd.importantDocs.index'),
                        'permission' => 'document.view',
                    ],
                ],
            ],

            // Section: Oversight & Compliance
            ['type' => 'divider', 'label' => 'Oversight'],
            [
                'type' => 'group',
                'label' => 'Performance & Evaluation',
                'icon' => 'chart-bar',
                'permission' => ['evaluation.view-any', 'evaluation.view-department'],
                'priority' => 70,
                'children' => [
                    [
                        'label' => 'Unified Evaluations',
                        'route' => 'evaluation.index',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('evaluation.*'),
                        'permission' => ['evaluation.view-any', 'evaluation.view-department'],
                    ],
                    [
                        'label' => 'Export JPayroll',
                        'route' => 'evaluation.jpayroll.select',
                        'icon' => 'arrow-down-tray',
                        'active' => request()->routeIs('evaluation.jpayroll.*'),
                        'permission' => ['evaluation.export-jpayroll'],
                    ],
                ],
            ],
            [
                'type' => 'group',
                'label' => 'Compliance & Documentation',
                'icon' => 'shield-check',
                'permission' => 'compliance.view',
                'priority' => 60,
                'children' => [
                    [
                        'label' => 'Dashboard',
                        'route' => 'compliance.dashboard',
                        'icon' => 'chart-bar',
                        'active' => request()->routeIs('compliance.dashboard'),
                        'permission' => 'compliance.view',
                    ],
                    [
                        'label' => 'Departments',
                        'route' => 'departments.index',
                        'icon' => 'building-office-2',
                        'active' => request()->routeIs('departments.*'),
                        'permission' => 'compliance.view',
                    ],
                    [
                        'label' => 'Requirements',
                        'route' => 'requirements.index',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('requirements.*'),
                        'permission' => 'compliance.view',
                    ],
                    [
                        'label' => 'Review Uploads',
                        'route' => 'requirement-uploads.review',
                        'icon' => 'inbox-arrow-down',
                        'active' => request()->routeIs('requirement-uploads.review'),
                        'permission' => 'compliance.review-uploads',
                    ],
                    [
                        'label' => 'File Library',
                        'route' => 'files.index',
                        'icon' => 'folder-open',
                        'active' => request()->routeIs('files.*'),
                        'permission' => 'compliance.view',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get menu for guest users
     */
    private static function getGuestMenu(): array
    {
        return [
            [
                'type' => 'single',
                'label' => 'Login',
                'route' => 'login',
                'icon' => 'lock',
                'active' => request()->routeIs('login'),
            ],
        ];
    }

    /**
     * Apply visibility filtering to the menu.
     *
     * Priority order per item:
     *   1. 'permission' key → $user->can($item['permission'])
     *      Use this when a Spatie permission already guards the route —
     *      the same permission controls nav visibility and route access.
     *   2. 'roles' key     → $user->hasAnyRole($item['roles'])
     *      Legacy fallback for sections not yet migrated to proper permissions.
     *   3. Neither         → hidden (super-admin always sees via bypass below).
     *
     * Migration path: replace 'roles' with 'permission' on a menu item once its
     * route is guarded by that Spatie permission.
     */
    private static function applyRoleBasedFiltering(array $menu, $user): array
    {
        // Super-admin sees everything
        if ($user->hasRole('super-admin')) {
            return collect($menu)->map(function ($item) {
                if ($item['type'] === 'single' && isset($item['route'])) {
                    // Item active state is already pre-calculated in getBaseMenuStructure

                }
                if (isset($item['children'])) {
                    $item['children'] = collect($item['children'])->map(function ($child) {
                        if (isset($child['route'])) {
                            // Child active state is already pre-calculated in getBaseMenuStructure
                        }

                        return $child;
                    })->toArray();
                }

                return $item;
            })->toArray();
        }

        // Resolve visibility: 'permission' beats 'roles'
        $canSee = function (array $item) use ($user): bool {
            if (isset($item['permission'])) {
                if (is_array($item['permission'])) {
                    return $user->hasAnyPermission($item['permission']);
                }

                return $user->can($item['permission']);
            }
            if (isset($item['roles'])) {
                if (in_array('all', $item['roles'], true)) {
                    return true;
                }

                return $user->hasAnyRole($item['roles']);
            }

            return false;
        };

        // Step 1: map — filter group children (map propagates mutations; bare filter does not)
        $menu = collect($menu)->map(function ($item) use ($canSee) {
            if ($item['type'] === 'group' && isset($item['children'])) {
                $item['children'] = collect($item['children'])
                    ->filter(fn ($child) => $canSee($child))
                    ->values()
                    ->toArray();
            }

            return $item;
        })->toArray();

        // Step 2: filter — drop invisible top-level items and now-empty groups
        return collect($menu)->filter(function ($item) use ($canSee) {
            if ($item['type'] === 'divider') {
                return true;
            }
            if ($item['type'] === 'group') {
                return ! empty($item['children']);
            }

            return $canSee($item);
        })->values()->toArray();
    }

    /**
     * Public entry point for the QuickAccess Livewire component.
     * Returns the scored + pinned items array for the given user.
     */
    public static function getQuickAccessItems($user): array
    {
        $menu = self::applyRoleBasedFiltering(self::getBaseMenuStructure(), $user);

        return self::buildQuickAccessItems($menu, $user);
    }

    /**
     * Add Quick Access section based on pins + visit activity, with role-based cold-start fallback.
     */
    private static function addQuickAccessSection(array $menu, $user): array
    {
        $quickItems = self::buildQuickAccessItems($menu, $user);

        array_unshift($menu, [
            'type' => 'quick-access',
            'label' => 'Quick Access',
            'icon' => 'star',
            'items' => $quickItems,
        ]);

        return $menu;
    }

    /**
     * Build the scored+pinned Quick Access items list. Shared by both the full menu pipeline
     * and the standalone Livewire QuickAccess component.
     */
    private static function buildQuickAccessItems(array $menu, $user): array
    {
        $userId = $user->id;
        $userRoles = $user->getRoleNames()->toArray();

        $allowedRoutes = self::buildAllowedRouteMap($menu, $userRoles, $user);

        // ── 1. Pinned items (max 3, always first) ───────────────────────────
        $pinnedRoutes = UserPinnedRoute::where('user_id', $userId)
            ->orderBy('pinned_at', 'desc')
            ->limit(3)
            ->pluck('route_name')
            ->toArray();

        $quickItems = [];
        foreach ($pinnedRoutes as $routeName) {
            if (isset($allowedRoutes[$routeName])) {
                $item = $allowedRoutes[$routeName];
                $item['pinned'] = true;
                $quickItems[] = $item;
            }
        }
        $pinnedRouteNames = array_column($quickItems, 'route');

        // ── 2. Activity-scored items (fill up to 5 total) ───────────────────
        $topVisits = UserPageVisit::where('user_id', $userId)
            ->orderByRaw('(visit_count * 2) + (10 / (DATEDIFF(NOW(), last_visited_at) + 1)) DESC')
            ->limit(10)
            ->get();

        foreach ($topVisits as $visit) {
            if (count($quickItems) >= 5) {
                break;
            }
            if (in_array($visit->route_name, $pinnedRouteNames)) {
                continue;
            }
            if (! isset($allowedRoutes[$visit->route_name])) {
                continue;
            }

            $item = $allowedRoutes[$visit->route_name];
            $item['pinned'] = false;
            $quickItems[] = $item;
        }

        return $quickItems;
    }

    /**
     * Build a flat map of [route_name => item_array] for all routes accessible to this user.
     * The $menu has already been role-filtered by applyRoleBasedFiltering() before this runs.
     */
    private static function buildAllowedRouteMap(array $menu, array $userRoles, $user): array
    {
        $map = [];

        foreach ($menu as $item) {
            if ($item['type'] === 'single' && isset($item['route'])) {
                $map[$item['route']] = [
                    'label' => $item['label'],
                    'route' => $item['route'],
                    'icon' => $item['icon'] ?? 'circle',
                    'active' => $item['active'] ?? request()->routeIs($item['route']),
                ];
            }

            if ($item['type'] === 'group' && isset($item['children'])) {
                foreach ($item['children'] as $child) {
                    if (! isset($child['route'])) {
                        continue;
                    }
                    $map[$child['route']] = [
                        'label' => $child['label'],
                        'route' => $child['route'],
                        'icon' => $child['icon'] ?? 'circle',
                        'active' => $child['active'] ?? request()->routeIs($child['route']),
                    ];
                }
            }
        }

        return $map;
    }

    /**
     * Auto-expand groups that contain the currently active page.
     * All other groups default to collapsed.
     */
    private static function applySmartDefaults(array $menu, $user): array
    {
        foreach ($menu as &$item) {
            if ($item['type'] === 'group' && isset($item['children'])) {
                $hasActiveChild = collect($item['children'])
                    ->contains(fn ($child) => $child['active'] ?? false);

                $item['defaultOpen'] = $hasActiveChild;
            }
        }

        return $menu;
    }

    /**
     * Ensure all menu items have required keys to prevent undefined array key errors
     */
    private static function ensureRequiredKeys(array $menu): array
    {
        return collect($menu)->map(function ($item) {
            // Ensure basic keys exist for all item types (don't set default roles)
            $defaults = [
                'active' => false,
                'priority' => 0,
            ];

            // Only merge defaults that don't override existing values
            foreach ($defaults as $key => $value) {
                if (! isset($item[$key])) {
                    $item[$key] = $value;
                }
            }

            // Handle Quick Access items
            if (isset($item['items']) && is_array($item['items'])) {
                $item['items'] = collect($item['items'])->map(function ($quickItem) {
                    return array_merge([
                        'active' => false,
                        'icon' => 'circle',
                        'label' => 'Unknown',
                        'route' => '#',
                    ], $quickItem);
                })->toArray();
            }

            // Handle group children
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = collect($item['children'])->map(function ($child) {
                    // Don't set default roles for children either
                    return array_merge([
                        'active' => false,
                        'icon' => 'circle',
                        'label' => 'Unknown',
                        'route' => '#',
                    ], $child);
                })->toArray();
            }

            // Ensure group-specific keys
            if ($item['type'] === 'group') {
                if (! isset($item['defaultOpen'])) {
                    $item['defaultOpen'] = false;
                }
                if (! isset($item['children'])) {
                    $item['children'] = [];
                }
            }

            return $item;
        })->toArray();
    }

    /**
     * Get menu item by route name
     */
    public static function getMenuItemByRoute(string $routeName): ?array
    {
        $menu = self::getPersonalizedMenu();

        foreach ($menu as $item) {
            if ($item['type'] === 'single' && isset($item['route']) && $item['route'] === $routeName) {
                return $item;
            }

            if (isset($item['children'])) {
                foreach ($item['children'] as $child) {
                    if (isset($child['route']) && $child['route'] === $routeName) {
                        return $child;
                    }
                }
            }

            if (isset($item['items'])) {
                foreach ($item['items'] as $quickItem) {
                    if (isset($quickItem['route']) && $quickItem['route'] === $routeName) {
                        return $quickItem;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get a flat list of all visible menu items for client-side search indexing.
     */
    public static function getSearchableMenu(): array
    {
        $menu = self::getPersonalizedMenu();
        $flat = [];

        foreach ($menu as $item) {
            if ($item['type'] === 'single' && isset($item['route'])) {
                $flat[] = [
                    'label' => $item['label'],
                    'route' => $item['route'],
                    'params' => $item['params'] ?? [],
                    'icon' => $item['icon'] ?? 'circle',
                    'active' => $item['active'] ?? false,
                ];
            }

            if ($item['type'] === 'group' && isset($item['children'])) {
                foreach ($item['children'] as $child) {
                    $flat[] = [
                        'label' => $child['label'],
                        'route' => $child['route'],
                        'params' => $child['params'] ?? [],
                        'icon' => $child['icon'] ?? 'circle',
                        'active' => $child['active'] ?? false,
                        'parent_label' => $item['label'],
                    ];
                }
            }
        }

        return $flat;
    }
}
