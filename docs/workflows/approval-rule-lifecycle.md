# Approval Rule Lifecycle Workflow

## Overview

This document describes the complete lifecycle of an Approval Rule Template, from creation to deletion, including the versioning strategy.

## Lifecycle Diagram

```
┌─────────────────┐
│  1. CREATE RULE                            │
│  RuleManager::saveRule() (no editingRuleId)    │
│  → RuleTemplate::create($data)                    │
│  → version_uuid auto-generated, version_number=1      │
│  → is_current=true                                │
└──────────────┬──────────────────────────────┘
               │
               ▼
┌─────────────────┐
│  2. ACTIVE (Current Version)                 │
│  → Used by new approval requests                │
│  → ApprovalEngine uses rule_template_version_id    │
│  → Displayed as "v1 (Current)" in UI              │
└──────────────┬──────────────────────────────┘
               │
        ┌───────┴────────┐
        │                │
        ▼                ▼
┌───────────────┐  ┌───────────────┐
│  3a. EDIT RULE │  │  3b. DEACTIVATE  │
│  Click "Edit"  │  │  Click "Deactivate"│
│  → Creates v2  │  │  → active=false    │
│  → v1: is_current=false │  │  → No new requests │
│  → v2: is_current=true │  │  → Still in history│
└───────────────┘  └───────────────┘
        │
        ▼ (after edit)
┌─────────────────┐
│  4. NEW VERSION CREATED (v2)             │
│  → v1 preserved (old approvals use it)        │
│  → v2 is now "current"                     │
│  → Steps cloned to v2                      │
│  → Old approvals still reference v1           │
└──────────────┬──────────────────────────────┘
               │
               ▼
┌─────────────────┐
│  5. APPROVAL REQUEST USES SPECIFIC VERSION    │
│  Request created with v1 → rule_template_version_id=1 │
│  Request created with v2 → rule_template_version_id=3 │
│  → Immutable reference (never breaks)            │
└──────────────┬──────────────────────────────┘
               │
        ┌───────┴────────┐
        │                │
        ▼                ▼
┌───────────────┐  ┌───────────────┐
│  6a. SOFT DELETE │  │  6b. RESTORE    │
│  Click "Delete" │  │  Click "Restore"  │
│  → deleted_at set │  │  → deleted_at=null │
│  → Hidden from UI│  │  → Visible again  │
│  → Can restore   │  │  → Version history│
└───────────────┘  └───────────────┘
               │
               ▼
┌─────────────────┐
│  7. HARD DELETE (GDPR Purge)              │
│  → forceDeleteRulePermanently()            │
│  → Permanently removes record             │
│  → Only after soft delete                  │
│  → Use for GDPR compliance                │
└─────────────────┘
```

## Detailed Workflows

### 1. Creating a New Rule

**User Action:** Fill form → Click "Save Rule"

**System Flow:**

```php
// RuleManager::saveRule()
$data = [
    'model_type' => 'App\Models\PurchaseRequest',
    'code' => 'PR-GENERAL',
    'name' => 'General PR Approval',
    // ... other fields
];

$rule = RuleTemplate::create($data);
// Trait auto-generates: version_uuid, version_number=1, is_current=true

session()->flash('success', 'Rule created.');
```

**Database State:**

```sql
INSERT INTO approvals_rule_templates (
    id, version_uuid, version_number, is_current, model_type, code, name, ...
) VALUES (
    1, 'abc-123', 1, true, 'App\Models\PurchaseRequest', 'PR-GENERAL', ...
);
```

---

### 2. Editing a Rule (Versioning in Action)

**User Action:** Select rule → Click "Edit" → Modify fields → Click "Save Rule"

**System Flow:**

```php
// RuleManager::saveRule() WITH editingRuleId
$currentRule = RuleTemplate::findOrFail($this->editingRuleId);

// Check active approvals using ANY version of this rule
$activeRequests = ApprovalRequest::where('rule_template_id', $currentRule->version_uuid)
    ->whereNotIn('status', ['APPROVED', 'CANCELED', 'REJECTED'])
    ->count();

if ($activeRequests > 0) {
    // Warn user, but allow version creation
    $this->dispatch('confirm-new-version', [...]);
    return;
}

// Create NEW version (old remains unchanged)
$newVersion = $currentRule->createNewVersion($data, auth()->id());

// Inside Versionable::createNewVersion():
    // 1. Mark current as not current
    $currentRule->update(['is_current' => false]);

    // 2. Create new record
    $newVersion = static::create([
        'version_uuid' => $currentRule->version_uuid,  // Same group
        'version_number' => $currentRule->version_number + 1,  // v2
        'is_current' => true,
        'parent_version_id' => $currentRule->id,  // Link to v1
        // ... other fields from $data
    ]);

    // 3. Clone related steps
    foreach ($currentRule->steps as $step) {
        $newVersion->steps()->create([...clone step data...]);
    }

session()->flash('success', "New version (v{$newVersion->version_number}) created!");
```

**Database State After Edit:**

```sql
-- v1 (old, preserved)
id=1, version_uuid='abc-123', version_number=1, is_current=false, ...

-- v2 (new, current)
id=3, version_uuid='abc-123', version_number=2, is_current=true, parent_version_id=1, ...
```

---

### 3. Submitting Approval (Stability Guarantee)

**User Action:** Submit a Purchase Request

**System Flow:**

```php
// ApprovalEngine::submit()
$modelType = get_class($approvable); // e.g., "App\Models\PurchaseRequest"

// Resolve CURRENT version of the rule
$tpl = $this->resolver->resolveFor($modelType, $ctx);

// Get the CURRENT active version
$currentVersion = RuleTemplate::where('version_uuid', $tpl->version_uuid)
    ->where('is_current', true)
    ->first();

// Create approval request with IMMUTABLE version reference
$req = $approvable->approvalRequest()->create([
    'rule_template_id' => $currentVersion->version_uuid,  // Group reference
    'rule_template_version_id' => $currentVersion->id,  // SPECIFIC version (immutable)
    'status' => 'IN_REVIEW',
    // ...
]);

// Snapshot steps from THIS version
foreach ($currentVersion->steps as $s) {
    $req->steps()->create([...]);
}
```

**Result:**

- Approval request #42 references `rule_template_version_id = 3` (v2)
- Even if v3 is created later, request #42 still uses v2's steps
- **Stability guaranteed**

---

### 4. Deactivating a Rule

**User Action:** Select rule → Click "Deactivate"

**System Flow:**

```php
// RuleManager::deactivateRule($id)
$rule = RuleTemplate::findOrFail($id);
$rule->update(['active' => false]);

// Effect:
// - New approval requests WON'T use this rule
// - Existing approvals still work (reference specific version)
// - Rule still appears in version history
```

---

### 5. Soft Deleting a Rule

**User Action:** Select rule → Click "Delete" → Confirm deletion

**System Flow:**

```php
// RuleManager::deleteRule($id)
$rule = RuleTemplate::findOrFail($id);

// Check active approvals
$activeRequests = ApprovalRequest::where('rule_template_id', $rule->version_uuid)
    ->whereNotIn('status', ['APPROVED', 'CANCELED', 'REJECTED'])
    ->count();

if ($activeRequests > 0) {
    // Warn user about active approvals
    $this->dispatch('confirm-delete-rule', [...]);
    return;
}

// Soft delete
$rule->steps()->delete();  // Soft delete steps
$rule->delete();  // Sets deleted_at

// Effect:
// - Rule hidden from UI (withTrashed() to see it)
// - Can be restored with $rule->restore()
// - Version history preserved
```

---

### 6. Restoring a Soft-Deleted Rule

**User Action:** (Admin) Restore from trash

**System Flow:**

```php
// RuleManager::restoreRule($id)
$rule = RuleTemplate::withTrashed()->findOrFail($id);
$rule->restore();  // Clears deleted_at

// If this was the current version, it becomes active again
```

---

### 7. Hard Delete (GDPR Purge)

**User Action:** Permanently remove old version

**System Flow:**

```php
// RuleManager::forceDeleteRulePermanently($id)
$rule = RuleTemplate::withTrashed()->findOrFail($id);

// Check if any approvals still reference this version
$inUse = ApprovalRequest::where('rule_template_version_id', $rule->id)->exists();

if ($inUse) {
    throw new \Exception('Cannot permanently delete: approvals still reference this version.');
}

// Permanently delete
$rule->steps()->forceDelete();  // Permanent
$rule->forceDelete();  // Permanent

// Use for GDPR compliance or purging old data
```

## State Transition Table

| Current State | Action        | Next State                                  | Notes                                |
| ------------- | ------------- | ------------------------------------------- | ------------------------------------ |
| **None**      | Create        | v1, is_current=true                         | Initial creation                     |
| v1 (current)  | Edit          | v1: is_current=false<br>v2: is_current=true | New version created                  |
| v1 (current)  | Deactivate    | v1: active=false, is_current=true           | Still current version, just disabled |
| v2 (current)  | Restore to v1 | v1: is_current=true<br>v2: is_current=false | Rollback to old version              |
| Any version   | Soft Delete   | deleted_at set                              | Can restore later                    |
| Soft-deleted  | Restore       | deleted_at=null                             | Back to active                       |
| Soft-deleted  | Hard Delete   | **REMOVED**                                 | Permanent, use for GDPR              |

## Key Guarantees

### 1. Immutability

> "Once an approval request is created, the rule version it references NEVER changes."

-- Approval request #42 created with v2
-- Even if v3, v4, v5 are created, request #42 still uses v2's logic

### 2. Version Isolation

> "Edits create NEW versions; old versions remain unchanged."

```php
// v1 has steps: [Step 1: Manager, Step 2: Director]
// Edit v1 → Creates v2
// v2 has steps: [Step 1: Manager, Step 2: VP, Step 3: Director]
// v1 still has old steps (for old approvals)
```

### 3. Recoverability

> "Soft delete allows recovery; hard delete is permanent."

| Operation   | Reversible?          | Use Case            |
| ----------- | -------------------- | ------------------- |
| Soft Delete | ✅ Yes (`restore()`) | Accidental deletion |
| Hard Delete | ❌ No                | GDPR purge, cleanup |

## Integration Points

### With ApprovalEngine

```php
// ApprovalEngine MUST use specific version
$currentVersion = RuleTemplate::where('version_uuid', $tpl->version_uuid)
    ->where('is_current', true)
    ->first();

$req->rule_template_version_id = $currentVersion->id;  // IMMUTABLE
```

### With RuleManager UI

```blade
{{-- Show version badge --}}
<span class="badge">
    v{{ $rule->version_number }}
    @if($rule->is_current) (Current) @endif
</span>

{{-- Show version history --}}
@foreach($rule->allVersions as $version)
    <li>{{ $version->version_number }} - {{ $version->name }}</li>
@endforeach
```

### With API/External Systems

```php
// API response includes version info
return [
    'id' => $rule->id,  // Specific version ID
    'version_uuid' => $rule->version_uuid,  // Group ID
    'version_number' => $rule->version_number,  // Version number
    'is_current' => $rule->is_current,  // Is this the latest?
];
```

## Troubleshooting

### Issue: "Version not found" error

**Cause:** `rule_template_version_id` references deleted version.
**Fix:** Use `withTrashed()` when querying:

```php
$request = ApprovalRequest::withTrashed()
    ->where('id', $requestId)
    ->first();
```

### Issue: Multiple "current" versions

**Cause:** Race condition or manual DB edit.
**Fix:** Ensure only one is current:

```php
RuleTemplate::where('version_uuid', $uuid)
    ->update(['is_current' => false]);

RuleTemplate::where('id', $correctId)
    ->update(['is_current' => true]);
```

### Issue: Steps not cloned to new version

**Cause:** `cloneRelatedToVersion()` not implemented.
**Fix:** Implement in model:

```php
protected function cloneRelatedToVersion($newVersion): void
{
    foreach ($this->steps as $step) {
        $newVersion->steps()->create([...clone step...]);
    }
}
```
