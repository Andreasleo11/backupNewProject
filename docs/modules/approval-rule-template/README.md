# Approval Rule Template - Module Documentation

## Overview

The **Approval Rule Template** module defines reusable approval workflows for different document types in the system. It supports **immutable versioning**, allowing safe updates without breaking active approval requests.

## Key Features

- **Immutable Versioning**: Edits create new versions; old approvals remain stable
- **Soft Delete**: Recoverable deletion with `deleted_at`
- **Active/Inactive Control**: Businesslogic enable/disable independent of versioning
- **Step Definitions**: Configurable multi-step approval flows
- **Match Expressions**: JSON-based conditions to auto-match rules to documents

## Architecture

### Component Diagram

```
┌─────────────────────────────────────────────────────┐
│                   Livewire UI                       │
│  RuleManager.php (Component)                     │
│  - Filters, pagination, selection                  │
│  - saveRule(), deleteRule(), restoreRule()        │
└───────────────────┬─────────────────────────────┘
                    │ uses
                    ▼
┌─────────────────────────────────────────────────────┐
│              Infrastructure Layer                    │
│  RuleTemplate.php (Model + Versionable trait)      │
│  RuleStepTemplate.php (Steps + Versionable)        │
│  ApprovalRequest.php (references rule_version_id)    │
└───────────────────┬─────────────────────────────┘
                    │ called by
                    ▼
┌─────────────────────────────────────────────────────┐
│            ApprovalEngine.php (Service)                │
│  - submit(): Creates approval request from rule       │
│  - Uses specific version (rule_template_version_id)  │
└─────────────────────────────────────────────────────┘
```

### Key Files

| File                                                                  | Purpose                                 |
| --------------------------------------------------------------------- | --------------------------------------- |
| `app/Livewire/Admin/Approvals/RuleManager.php`                        | UI component: CRUD, versioning, filters |
| `app/Infrastructure/Persistence/Eloquent/Models/RuleTemplate.php`     | Model with Versionable trait            |
| `app/Infrastructure/Persistence/Eloquent/Models/RuleStepTemplate.php` | Step definitions                        |
| `app/Infrastructure/Support/Traits/Versionable.php`                   | Reusable versioning logic               |
| `app/Infrastructure/Approval/Services/ApprovalEngine.php`             | Orchestrates approval workflow          |
| `resources/views/livewire/admin/approvals/rule-manager.blade.php`     | Main UI view                            |

## Data Model

### RuleTemplate Model

```php
// app/Infrastructure/Persistence/Eloquent/Models/RuleTemplate.php
class RuleTemplate extends Model
{
    use SoftDeletes, Versionable;  // ← Key traits

    protected $fillable = [
        'model_type', 'code', 'name', 'active', 'priority', 'match_expr',
        'version_uuid', 'version_number', 'is_current', 'parent_version_id'
    ];

    // Relationships
    public function steps(): HasMany  // RuleStepTemplate
    public function allVersions(): HasMany  // All versions of this rule
    public function currentVersion(): HasOne  // The active version
    public function approvalRequests(): HasMany  // Requests using this rule
}
```

**Key Attributes:**
| Attribute | Type | Description |
|-----------|------|-------------|
| `model_type` | string | E.g., `App\Domain\Overtime\Models\OvertimeForm` |
| `code` | string | Unique code like "OT-GENERAL" |
| `name` | string | Human-readable name |
| `active` | boolean | Business logic: is rule enabled? |
| `match_expr` | json | Conditions: `{"department":"FIN", "amount_gt":1000000}` |
| `version_uuid` | UUID | Groups all versions of same rule |
| `version_number` | int | Version number within group (1, 2, 3...) |
| `is_current` | boolean | Is this the latest version? |
| `parent_version_id` | foreign key | Links to previous version |

### RuleStepTemplate Model

```php
class RuleStepTemplate extends Model
{
    use SoftDeletes, Versionable;

    protected $fillable = [
        'rule_template_id', 'sequence', 'approver_type',
        'approver_id', 'final', 'parallel_group'
    ];

    // Relationships
    public function rule(): BelongsTo  // Parent RuleTemplate
    public function user(): BelongsTo  // If approver_type = 'user'
    public function role(): BelongsTo  // If approver_type = 'role'
}
```

### Versionable Trait Methods

```php
// app/Infrastructure/Support/Traits/Versionable.php
trait Versionable {
    public function createNewVersion(array $attributes, int $createdBy): self
    public function restoreToVersion(int $versionId): self
    public function diffAgainst(self $other): array
    public function allVersions(): HasMany
    public function currentVersion(): HasOne
}
```

## Workflow: Creating & Using Rules

### 1. Creating a Rule (User Action)

```
User fills form →
RuleManager::saveRule() →
RuleTemplate::create($data) [version_uuid auto-generated, version_number=1, is_current=true]
```

### 2. Editing a Rule (Versioning in Action)

```
User edits rule →
RuleManager::saveRule() with editingRuleId →
RuleTemplate::createNewVersion($data, $userId) →
  1. Old version: is_current = false
  2. New version: version_number = old + 1, is_current = true
  3. Steps cloned to new version
```

### 3. Submitting an Approval (Stability Guarantee)

```
User submits document →
ApprovalEngine::submit($approvable, $userId) →
  1. Resolve current rule version: RuleTemplate::where('version_uuid', ...)->where('is_current', true)
  2. Create ApprovalRequest with:
     - rule_template_id = version_uuid (group reference)
     - rule_template_version_id = specific version ID (immutable)
  3. Snapshot steps from the CURRENT version
```

**Result:** Active approvals reference immutable version; editing creates new version without affecting them.

## Versioning Strategy

### What is Versioning?

- **Not in-place updates**: Editing creates NEW records
- **Immutable history**: Old versions preserved
- **Stability**: Active approvals never break

### Version Lifecycle

```
Version 1 (v1): created_at=2026-04-20, is_current=true    ← Active for new submissions
         ↓ edit rule
Version 2 (v2): created_at=2026-04-24, is_current=true    ← New active version
         ↓ old approvals still reference v1 via rule_template_version_id
```

### Database Migration

```php
// database/migrations/2026_04_24_102939_add_versioning_to_rule_templates_table.php
Schema::table('approvals_rule_templates', function (Blueprint $table) {
    $table->uuid('version_uuid')->nullable();
    $table->unsignedInteger('version_number')->default(1);
    $table->boolean('is_current')->default(true);
    $table->foreignId('parent_version_id')->nullable()->constrained('approvals_rule_templates');
    $table->index(['version_uuid', 'version_number']);
    $table->index(['version_uuid', 'is_current']);
});
```

## API Reference

### Livewire: RuleManager Methods

| Method                                  | Parameters | Description                                  |
| --------------------------------------- | ---------- | -------------------------------------------- |
| `saveRule()`                            | -          | Creates new rule OR new version (if editing) |
| `deleteRule($id)`                       | int $id    | Soft-deletes with active request check       |
| `restoreRule($id)`                      | int $id    | Restores soft-deleted rule                   |
| `forceCreateNewVersion($ruleId, $data)` | int, array | Forces new version even with active requests |
| `selectRule($id)`                       | int $id    | Selects rule for viewing                     |
| `openEditRule($id)`                     | int $id    | Opens edit modal with version notes          |

### Service: ApprovalEngine Methods

| Method                                    | Parameters             | Description                                         |
| ----------------------------------------- | ---------------------- | --------------------------------------------------- |
| `submit($approvable, $userId, $ctx)`      | Approvable, int, array | Creates approval request using CURRENT rule version |
| `approve($approvable, $userId, $remarks)` | -                      | Advances to next step                               |
| `reject($approvable, $userId, $remarks)`  | -                      | Rejects approval                                    |

## Database Schema

### Table: `approvals_rule_templates`

| Column              | Type            | Null | Default | Key                              | Description |
| ------------------- | --------------- | ---- | ------- | -------------------------------- | ----------- |
| `id`                | bigint          | NO   | -       | Primary key                      |
| `model_type`        | varchar(255)    | NO   | -       | E.g., App\Models\PurchaseRequest |
| `code`              | varchar(255)    | YES  | NULL    | Unique code                      |
| `name`              | varchar(255)    | NO   | -       | Rule name                        |
| `active`            | tinyint(1)      | NO   | 1       | Business enable/disable          |
| `priority`          | int unsigned    | NO   | 100     | Lower = higher priority          |
| `match_expr`        | json            | YES  | NULL    | JSON conditions                  |
| `version_uuid`      | uuid            | YES  | NULL    | Groups versions                  |
| `version_number`    | int unsigned    | NO   | 1       | Version in group                 |
| `is_current`        | tinyint(1)      | NO   | 1       | Latest version flag              |
| `parent_version_id` | bigint unsigned | YES  | NULL    | FK to self                       |
| `created_by`        | bigint unsigned | YES  | NULL    | User who created                 |
| `version_notes`     | text            | YES  | NULL    | Why was version created?         |
| `created_at`        | timestamp       | YES  | NULL    |                                  |
| `updated_at`        | timestamp       | YES  | NULL    |                                  |
| `deleted_at`        | timestamp       | YES  | NULL    | Soft delete                      |

**Indexes:**

- `approvals_rule_templates_model_type_active_index` (`model_type`, `active`)
- `approvals_rule_templates_version_uuid_version_number_index` (`version_uuid`, `version_number`)
- `approvals_rule_templates_version_uuid_is_current_index` (`version_uuid`, `is_current`)

### Table: `approvals_rule_step_templates`

| Column             | Type            | Null | Default | Key                            | Description |
| ------------------ | --------------- | ---- | ------- | ------------------------------ | ----------- |
| `id`               | bigint          | NO   | -       | Primary key                    |
| `rule_template_id` | bigint unsigned | NO   | -       | FK to approvals_rule_templates |
| `sequence`         | int             | NO   | -       | Step order (1, 2, 3...)        |
| `approver_type`    | varchar(255)    | NO   | -       | 'user' or 'role'               |
| `approver_id`      | int             | NO   | -       | User ID or Role ID             |
| `final`            | tinyint(1)      | NO   | 0       | Is this the final step?        |
| `parallel_group`   | tinyint(1)      | NO   | 0       | Can be processed in parallel?  |
| `created_at`       | timestamp       | YES  | NULL    |                                |
| `updated_at`       | timestamp       | YES  | NULL    |                                |
| `deleted_at`       | timestamp       | YES  | NULL    | Soft delete                    |

### Table: `approval_requests` (relevant columns)

| Column                     | Type            | Null | Default | Key                                  | Description |
| -------------------------- | --------------- | ---- | ------- | ------------------------------------ | ----------- |
| `rule_template_id`         | bigint unsigned | YES  | NULL    | FK to version group (UUID reference) |
| `rule_template_version_id` | bigint unsigned | YES  | NULL    | FK to SPECIFIC version               |

## How to Extend (Adding Versioning to Other Modules)

### Step 1: Create Migration

```bash
php artisan make:migration add_versioning_to_[table]_table --table=[table]
```

### Step 2: Add Trait to Model

```php
class YourModel extends Model
{
    use Versionable;  // ← Add this

    protected function getVersionedFields(): array
    {
        return ['field1', 'field2'];  // Fields to track in diff
    }

    protected function cloneRelatedToVersion($newVersion): void
    {
        // Clone related records (e.g., items, steps)
    }
}
```

### Step 3: Update Business Logic

```php
// Instead of: $model->update($data);
$newVersion = $model->createNewVersion($data, auth()->id());
```

See `docs/contributing.md` for full contributing guidelines.
