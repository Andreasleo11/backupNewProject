# Versioning Implementation Guide

## Overview

This document describes the versioning implementation for the Rule Template system and how to apply it to other modules.

## What Was Implemented

### 1. Reusable Versionable Trait

**Location:** `app/Infrastructure/Support/Traits/Versionable.php`

The `Versionable` trait provides:

- Automatic `version_uuid` generation on creation
- Version numbering (1, 2, 3...)
- Current version tracking (`is_current` flag)
- Methods: `createNewVersion()`, `restoreToVersion()`, `diffAgainst()`
- Relationships: `allVersions()`, `parentVersion()`, `currentVersion()`

### 2. Database Changes

#### Rule Templates Table (`approvals_rule_templates`)

New columns added:

- `version_uuid` (UUID) - Groups all versions of same rule
- `version_number` (int) - Version number within group
- `is_current` (boolean) - Is this the active version?
- `parent_version_id` (foreign key) - Link to previous version
- `version_notes` (text) - Why was this version created?
- `created_by` (foreign key) - Who created this version

#### Approval Requests Table (`approval_requests`)

New column added:

- `rule_template_version_id` (foreign key) - References SPECIFIC version

### 3. Updated Models

#### RuleTemplate

- Uses `Versionable` trait
- Implements `cloneRelatedToVersion()` to clone steps
- Implements `getVersionedFields()` to specify versioned fields

### 4. Updated Logic

#### RuleManager (Livewire Component)

- SaveRule() now creates NEW versions instead of updating in-place
- Shows confirmation dialog if active approval requests exist
- Supports `forceCreateNewVersion()` for override

#### ApprovalEngine

- Now references specific version via `rule_template_version_id`
- Gets current active version when submitting approval requests
- Existing approvals remain stable (reference immutable version)

## How to Apply Versioning to Other Modules

### Step 1: Create Migration

```bash
php artisan make:migration add_versioning_to_[table]_table --table=[table]
```

Migration template:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('[TABLE_NAME]', function (Blueprint $table) {
            $table->uuid('version_uuid')->nullable()->after('id');
            $table->unsignedInteger('version_number')->default(1)->after('version_uuid');
            $table->boolean('is_current')->default(true)->after('version_number');
            $table->foreignId('parent_version_id')->nullable()->constrained('[TABLE_NAME]')->nullOnDelete();
            $table->text('version_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['version_uuid', 'version_number']);
            $table->index(['version_uuid', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::table('[TABLE_NAME]', function (Blueprint $table) {
            $table->dropIndex(['version_uuid', 'is_current']);
            $table->dropIndex(['version_uuid', 'version_number']);
            $table->dropForeign(['parent_version_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['version_uuid', 'version_number', 'is_current', 'parent_version_id', 'version_notes', 'created_by']);
        });
    }
};
```

### Step 2: Initialize Existing Data

Create a migration to assign version_uuid to existing records:

```php
// database/migrations/xxxx_add_versioning_to_[table]_initialize.php
public function up(): void
{
    DB::statement("
        UPDATE [TABLE_NAME]
        SET version_uuid = UUID(),
            version_number = 1,
            is_current = 1
        WHERE version_uuid IS NULL
    ");
}
```

### Step 3: Update Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Infrastructure\Support\Traits\Versionable;

class YourModel extends Model
{
    use Versionable;

    protected $fillable = [
        // Your existing fillable fields...
        'version_uuid', 'version_number', 'is_current',
        'parent_version_id', 'version_notes', 'created_by'
    ];

    /**
     * Specify which fields are versioned
     */
    protected function getVersionedFields(): array
    {
        return ['field1', 'field2', 'field3']; // List versioned fields
        // OR return ['*'] to version all fillable
    }

    /**
     * Clone related records when creating new version
     */
    protected function cloneRelatedToVersion($newVersion): void
    {
        foreach ($this->relatedItems as $item) {
            $newVersion->relatedItems()->create($item->toArray());
        }
    }
}
```

### Step 4: Update Business Logic

When editing, create new version instead of updating:

```php
// Instead of: $model->update($data);
$newVersion = $model->createNewVersion($data, auth()->id());

// Optionally add version notes
$newVersion->update(['version_notes' => 'Fixed bug in calculation']);
```

### Step 5: Update References

If other tables reference this model, add a `version_id` column to reference specific version:

```php
// In migration for the referencing table
$table->foreignId('your_model_version_id')->nullable();
$table->foreign('your_model_version_id')->references('id')->on('your_table')->nullOnDelete();
```

## Testing Checklist

- [ ] New records get auto-generated `version_uuid`
- [ ] Editing creates new version (old version preserved)
- [ ] `is_current` flag updates correctly
- [ ] `allVersions()` returns all versions
- [ ] `restoreToVersion()` works correctly
- [ ] `diffAgainst()` shows differences
- [ ] Related records are cloned to new version
- [ ] Existing references still work (backward compatible)

## Benefits

1. **Immutability**: Active workflows never break
2. **Audit Trail**: Complete history of changes
3. **Rollback**: Can restore to any previous version
4. **Comparison**: Can diff between versions
5. **Reusability**: Trait can be applied to any model

## Files Modified/Created

1. `app/Infrastructure/Support/Traits/Versionable.php` (CREATED)
2. `app/Infrastructure/Persistence/Eloquent/Models/RuleTemplate.php` (UPDATED)
3. `database/migrations/2026_04_24_102939_add_versioning_to_rule_templates_table.php` (CREATED)
4. `database/migrations/2026_04_24_103030_initialize_versioning_data.php` (CREATED)
5. `app/Livewire/Admin/Approvals/RuleManager.php` (UPDATED)
6. `app/Infrastructure/Approval/Services/ApprovalEngine.php` (UPDATED)
7. `resources/views/livewire/admin/approvals/rule-manager.blade.php` (UPDATED)
8. `resources/views/livewire/admin/approvals/_rule-card.blade.php` (UPDATED)
