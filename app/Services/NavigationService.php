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
                'roles' => ['all'], // Available to all roles
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
                ],
            ],

            // Section: Operations & Assets
            ['type' => 'divider', 'label' => 'Operations'],
            [
                'type' => 'group',
                'label' => 'Inventory & Assets',
                'icon' => 'database',
                'roles' => ['admin', 'super-admin', 'inventory', 'operations', 'manager'],
                'priority' => 80,
                'children' => [
                    [
                        'label' => 'Stock Management',
                        'route' => 'mastertinta.index',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('mastertinta.index'),
                        'roles' => ['admin', 'super-admin', 'inventory', 'operations'],
                    ],
                    [
                        'label' => 'Inventory Master',
                        'route' => 'masterinventory.index',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('masterinventory.index'),
                        'roles' => ['admin', 'super-admin', 'inventory', 'operations'],
                    ],
                    [
                        'label' => 'Maintenance Inventory',
                        'route' => 'maintenance.inventory.index',
                        'icon' => 'wrench',
                        'active' => request()->routeIs('maintenance.inventory.index'),
                        'roles' => ['admin', 'super-admin', 'maintenance', 'operations'],
                    ],
                    [
                        'label' => 'Type Inventory',
                        'route' => 'masterinventory.typeindex',
                        'icon' => 'cog',
                        'active' => request()->routeIs('masterinventory.typeindex'),
                        'roles' => ['admin', 'super-admin', 'inventory'],
                    ],
                ],
            ],
            [
                'type' => 'group',
                'label' => 'Quality Control',
                'icon' => 'beaker',
                'roles' => ['admin', 'super-admin', 'quality', 'operations', 'manager'],
                'priority' => 75,
                'children' => [
                    [
                        'label' => 'Verification Reports',
                        'route' => 'qaqc.report.index',
                        'icon' => 'clipboard-document-check',
                        'active' => request()->routeIs('qaqc.report.*'),
                        'roles' => ['admin', 'super-admin', 'quality', 'operations'],
                    ],
                    [
                        'label' => 'Form Adjust',
                        'route' => 'listformadjust',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('listformadjust'),
                        'roles' => ['admin', 'super-admin', 'quality'],
                    ],
                    [
                        'label' => 'Defect Categories',
                        'route' => 'qaqc.defectcategory',
                        'icon' => 'x-circle',
                        'active' => request()->routeIs('qaqc.defectcategory'),
                        'roles' => ['admin', 'super-admin', 'quality'],
                    ],
                ],
            ],
            [
                'type' => 'group',
                'label' => 'Production',
                'icon' => 'cog',
                'roles' => ['admin', 'super-admin', 'production', 'operations', 'manager'],
                'priority' => 70,
                'children' => [
                    [
                        'label' => 'Form Request Trial',
                        'route' => 'pe.formlist',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('pe.*'),
                        'roles' => ['admin', 'super-admin', 'production', 'operations'],
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
                        'permission' => 'pr.view-any',
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
                        'roles' => ['admin', 'super-admin', 'procurement', 'manager'],
                    ],
                    [
                        'label' => 'Supplier Evaluation',
                        'route' => 'purchasing.evaluationsupplier.index',
                        'icon' => 'check-circle',
                        'active' => request()->routeIs('purchasing.evaluationsupplier.*'),
                        'roles' => ['admin', 'super-admin', 'procurement', 'manager'],
                    ],
                    [
                        'label' => 'Forecast Customer Master',
                        'route' => 'fc.index',
                        'icon' => 'user-group',
                        'active' => request()->routeIs('fc.index'),
                        'roles' => ['admin', 'super-admin', 'procurement', 'sales'],
                    ],
                ],
            ],

            // Section: Finance & Oversight
            ['type' => 'divider', 'label' => 'Finance'],
            [
                'type' => 'group',
                'label' => 'Finance & Accounting',
                'icon' => 'currency-dollar',
                'roles' => ['admin', 'super-admin', 'finance', 'manager'],
                'priority' => 85,
                'children' => [
                    [
                        'label' => 'Approved PRs',
                        'route' => 'accounting.purchase-request',
                        'icon' => 'clipboard-document-check',
                        'active' => request()->routeIs('accounting.purchase-request'),
                        'roles' => ['admin', 'super-admin', 'finance', 'accounting'],
                    ],
                    [
                        'label' => 'Monthly Budget Reports',
                        'route' => 'monthly-budget-reports.index',
                        'icon' => 'chart-bar',
                        'active' => request()->routeIs('monthly-budget-reports.*'),
                        'roles' => ['admin', 'super-admin', 'finance', 'accounting', 'manager'],
                    ],
                    [
                        'label' => 'Budget Summary Reports',
                        'route' => 'monthly-budget-summary-report.index',
                        'icon' => 'chart-bar',
                        'active' => request()->routeIs('monthly-budget-summary-report.*'),
                        'roles' => ['admin', 'super-admin', 'finance', 'accounting', 'manager'],
                    ],
                ],
            ],
            [
                'type' => 'group',
                'label' => 'Operations',
                'icon' => 'truck',
                'roles' => ['admin', 'super-admin', 'operations', 'logistics', 'manager'],
                'priority' => 80,
                'children' => [
                    [
                        'label' => 'Delivery Notes',
                        'route' => 'delivery-notes.index',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('delivery-notes.*'),
                        'roles' => ['admin', 'super-admin', 'operations', 'logistics'],
                    ],
                    [
                        'label' => 'Destinations',
                        'route' => 'destination.index',
                        'icon' => 'map-pin',
                        'active' => request()->routeIs('destination.*'),
                        'roles' => ['admin', 'super-admin', 'operations', 'logistics'],
                    ],
                    [
                        'label' => 'Vehicles',
                        'route' => 'vehicles.index',
                        'icon' => 'truck',
                        'active' => request()->routeIs('vehicles.*') || request()->routeIs('services.*'),
                        'roles' => ['admin', 'super-admin', 'operations', 'logistics'],
                    ],
                    [
                        'label' => 'SPK Management',
                        'route' => 'spk.index',
                        'icon' => 'clipboard-document-check',
                        'active' => request()->routeIs('spk.*'),
                        'roles' => ['admin', 'super-admin', 'operations', 'production'],
                    ],
                    [
                        'label' => 'Daily Reports',
                        'route' => 'daily-reports.index',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('daily-reports.*'),
                        'roles' => ['admin', 'super-admin', 'operations', 'manager'],
                    ],
                    [
                        'label' => 'Upload Daily Report',
                        'route' => 'daily-report.form',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('daily-report.form'),
                        'roles' => ['admin', 'super-admin', 'operations', 'manager'],
                    ],
                    [
                        'label' => 'Department Expenses',
                        'route' => 'department-expenses.index',
                        'icon' => 'currency-dollar',
                        'active' => request()->routeIs('department-expenses.*'),
                        'roles' => ['admin', 'super-admin', 'finance', 'accounting', 'manager'],
                    ],
                ],
            ],

            // Section: Oversight & Compliance
            ['type' => 'divider', 'label' => 'Oversight'],
            [
                'type' => 'group',
                'label' => 'Performance & Evaluation',
                'icon' => 'chart-bar',
                'roles' => ['admin', 'super-admin', 'hr', 'manager'],
                'priority' => 70,
                'children' => [
                    [
                        'label' => 'All Evaluations',
                        'route' => 'discipline.index',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('discipline.*'),
                        'roles' => ['admin', 'super-admin', 'hr', 'manager'],
                    ],
                    [
                        'label' => 'Yayasan Evaluations',
                        'route' => 'yayasan.table',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('yayasan.table'),
                        'roles' => ['admin', 'super-admin', 'hr'],
                    ],
                    [
                        'label' => 'Internship Evaluations',
                        'route' => 'magang.table',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('magang.table'),
                        'roles' => ['admin', 'super-admin', 'hr'],
                    ],
                    [
                        'label' => 'Individual Evaluations (All IN)',
                        'route' => 'format.evaluation.year.allin',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('format.evaluation.year.allin'),
                        'roles' => ['admin', 'super-admin', 'hr', 'manager'],
                    ],
                    [
                        'label' => 'Individual Evaluations (Yayasan)',
                        'route' => 'format.evaluation.year.yayasan',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('format.evaluation.year.yayasan'),
                        'roles' => ['admin', 'super-admin', 'hr'],
                    ],
                    [
                        'label' => 'Individual Evaluations (Internship)',
                        'route' => 'format.evaluation.year.magang',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('format.evaluation.year.magang'),
                        'roles' => ['admin', 'super-admin', 'hr'],
                    ],
                    [
                        'label' => 'Individual Evaluations (Renewal)',
                        'route' => 'format.evaluation.year.allinperpanjangan',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('format.evaluation.year.allinperpanjangan'),
                        'roles' => ['admin', 'super-admin', 'hr', 'manager'],
                    ],
                    [
                        'label' => 'Export Yayasan JPayroll',
                        'route' => 'exportyayasan.dateinput',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('exportyayasan.dateinput'),
                        'roles' => ['admin', 'super-admin', 'hr', 'finance'],
                    ],
                ],
            ],
            [
                'type' => 'group',
                'label' => 'Compliance & Documentation',
                'icon' => 'folder',
                'roles' => ['admin', 'super-admin', 'compliance', 'hr', 'manager'],
                'priority' => 60,
                'children' => [
                    [
                        'label' => 'File Requirements',
                        'route' => 'requirements.index',
                        'icon' => 'clipboard-document-list',
                        'active' => request()->routeIs('requirements.index'),
                        'roles' => ['admin', 'super-admin', 'compliance', 'hr'],
                    ],
                    [
                        'label' => 'Assign Requirements',
                        'route' => 'requirements.assign',
                        'icon' => 'clipboard-document-check',
                        'active' => request()->routeIs('requirements.assign'),
                        'roles' => ['admin', 'super-admin', 'compliance', 'hr'],
                    ],
                    [
                        'label' => 'Review Uploads',
                        'route' => 'admin.requirement-uploads',
                        'icon' => 'clipboard-document-check',
                        'active' => request()->routeIs('admin.requirement-uploads'),
                        'roles' => ['admin', 'super-admin', 'compliance'],
                    ],
                    [
                        'label' => 'Departments Overview',
                        'route' => 'departments.overview',
                        'icon' => 'building',
                        'active' => request()->routeIs('departments.overview'),
                        'roles' => ['admin', 'super-admin', 'manager', 'hr'],
                    ],
                    [
                        'label' => 'Compliance Dashboard',
                        'route' => 'compliance.dashboard',
                        'icon' => 'chart-bar',
                        'active' => request()->routeIs('compliance.dashboard'),
                        'roles' => ['admin', 'super-admin', 'compliance', 'manager'],
                    ],
                    [
                        'label' => 'File Library',
                        'route' => 'files.index',
                        'icon' => 'folder',
                        'active' => request()->routeIs('files.*'),
                        'roles' => ['admin', 'super-admin', 'compliance', 'hr', 'manager'],
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
                    $item['active'] = request()->routeIs($item['route']);
                }
                if (isset($item['children'])) {
                    $item['children'] = collect($item['children'])->map(function ($child) {
                        if (isset($child['route'])) {
                            $child['active'] = request()->routeIs($child['route']);
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
                return $user->can($item['permission']);
            }
            if (isset($item['roles'])) {
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
                    'active' => request()->routeIs($item['route']),
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
                        'active' => request()->routeIs($child['route']),
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
}
