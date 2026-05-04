# Approval Scoping Layer

> **Living Document** · Last Updated: May 4, 2026

This document covers the two services that act as the **Single Source of Truth** for who can see, be notified about, and act on an approval request. Understanding these services is essential before touching anything related to approval visibility, policy checks, or notification dispatch.

---

## Table of Contents

1. [Why a Scoping Layer Exists](#1-why-a-scoping-layer-exists)
2. [The Approvable Contract (Prerequisite)](#2-the-approvable-contract-prerequisite)
3. [ApprovalScopingManager](#3-approvalscopingmanager)
   - [Department Responsibility (getEligibleDepartments)](#31-department-responsibility-geteligibledepartments)
   - [Single-User Eligibility (isUserEligible)](#32-single-user-eligibility-isusereligible)
   - [Notification Preference Gate (wantsNotification)](#33-notification-preference-gate-wantsnotification)
   - [Purchaser Specialization Helper (getPurchaserSpecializedDepartments)](#34-purchaser-specialization-helper-getpurchaserspecializeddepartments)
   - [Bulk Query Scoping (applyVisibilityScope)](#35-bulk-query-scoping-applyvisibilityscope)
   - [Instance-level Jurisdiction (hasJurisdiction)](#36-instance-level-jurisdiction-hasjurisdiction)
4. [ApprovalVisibilityScoper](#4-approvalvisibilityscoper)
   - [Layer 1 — Global Overrides](#41-layer-1--global-overrides)
   - [Layer 2 — Historical Actions](#42-layer-2--historical-actions)
   - [Layer 3 — Active Turn (with Branch Scoping)](#43-layer-3--active-turn-with-branch-scoping)
   - [Layer 4 — Jurisdiction-Based Oversight](#44-layer-4--jurisdiction-based-oversight)
5. [Configuration (config/approvals.php)](#5-configuration-configapprovalphp)
6. [Who Calls These Services](#6-who-calls-these-services)
   - [ApprovalEngine (Notification dispatch)](#61-approvalengine-notification-dispatch)
   - [ApprovalRequest Model Scope](#62-approvalrequest-model-scope)
   - [PurchaseRequestPolicy](#63-purchaserequestpolicy)
   - [SendDailyApprovalSummary Command](#64-senddailyapprovalsummary-command)
7. [Decision Flow Diagrams](#7-decision-flow-diagrams)
8. [Scoping Rules by Role (Reference Table)](#8-scoping-rules-by-role-reference-table)
9. [Adding a New Module](#9-adding-a-new-module)
10. [Known Gotchas](#10-known-gotchas)

---

## 1. Why a Scoping Layer Exists

Without a centralised scoping layer, approval visibility logic would need to be duplicated in at least four places:

| Consumer | What it needs to decide |
|---|---|
| **ApprovalEngine** (notifications) | Which users to notify for a role-based step |
| **ApprovalRequest::scopeForUser** (queries) | Which rows to return in index views |
| **PurchaseRequestPolicy** | Whether a user can `view`, `update`, or `delete` a single record |
| **SendDailyApprovalSummary** | Which requests to include in a user's digest email |

If each consumer implemented its own rules, they would inevitably diverge. The scoping layer solves this:

```
                         ┌────────────────────────────┐
                         │    ApprovalScopingManager   │
                         │  (Single Source of Truth)   │
                         └────────────┬───────────────┘
          ┌──────────────┬────────────┼────────────────────────┐
          ▼              ▼            ▼                        ▼
  ApprovalEngine  PurchaseRequest  ApprovalVisibility  Daily Digest
  (notifications)    Policy        Scoper (queries)     Command
```

---

## 2. The Approvable Contract (Prerequisite)

**File:** `app/Domain/Approval/Contracts/Approvable.php`

Every model that participates in the approval workflow must implement this interface. The scoping services call two of its methods to determine geographic/departmental context:

| Method | Returns | Used by |
|---|---|---|
| `approvalRequest()` | `morphOne` relationship | Engine, queries |
| `getApprovableTypeLabel()` | `string` — e.g. `"Purchase Request"` | Notifications, UI |
| `getApprovableIdentifier()` | `string` — e.g. `"PR/1/2026"` | Notifications, UI |
| `getApprovableShowUrl()` | `string` — route URL to the detail view | Notifications |
| `getApprovableDepartmentName()` | `?string` — originating department name | **Scoping** |
| `getApprovableBranchValue()` | `?string` — originating branch value | **Scoping** |

The two scoping methods feed directly into `isUserEligible()` and `hasJurisdiction()`. If a model returns `null` from either, those role checks short-circuit to `false`.

**Current implementors:**

| Model | `getDepartmentName()` source | `getBranchValue()` source |
|---|---|---|
| `PurchaseRequest` | `from_department` column | `branch` column |
| `OvertimeForm` | Resolved from `dept_id` → `Department.name` | `branch` column |

---

## 3. `ApprovalScopingManager`

**File:** `app/Infrastructure/Approval/Services/ApprovalScopingManager.php`  
**Namespace:** `App\Infrastructure\Approval\Services`

This class contains **all** jurisdiction logic in one place. It has no constructor dependencies — it reads from config and receives its inputs via method parameters.

### 3.1 Department Responsibility (`getEligibleDepartments`)

```php
public function getEligibleDepartments(User $user): array
```

Returns the user's primary department **plus** any departments they are responsible for via `config('approvals.department_links')`.

**Example:** A user in the `LOGISTIC` department gets `['LOGISTIC', 'STORE']` because `department_links['LOGISTIC'] = ['STORE']`.

This is used wherever a department-scoped role must be checked — never hardcode department chains in application logic.

---

### 3.2 Single-User Eligibility (`isUserEligible`)

```php
public function isUserEligible(User $user, string $roleSlug, Approvable $approvable): bool
```

Answers: **"Should this specific user be notified/authorized for this role step on this document?"**

Evaluates four cases in order:

| Case | Roles | Logic |
|---|---|---|
| **1. Department-scoped** | `department-head`, `supervisor`, `purchasing-manager` | The document's `departmentName` must be in the user's eligible departments (including linked ones) |
| **2. Branch-scoped** | `general-manager` | The document's `branchValue` must case-insensitively match the user's `employee.branch` |
| **3. Purchaser specialization** | `purchaser` on `PurchaseRequest` | Checks for a `purchaser-{dept-slug}` sub-role match; falls back to global access only if the user has **no** specialized sub-roles |
| **4. Global oversight** | Everything else (director, verificator…) | Always `true` |

> **Note:** Case 3 only applies when the `$approvable` is an instance of `PurchaseRequest`. For other document types, a user with the `purchaser` role falls through to Case 4 (global `true`).

---

### 3.3 Notification Preference Gate (`wantsNotification`)

```php
public function wantsNotification(User $user, string $moduleClass, string $requestedMode = 'immediate'): bool
```

Checks two layers of opt-out preferences before any notification is sent:

1. **Module-specific override** — stored in `user.notification_preferences` as `{ "App\Models\PurchaseRequest": "none" }`.
2. **Global mode** — `user.email_notification_mode` — one of `immediate`, `daily_summary`, `both`, or `none`.

| Requested mode | Allowed global modes |
|---|---|
| `immediate` | `immediate`, `both` |
| `daily_summary` | `daily_summary`, `both` |

A value of `none` at either layer short-circuits to `false` immediately.

---

### 3.4 Purchaser Specialization Helper (`getPurchaserSpecializedDepartments`)

```php
public function getPurchaserSpecializedDepartments(User $user): array
```

Scans all of the user's role names for those prefixed with `purchaser-`. For each match, it resolves the suffix to a `ToDepartment` enum value and returns an array of enum values.

**Example:** A user with roles `['purchaser', 'purchaser-moulding']` returns `['MOULDING']` (the enum value for that department).

An **empty array** means the user is a "global purchaser" with access to all categories. A **non-empty array** restricts them to only those specific categories.

---

### 3.5 Bulk Query Scoping (`applyVisibilityScope`)

```php
public function applyVisibilityScope(Builder $query, User $user, ?array $statuses = null): void
```

Applies `WHERE` clauses to an **`ApprovalRequest`** query to restrict results to records the user has jurisdiction over. Used internally by `ApprovalVisibilityScoper` and directly by `SendDailyApprovalSummary`.

The method dispatches to three private sub-methods, each isolated with `whereRaw('1 = 0')` seeds so they behave as strict additive `orWhere` blocks:

#### `applyPurchaseRequestScope` (Purchase Request rules)

| Sub-rule | Trigger | Filtered by |
|---|---|---|
| **A. Purchaser** | `pr.view` + `purchaser` role + no wide access | `to_department IN (specialized depts)` on the morphed PR |
| **B. Dept Head / Supervisor** | `pr.view` + dept role + no wide access | `LOWER(from_department) IN (eligible depts)` |
| **C. General Manager** | `pr.view` + `general-manager` + no wide access | `LOWER(branch) = user branch` |

#### `applyOvertimeScope` (Overtime rules)

| Sub-rule | Trigger | Filtered by |
|---|---|---|
| **A. Dept Head / Supervisor** | `overtime.view` + dept role + no wide access | `dept_id IN (resolved dept IDs)` on the morphed OvertimeForm |
| **B. General Manager** | `overtime.view` + `general-manager` + no wide access | `LOWER(branch) = user branch` |
| **C. Verificator** | `verificator` role | All `APPROVED` overtime (global, no isolation) |

#### `applyGlobalOversightScope` (Director / View-All)

| User type | Sees |
|---|---|
| `director` (has `approval.view-all`) | `IN_REVIEW`, `APPROVED`, `REJECTED` |
| Other `view-all` holders (non-director) | `APPROVED`, `REJECTED` only |

---

### 3.6 Instance-level Jurisdiction (`hasJurisdiction`)

```php
public function hasJurisdiction(User $user, Approvable $approvable): bool
```

Answers: **"Does this user have oversight authority over this specific document?"** Used for single-record authorization (policies, detail views).

Evaluation order:

```
1. system.admin → true (global bypass)
2. Module-specific bypass (pr.admin, pr.view-all, overtime.view-all) → true
   Module-specific base permission check (pr.view, overtime.view) → false if missing
3. general-manager → branch match → true if matches
4. department-head | supervisor | purchasing-manager → eligible dept match → true if matches
5. purchaser (PR only) → specialized dept match, or global fallback if no sub-roles
6. director | verificator → true (top-level oversight)
7. default → false
```

> **Critical:** Policy methods (`view`, `update`, `delete`, `cancel`, `viewPrices`) all call `hasJurisdiction()` to determine "oversight authority" — this is the mechanism that ensures **the policy and the index query always agree** on who can see a record.

---

## 4. `ApprovalVisibilityScoper`

**File:** `app/Infrastructure/Approval/Services/ApprovalVisibilityScoper.php`  
**Namespace:** `App\Infrastructure\Approval\Services`

This class has a single public method that is called as an Eloquent **model scope** (`ApprovalRequest::scopeForUser`). It applies the complete set of visibility rules to an `ApprovalRequest` query builder.

It uses `ApprovalScopingManager` internally (instantiated inline) for the jurisdiction layer.

### 4.1 Layer 1 — Global Overrides

```php
if ($user->can('system.admin') || $user->can('pr.admin')) {
    return; // No WHERE clauses added — sees everything
}
```

`system.admin` and `pr.admin` bypass all scoping entirely.

### 4.2 Layer 2 — Historical Actions

```php
$groupedQuery->orWhereHas('steps', function ($sq) use ($user) {
    $sq->where('acted_by', $user->id);
});
```

A user always sees requests they have **previously acted on** (approved, rejected, returned), regardless of current status.

### 4.3 Layer 3 — Active Turn (with Branch Scoping)

This is the most complex layer. It matches `IN_REVIEW` requests where it is **currently this user's turn**.

**Step 1 — Role matching:**

The query matches the current step's `approver_type`/`approver_id` against the user's identity and roles. `purchaser`-role steps get special treatment: if the user has specialized sub-roles, only PRs matching those categories are included.

**Step 2 — Jurisdiction intersection (`isBranchScoped`):**

A user is `isBranchScoped` if they have any role from `config('approvals.jurisdiction_scoped_roles')` (default: `department-head`, `supervisor`, `general-manager`) **and** no admin/view-all override.

If `isBranchScoped` is `true`, one of three overrides must also hold:

| Override | Effect |
|---|---|
| Direct `user` assignment (the step names this user's ID) | Bypasses jurisdiction check — always visible |
| Step is assigned to the `verificator` role | Bypass — verificator is always global |
| Passes `ApprovalScopingManager::applyVisibilityScope` for `IN_REVIEW` | Normal jurisdiction filter |

### 4.4 Layer 4 — Jurisdiction-Based Oversight

```php
$groupedQuery->orWhere(function ($oversightQuery) use ($user, $manager) {
    $oversightQuery->whereIn('status', ['IN_REVIEW', 'APPROVED', 'REJECTED', 'CANCELED']);
    $manager->applyVisibilityScope($oversightQuery, $user);
});
```

This layer covers index-view access: seeing requests from your department/branch even when it is **not your active turn**. It delegates entirely to `ApprovalScopingManager::applyVisibilityScope`.

---

## 5. Configuration (`config/approvals.php`)

| Key | Type | Purpose |
|---|---|---|
| `approvables` | `array` | Registry of model classes that implement `Approvable` (used by some UI lookups) |
| `department_links` | `array<string, string[]>` | Cross-department responsibility chains read by `getEligibleDepartments()` |
| `jurisdiction_scoped_roles` | `string[]` | Role slugs whose active-turn visibility is intersected with their branch/department |

### `department_links` example

```php
'department_links' => [
    'LOGISTIC' => ['STORE'],   // LOGISTIC head also oversees STORE
    'QC'       => ['QA'],      // QC head also oversees QA
],
```

To give a department head cross-department visibility, **only this config file** needs to change — no PHP logic is modified.

### `jurisdiction_scoped_roles`

```php
'jurisdiction_scoped_roles' => [
    'department-head',
    'supervisor',
    'general-manager',
],
```

Any role slug listed here will have its active-turn queries intersected with branch/department jurisdiction by `ApprovalVisibilityScoper`. Add a slug here to make a new role "locally scoped".

---

## 6. Who Calls These Services

### 6.1 `ApprovalEngine` (Notification dispatch)

**File:** `app/Infrastructure/Approval/Services/ApprovalEngine.php`

The engine holds `$this->scopingManager = new ApprovalScopingManager` and calls it in `notifyCurrentApprover()`:

```php
// For role-based steps:
$usersToNotify = $roleUsers->filter(function ($u) use ($roleSlug, $approvable) {
    // Filter 1: Jurisdiction
    if (! $this->scopingManager->isUserEligible($u, $roleSlug, $approvable)) {
        return false;
    }
    // Filter 2: Personal opt-out preference
    return $this->scopingManager->wantsNotification($u, get_class($approvable), 'immediate');
});
```

This ensures that when a step is assigned to the `department-head` role, only department heads from the correct department receive the notification — not all department heads in the system.

For **user-targeted steps**, only `wantsNotification()` is checked (eligibility is implied by direct assignment).

### 6.2 `ApprovalRequest` Model Scope

**File:** `app/Infrastructure/Persistence/Eloquent/Models/ApprovalRequest.php`

```php
public function scopeForUser($query, User $user): void
{
    (new ApprovalVisibilityScoper)->apply($query, $user);
}
```

Usage throughout the application:

```php
ApprovalRequest::forUser(auth()->user())->with('approvable')->get();
```

This scope is used in:
- **Dashboard widgets** — pending approval counts for the current user
- **Approval index/pending views** — all requests visible to the user
- **`SendDailyApprovalSummary`** — as the base query before further filtering

### 6.3 `PurchaseRequestPolicy`

**File:** `app/Policies/PurchaseRequestPolicy.php`

The policy injects `ApprovalScopingManager` via the constructor and calls `hasJurisdiction()` in five policy gates:

| Policy method | `hasJurisdiction()` call effect |
|---|---|
| `view` | `true` means oversight access (in addition to ownership and workflow involvement) |
| `update` | `true` means oversight allows editing if `PurchaseRequestSecurityService` also permits it |
| `updatePo` | Same as `update` |
| `delete` | `true` means oversight can delete documents beyond just own creations |
| `cancel` | `true` means oversight can cancel |
| `viewPrices` | `true` feeds into `iseSensitiveDataVisible()` |

The `before()` hook bypasses all policy checks for `system.admin` and `pr.admin`, matching the scoper's own bypass logic. This ensures the policy and the query scoper have identical top-level behavior.

### 6.4 `SendDailyApprovalSummary` Command

**File:** `app/Console/Commands/SendDailyApprovalSummary.php`  
**Artisan:** `app:send-approval-summary`

```bash
# Run daily via scheduler
php artisan app:send-approval-summary
```

The command:
1. Fetches all users configured for `daily_summary` or `both` notification mode.
2. For each user, loads actionable `ApprovalRequest`s using `ApprovalRequest::forUser($user)`.
3. Filters further with the `ApprovalScopingManager`:
   - **Jurisdictional check**: calls `isUserEligible($user, $roleSlug, $approvable)` for role-based steps to confirm the user is actually responsible for that specific document.
   - **Preference check**: calls `wantsNotification($user, $moduleClass, 'daily_summary')` to respect per-module overrides.
4. Sends a single `ApprovalSummaryNotification` with all matched requests if any exist.

---

## 7. Decision Flow Diagrams

### Notification target selection (role-based step)

```
For each user with the role:
    │
    ├─ isUserEligible($user, $roleSlug, $approvable)?
    │   ├─ roleSlug in dept-scoped list?
    │   │   └─ document dept ∈ user's eligible depts? → YES/NO
    │   ├─ roleSlug == 'general-manager'?
    │   │   └─ document branch == user branch? → YES/NO
    │   ├─ roleSlug == 'purchaser' && approvable is PR?
    │   │   ├─ user has matching purchaser-{dept} sub-role? → YES
    │   │   └─ user has no sub-roles + has 'purchaser' role? → YES (global fallback)
    │   └─ else → YES (global roles: director, verificator, etc.)
    │
    └─ wantsNotification($user, $moduleClass, 'immediate')?
        ├─ module-specific override == 'none'? → NO
        ├─ global mode == 'none'? → NO
        └─ mode ∈ ['immediate', 'both']? → YES
```

### ApprovalRequest::forUser() result set

```
User query starts → seed with WHERE 1=0

├─ GLOBAL BYPASS (system.admin | pr.admin) → return all rows (no WHERE added)

└─ OR: acted_by = user.id (historical participation)

   OR: status = IN_REVIEW
       AND (step matches user/role)
       AND IF jurisdiction_scoped_role:
              (direct user assignment OR verificator OR passes applyVisibilityScope)

   OR: status IN (IN_REVIEW, APPROVED, REJECTED, CANCELED)
       AND applyVisibilityScope (department/branch/global rules)
```

---

## 8. Scoping Rules by Role (Reference Table)

| Role | Index Visibility | Active-Turn Restriction | Notification Scope |
|---|---|---|---|
| `system.admin` | **All records** | None | User preference only |
| `pr.admin` | **All PR records** | None | User preference only |
| `director` | All `IN_REVIEW`, `APPROVED`, `REJECTED` | None | Always eligible |
| `verificator` | All `APPROVED` overtime; involved in any | Active turn always visible (bypass jurisdiction) | Always eligible |
| `general-manager` | Same branch, `IN_REVIEW`+`APPROVED`+`REJECTED` | Branch must match document | Branch match required |
| `department-head` | Own depts (+ linked), `APPROVED`+`REJECTED` | Dept must match document (PR); dept ID match (OT) | Dept match required |
| `supervisor` | Same as dept head | Same as dept head | Dept match required |
| `purchasing-manager` | Own depts (+ linked) | Dept match required | Dept match required |
| `purchaser` (global) | All `IN_REVIEW`+`APPROVED`+`REJECTED` PRs | Sees all purchaser steps | Always eligible |
| `purchaser` + sub-roles | Only matching categories | Only matching category steps | Category match required |
| `requester` / `staff` | Own creations only (via historical steps) | Direct assignment only | Direct assignment only |

---

## 9. Adding a New Module

To make a new document type (e.g., `BudgetRequest`) participate in the scoping layer:

### Step 1 — Implement `Approvable`

```php
class BudgetRequest extends Model implements Approvable
{
    public function approvalRequest() { return $this->morphOne(ApprovalRequest::class, 'approvable'); }
    public function getApprovableTypeLabel(): string { return 'Budget Request'; }
    public function getApprovableIdentifier(): string { return 'BR/' . $this->id; }
    public function getApprovableShowUrl(): string { return route('budget-requests.show', $this->id); }
    public function getApprovableDepartmentName(): ?string { return $this->department_name; }
    public function getApprovableBranchValue(): ?string { return $this->branch; }
}
```

### Step 2 — Add visibility rules to `ApprovalScopingManager`

Add a private `applyBudgetRequestScope(Builder $query, User $user, ?array $statuses): void` method following the same pattern as `applyPurchaseRequestScope`, then call it from `applyVisibilityScope()`.

### Step 3 — Add `hasJurisdiction` module identity check

Inside `hasJurisdiction()`, add:
```php
$isBudgetRequest = ($approvable instanceof BudgetRequest);
if ($isBudgetRequest) {
    if ($user->can('budget.admin')) return true;
    if (!$user->can('budget.view')) return false;
}
```

### Step 4 — Register in config (optional)

Add the model class to `config/approvals.php` → `approvables` for UI discovery:
```php
BudgetRequest::class => 'Budget Request',
```

No changes to `ApprovalVisibilityScoper` or `ApprovalEngine` are needed — they delegate to the manager.

---

## 10. Known Gotchas

> [!WARNING]
> **`ApprovalScopingManager` is instantiated with `new`, not resolved from the container.** Both `ApprovalEngine` and `ApprovalVisibilityScoper` do `$manager = new ApprovalScopingManager`. If you need to mock it in tests, you must refactor those classes to use constructor injection or `app()` resolution.

> [!IMPORTANT]
> **The `purchaser` role bypass only applies to `PurchaseRequest` instances.** In `isUserEligible()`, Case 3 has an `instanceof PurchaseRequest` guard. A `purchaser`-role step on any other document type falls through to Case 4 (global `true`). Keep this in mind when adding new modules that involve purchasers.

> [!NOTE]
> **`isBranchScoped` in `ApprovalVisibilityScoper` is computed from `config('approvals.jurisdiction_scoped_roles')`.** Adding a role slug to that config list automatically restricts its active-turn visibility to matching branches/departments — the verificator and direct-user-assignment overrides still apply.

> [!NOTE]
> **Department name comparisons are case-insensitive but whitespace-sensitive.** `strtoupper(trim($formDept))` is used. Ensure department names in `config/approvals.php → department_links` match the exact stored values (after trim) for `LOGISTIC`, `QC`, etc.

> [!TIP]
> **`applyVisibilityScope()` takes an optional `$statuses` parameter.** Passing `['IN_REVIEW']` tightens the filter to pending-only items. Omitting it uses module-appropriate defaults per sub-scope (e.g., purchaser defaults to `IN_REVIEW`, `APPROVED`, `REJECTED`, `CANCELED`).
