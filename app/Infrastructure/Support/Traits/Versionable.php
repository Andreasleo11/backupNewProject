<?php

namespace App\Infrastructure\Support\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait Versionable
{
    /**
     * Boot the versionable trait
     */
    protected static function bootVersionable(): void
    {
        static::creating(function ($model) {
            // Auto-generate version_uuid if not set
            if (empty($model->version_uuid)) {
                $model->version_uuid = (string) Str::uuid();
                $model->version_number = 1;
                $model->is_current = true;
            }
        });
    }

    /**
     * Get all versions of this entity
     */
    public function allVersions(): HasMany
    {
        return $this->hasMany(static::class, 'version_uuid', 'version_uuid')
            ->orderBy('version_number', 'desc');
    }

    /**
     * Get the parent (previous) version
     */
    public function parentVersion()
    {
        return $this->belongsTo(static::class, 'parent_version_id');
    }

    /**
     * Get the child (next) version
     */
    public function childVersion()
    {
        return $this->hasOne(static::class, 'parent_version_id');
    }

    /**
     * Get the current version in this version group
     */
    public function currentVersion()
    {
        return $this->hasOne(static::class, 'version_uuid', 'version_uuid')
            ->where('is_current', true);
    }

    /**
     * Scope: only current versions
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope: by version group
     */
    public function scopeForVersionGroup($query, $versionUuid)
    {
        return $query->where('version_uuid', $versionUuid);
    }

    /**
     * Create a new version of this entity
     */
    public function createNewVersion(array $attributes, int $createdBy, array $excludeFromClone = []): self
    {
        return DB::transaction(function () use ($attributes, $createdBy, $excludeFromClone) {
            // Mark current version as not current
            $this->update(['is_current' => false]);

            // Prepare new version data
            $fillable = array_diff($this->getFillable(), $excludeFromClone);
            $newData = [];

            foreach ($fillable as $field) {
                if (isset($attributes[$field])) {
                    $newData[$field] = $attributes[$field];
                } else {
                    $newData[$field] = $this->$field;
                }
            }

            $newData['version_uuid'] = $this->version_uuid;
            $newData['version_number'] = $this->version_number + 1;
            $newData['is_current'] = true;
            $newData['parent_version_id'] = $this->id;
            $newData['created_by'] = $createdBy;

            // Handle version notes
            if (isset($attributes['version_notes'])) {
                $newData['version_notes'] = $attributes['version_notes'];
            }

            // Create new version
            $newVersion = static::create($newData);

            // Clone related items if method exists
            if (method_exists($this, 'cloneRelatedToVersion')) {
                $this->cloneRelatedToVersion($newVersion);
            }

            return $newVersion;
        });
    }

    /**
     * Override this method in your model to clone related records
     */
    protected function cloneRelatedToVersion($newVersion): void
    {
        // Override in model, e.g., clone steps for RuleTemplate
    }

    /**
     * Get diff between this version and another
     */
    public function diffAgainst(self $other): array
    {
        $fieldsToCompare = $this->getVersionedFields();
        $diff = [];

        if ($fieldsToCompare === ['*']) {
            $fieldsToCompare = $this->getFillable();
        }

        foreach ($fieldsToCompare as $field) {
            if (! isset($this->$field) && ! isset($other->$field)) {
                continue;
            }

            if ($this->$field !== $other->$field) {
                $diff[$field] = [
                    'old' => $other->$field ?? null,
                    'new' => $this->$field ?? null,
                ];
            }
        }

        return $diff;
    }

    /**
     * Override this to specify which fields are versioned
     */
    protected function getVersionedFields(): array
    {
        return ['*']; // Version all fillable by default
    }

    /**
     * Restore to a specific version
     */
    public function restoreToVersion(int $versionId): self
    {
        $targetVersion = static::findOrFail($versionId);

        if ($targetVersion->version_uuid !== $this->version_uuid) {
            throw new \InvalidArgumentException('Cannot restore to a different version group.');
        }

        // Mark all versions as not current
        static::forVersionGroup($this->version_uuid)->update(['is_current' => false]);

        // Mark target as current
        $targetVersion->update(['is_current' => true]);

        return $targetVersion;
    }
}
