# Documentation-as-Code: Contributing Guide

## Overview

This project uses a **documentation-as-code** strategy where documentation lives alongside code, is version-controlled, and evolves with every feature update.

## Core Principles

1. **Code & Docs Together**: Every feature update includes documentation updates
2. **Version Controlled**: Docs are committed alongside code in the same PR
3. **Living Documentation**: Updated incrementally, never stale
4. **Reviewable**: Documentation is part of code review
5. **Module-Based**: Each module has its own documentation folder

## Directory Structure

```
docs/
├── architecture/              # System-wide architecture docs
│   └── overview.md
├── modules/                  # Per-module documentation
│   ├── approval-rule-template/   ← Pilot module
│   │   ├── README.md            ← Main documentation
│   │   ├── versioning.md      ← Deep dive: versioning
│   │   └── api-reference.md   ← API/Method reference
│   ├── purchase-request/
│   │   └── README.md
│   └── ... (future modules)
├── workflows/               # Process workflows
│   └── approval-rule-lifecycle.md
└── contributing.md           ← This file
```

## Documentation Requirements by Change Type

### 1. New Feature/Module

**Required Documentation:**

```
docs/modules/{module-name}/
├── README.md              # Main doc (architecture, data model, workflow)
├── versioning.md         # If uses versioning (copy from pilot)
├── api-reference.md      # All public methods/endpoints
└── workflows/           # Any new workflows
```

**Checklist:**

- [ ] Create `docs/modules/{module-name}/` directory
- [ ] Document architecture (components, relationships)
- [ ] Document data model (models, traits, attributes)
- [ ] Document workflow (diagrams: create → update → delete)
- [ ] Add to `docs/architecture/overview.md`
- [ ] Update `docs/modules/{module-name}/README.md` with links to related docs

### 2. Feature Update (Existing Module)

**Required Updates:**

- [ ] Update `docs/modules/{module}/README.md` (new features, changed behavior)
- [ ] Update `docs/modules/{module}/api-reference.md` (new methods, changed signatures)
- [ ] Update `docs/workflows/` if workflow changes
- [ ] Add "Version History" section to track changes

**Example:** Adding a new field to RuleTemplate:

```markdown
## Version History

- **2026-04-24**: Added `version_notes` field to RuleTemplate
  - Supports versioning workflow
  - Added to `Versionable` trait's `cloneRelatedToVersion()`
```

### 3. Bug Fix

**Required Updates:**

- [ ] Update affected documentation to reflect corrected behavior
- [ ] Add "Known Issues" or "Fixed Issues" section if applicable

### 4. Refactoring (No Behavior Change)

**Required Updates:**

- [ ] Update documentation if structure/architecture changes
- [ ] Ensure code examples in docs still work

## Documentation Standards

### 1. Use Markdown

All docs are in Markdown (`.md`) for version control readability.

### 2. Include Code Examples

```php
// Good: Concrete, copy-pasteable example
$newVersion = $rule->createNewVersion($data, auth()->id());
```

### 3. Use Diagrams

```
Use ASCII diagrams for architecture:
┌────────────────┐
│   Component    │
└────────┬───────┘
             │ uses
             ▼
┌────────────────┐
│   Service    │
└────────────────┘
```

### 4. Document Methods with Parameters

| Method               | Parameters                   | Description               |
| -------------------- | ---------------------------- | ------------------------- |
| `createNewVersion()` | `array $data`, `int $userId` | Creates immutable version |

### 5. Keep it Concise

- **Avoid**: Long paragraphs of text
- **Prefer**: Bullet points, tables, diagrams

## Integration into Development Lifecycle

### Step 1: Start with Documentation (Optional but Recommended)

```
1. Create feature branch: git checkout -b feature/new-rule-field
2. Write documentation FIRST (docs/modules/rule-template/... .md)
3. Implement code
4. Update docs if needed while coding
```

### Step 2: Implement Code + Docs Together

```
# Good commit
git add app/Infrastructure/Support/Traits/Versionable.php
git add docs/modules/approval-rule-template/versioning.md
git commit -m "feat: add versioning support with docs"
```

### Step 3: Include Docs in PR Review

**PR Template (`.github/pull_request_template.md`):**

```markdown
## Documentation Updates

- [ ] Updated `docs/modules/{module}/README.md`
- [ ] Updated API reference (if applicable)
- [ ] Added architectural diagrams (if new components)
- [ ] Updated `docs/architecture/overview.md` (if system-wide change)
```

### Step 4: Enforce via Git Hooks (Optional)

Create `.git/hooks/pre-commit`:

```bash
#!/bin/bash
# Check if docs were modified in this commit
if ! git diff --cached --name-only | grep -q "^docs/"; then
    echo "WARNING: No documentation changes detected."
    echo "Consider updating docs/ for your changes."
    # Uncomment to enforce:
    # exit 1
fi
```

## Module Documentation Template

When creating documentation for a new module, use this template:

```markdown
# {Module Name} - Module Documentation

## Overview

{Brief description of what this module does}

## Architecture

{Components, relationships, ASCII diagram}

## Key Files

| File      | Purpose |
| --------- | ------- |
| `app/...` | ...     |

## Data Model

{Model classes, attributes, relationships}

## Workflow

{How to create, update, delete - with diagrams}

## API Reference

{Public methods, parameters, return values}

## Database Schema

{Table structure, columns, indexes}

## Version History

- **YYYY-MM-DD**: {Change description}
```

## Pilot: Approval Rule Template

The **Approval Rule Template** is our pilot module for documentation-as-code.

**Completed:**

- [x] `docs/modules/approval-rule-template/README.md` - Main documentation
- [x] `docs/modules/approval-rule-template/versioning.md` - Deep dive
- [x] `docs/workflows/approval-rule-lifecycle.md` - Workflow diagram
- [x] `docs/architecture/overview.md` - System architecture

**Next Modules to Document:**

- [ ] Purchase Request (`docs/modules/purchase-request/`)
- [ ] Overtime Form (`docs/modules/overtime-form/`)
- [ ] Approval Engine (`docs/modules/approval-engine/`)

## Incremental Updates with Version Control

### Scenario: Adding a New Field to RuleTemplate

**1. Create Feature Branch**

```bash
git checkout -b feature/add-rule-category
```

**2. Update Database**

```bash
# Create migration
php artisan make:migration add_category_to_rule_templates
# Update migration file...
```

**3. Update Model**

```php
// app/Infrastructure/Persistence/Eloquent/Models/RuleTemplate.php
protected $fillable = [
    // ... existing fields
    'category',  // NEW FIELD
];
```

**4. Update Documentation (Same Commit!)**

```markdown
# docs/modules/approval-rule-template/README.md

## Data Model

| Attribute  | Type   | Description                                  |
| ---------- | ------ | -------------------------------------------- |
| `category` | string | NEW: Rule category (e.g., "FINANCIAL", "HR") |
```

**5. Commit Together**

```bash
git add app/Infrastructure/Persistence/Eloquent/Models/RuleTemplate.php
git add database/migrations/2026_04_24_add_category_to_rule_templates.php
git add docs/modules/approval-rule-template/README.md
git commit -m "feat: add category field to rule templates with docs"
```

## CI/CD Integration (Optional)

### GitHub Actions: Check Documentation

```yaml
# .github/workflows/docs-check.yml
name: Documentation Check

on: [pull_request]

jobs:
  check-docs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Check for docs updates
        run: |
          if git diff --name-only origin/main...HEAD | grep -q "^app/"; then
            if ! git diff --name-only origin/main...HEAD | grep -q "^docs/"; then
              echo "WARNING: Code changes without documentation updates!"
              exit 1
            fi
          fi
```

## Common Pitfalls & Solutions

### Pitfall 1: "I'll add docs later"

**Problem:** Docs never get written.
**Solution:** Include docs in definition of done (DoD).

### Pitfall 2: Outdated Code Examples

**Problem:** Code changes but docs have old examples.
**Solution:** Reviewers check that code and docs match.

### Pitfall 3: Too Much Documentation

**Problem:** Spending more time on docs than code.
**Solution:** Keep it concise; focus on architecture, not inline comments.

## Tools & Resources

### Generating Diagrams

- **ASCIIFlow**: https://asciiflow.com/ (for ASCII diagrams)
- **Mermaid**: https://mermaid.live/ (for GitHub-flavored diagrams)

### Linting Documentation

```bash
# Install markdown lint
npm install -g markdownlint-cli

# Lint docs
markdownlint docs/**/*.md
```

## Summary: Your Documentation Workflow

```
1. Pick up a ticket/feature
   ↓
2. Create feature branch
   ↓
3. Write/Update documentation FIRST (or alongside code)
   ↓
4. Implement code
   ↓
5. Update docs if behavior changed
   ↓
6. Commit code + docs TOGETHER
   ↓
7. PR includes documentation review
   ↓
8. Merge to main (docs versioned with code)
```

## Questions?

- **Q: How detailed should docs be?**
  A: Focus on architecture, workflow, and public APIs. Don't document obvious things.

- **Q: What if I'm just fixing a typo?**
  A: Small changes might not need doc updates. Use judgment.

- **Q: Can I use tools to generate docs?**
  A: Yes! Use PHPDoc for API reference, then enhance manually.

## Contributing Checklist

Before submitting a PR, ensure:

- [ ] Documentation exists for new modules/features
- [ ] Code examples in docs are correct and runnable
- [ ] Diagrams match current architecture
- [ ] API reference is up-to-date
- [ ] Version history is updated (if applicable)
- [ ] `docs/architecture/overview.md` updated (if system-wide change)
