# Versioning Strategy - Approval Rule Template

## Purpose

The `Versionable` trait provides **immutable versioning** for Eloquent models, ensuring that edits never break active workflows.

## How It Works

### 1. Key Concepts

| Concept             | Description                                    |
| ------------------- | ---------------------------------------------- |
| `version_uuid`      | Groups all versions of the same logical entity |
| `version_number`    | Sequential number within group (1, 2, 3...)    |
| `is_current`        | Boolean flag marking the "active" version      |
| `parent_version_id` | FK to previous version (forms linked list)     |

### 2. Trait: `Versionable`

**Location:** `app/Infrastructure/Support/Traits/Versionable.php`

**Key Methods:**

```php
// Create new version (old remains unchanged)
$newVersion = $model->createNewVersion($attributes, $createdBy);

// Restore to a previous version
$model->restoreToVersion($versionId);

// Compare two versions
$diff = $currentVersion->diffAgainst($oldVersion);

// Query relationships
$model->allVersions();       // All versions
$model->currentVersion();    // The active version
$model->parentVersion();     // Previous version
```

### 3. Version Creation Flow

```
User clicks "Edit Rule"
    ↓
RuleManager::saveRule() WITH editingRuleId
    ↓
Check active approval requests using rule (by version_uuid)
    ↓
YES (active requests exist)?
  ├─ YES → Show confirmation dialog
  │            ↓
  │         Force create?
  │            ├─ YES → createNewVersion() (active requests keep old version)
  │            └─ NO  → Cancel
  │
  └─ NO  → createNewVersion() directly
              ↓
          1. Mark current version: is_current = false
          2. Create NEW record with:
             - version_uuid = same as old (groups them)
             - version_number = old + 1
             - is_current = true
             - parent_version_id = old.id
          3. Clone related records (steps) via cloneRelatedToVersion()
```

### 4. Database State After Editing

```sql
-- Before edit:
SELECT id, version_uuid, version_number, is_current, name
FROM approvals_rule_templates
WHERE version_uuid = 'abc-123';

-- Result:
id | version_uuid | version_number | is_current | name
1  | abc-123     | 1             | 1          | "OT Rule v1"

-- After edit (creates new version):
id | version_uuid | version_number | is_current | name
1  | abc-123     | 1             | 0          | "OT Rule v1"  ← Old (not current)
3  | abc-123     | 2             | 1          | "OT Rule v2"  ← NEW (current)
```

### 5. Usage in Approval Workflow

**ApprovalEngine::submit():**

```php
// Always use the CURRENT version for new submissions
$currentVersion = RuleTemplate::where('version_uuid', $tpl->version_uuid)
    ->where('is_current', true)
    ->first();

// Store SPECIFIC version ID (immutable reference)
$req->rule_template_version_id = $currentVersion->id;
```

**Result:** Old approval requests reference `rule_template_version_id` = 1 (v1), even though v2 is now current.

## Implementation Details

### Step 1: Add Trait to Model

```php
class RuleTemplate extends Model
{
    use Versionable;  // ← Add this

    protected $fillable = [
        'model_type', 'code', 'name', 'active',
        'version_uuid', 'version_number', 'is_current', 'parent_version_id'
    ];

    // Define which fields to track in diff
    protected function getVersionedFields(): array
    {
        return ['model_type', 'code', 'name', 'active', 'priority', 'match_expr'];
    }

    // Clone related records when creating new version
    protected function cloneRelatedToVersion($newVersion): void
    {
        foreach ($this->steps as $step) {
            $newVersion->steps()->create([
                'sequence' => $step->sequence,
                'approver_type' => $step->approver_type,
                // ...
            ]);
        }
    }
}
```

### Step 2: Database Migration

```php
Schema::table('approvals_rule_templates', function (Blueprint $table) {
    $table->uuid('version_uuid')->nullable();
    $table->unsignedInteger('version_number')->default(1);
    $table->boolean('is_current')->default(true);
    $table->foreignId('parent_version_id')->nullable()->constrained('approvals_rule_templates');
});
```

## Best Practices

### 1. When to Create New Version vs. Update In-Place

| Scenario                            | Action                         | Reason                                |
| ----------------------------------- | ------------------------------ | ------------------------------------- |
| Edit rule logic (steps, conditions) | **Create new version**         | Preserve active approvals             |
| Fix typo in name/description        | **Update in-place** (optional) | Low risk, or create version if strict |
| Emergency disable                   | **Set active=false**           | No need for new version               |

### 2. Querying Versions

```php
// Get all versions of a rule
$rule = RuleTemplate::find(1);
$allVersions = $rule->allVersions;  // Collection of all versions

// Get current version only
$current = RuleTemplate::current()->where('model_type', $modelType)->first();

// Check if specific version is current
if ($rule->is_current) {
    echo "This is the active version";
}
```

### 3. Restoring Versions

```php
// User wants to rollback to version 1
$currentVersion = RuleTemplate::find(3);  // v2
$currentVersion->restoreToVersion(1);  // Restores v1 as current

// Result: v1 becomes is_current=true, v2 becomes is_current=false
```

## Comparison with Alternatives

| Approach                             | Pros                                                                              | Cons                                                            |
| ------------------------------------ | --------------------------------------------------------------------------------- | --------------------------------------------------------------- |
| **Immutable Versioning (Current)**   | -Active approvals never break<br>- Complete audit trail<br>- Can rollback/compare | - More storage (multiple records)<br>- Slightly complex queries |
| **In-Place Updates**                 | - Simple<br>- Single record                                                       | - Breaks active approvals<br>- No history                       |
| **Separate Audit Table**             | - Clean main table<br>- Full history                                              | - Complex queries<br>- No easy "current" flag                   |
| **JSON Snapshot in ApprovalRequest** | - Approval self-contained                                                         | - No rule evolution tracking<br>- Redundant storage             |

## Common Operations

### Creating a New Version (Livewire)

```php
// In RuleManager.php
public function saveRule(): void
{
    if ($this->editingRuleId) {
        $currentRule = RuleTemplate::findOrFail($this->editingRuleId);

        // Create new version (old remains unchanged)
        $newVersion = $currentRule->createNewVersion([
            'model_type' => $this->rule_model_type,
            'code' => $this->rule_code,
            'name' => $this->rule_name,
            // ... other fields
        ], auth()->id());

        session()->flash('success', "New version (v{$newVersion->version_number}) created!");
    }
}
```

### Comparing Versions

```php
$v2 = RuleTemplate::find(3);  // Version 2
$v1 = RuleTemplate::find(1);  // Version 1

$diff = $v2->diffAgainst($v1);
// Result: ['name' => ['old' => 'OT v1', 'new' => 'OT v2'], ...]
```

### Checking Active Approvals Before Versioning

```php
$rule = RuleTemplate::find(1);
$activeRequests = \App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest::where('rule_template_id', $rule->version_uuid)
    ->whereNotIn('status', ['APPROVED', 'CANCELLED', 'REJECTED'])
    ->count();

if ($activeRequests > 0) {
    // Warn user, but allow version creation
    // Old approvals will continue using OLD version
}
```

## GDPR & Data Retention

### Soft Delete vs. Hard Delete

| Operation       | Method                 | Effect                                             |
| --------------- | ---------------------- | -------------------------------------------------- |
| **Soft Delete** | `$rule->delete()`      | Sets `deleted_at`, hides from queries, can restore |
| **Hard Delete** | `$rule->forceDelete()` | Permanently removes record                         |
| **Restore**     | `$rule->restore()`     | Clears `deleted_at`                                |

### Purging Old Versions (GDPR)

```php
// Keep only last 5 versions per rule group
$versionGroups = RuleTemplate::select('version_uuid')->distinct()->get();

foreach ($versionGroups as $group) {
    $versions = RuleTemplate::where('version_uuid', $group->version_uuid)
        ->orderBy('version_number', 'desc')
        ->get();

    if ($versions->count() > 5) {
        $toDelete = $versions->slice(5);

        foreach ($toDelete as $version) {
            // Check if any approval requests still use this version
            $inUse = ApprovalRequest::where('rule_template_version_id', $version->id)->exists();

            if (!$inUse && !$version->is_current) {
                $version->forceDelete();  // Permanent delete for GDPR
            }
        }
    }
}
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

## Testing Checklist

- [ ] New records get auto-generated `version_uuid`
- [ ] Editing creates NEW version (old preserved)
- [ ] `is_current` flag updates correctly
- [ ] Steps are cloned to new version
- [ ] Active approvals reference IMMUTABLE version
- [ ] `restoreToVersion()` works
- [ ] `diffAgainst()` shows correct differences
- [ ] Soft delete works, restore works
