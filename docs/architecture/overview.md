# System Architecture Overview

## Introduction

This document provides a high-level overview of the system's architecture, focusing on modular design, data flow, and key architectural patterns.

## Core Architectural Patterns

### 1. Domain-Driven Design (DDD) Elements

- **Models**: `App\Domain\*` (e.g., `OvertimeForm`, `PurchaseRequest`)
- **Infrastructure**: `App\Infrastructure\*` - Persistence, external services
- **Application**: `App\Application\*` - Use cases, query builders
- **Livewire Components**: `App\Livewire\*` - UI controllers

### 2. Approval System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Domain Layer                        │
│  App\Domain\Overtime\Models\OvertimeForm          │
│  App\Models\PurchaseRequest                     │
│  App\Models\PurchaseOrder                        │
└──────────────────────┬──────────────────────────────┘
                       │ implements Approvable
                       ▼
┌─────────────────────────────────────────────────────────────┐
│              Infrastructure\Approval\Services              │
│  ApprovalEngine: Orchestrates approval workflow        │
│  ApprovalVisibilityScoper: Controls access              │
└──────────────────────┬──────────────────────────────┘
                       │ uses
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                Persistence\Eloquent\Models               │
│  ApprovalRequest (snapshot of approval state)            │
│  ApprovalStep (individual step tracking)               │
│  RuleTemplate (defines approval logic)                 │
│  RuleStepTemplate (individual step definition)           │
└─────────────────────────────────────────────────────────────┘
```

### 3. Key Design Patterns

- **Trait-Based Reusability**: `Versionable`, `SoftDeletes`
- **Repository Pattern**: Query builders in `App\Application\*`
- **Service Layer**: Business logic in `App\Infrastructure\*Services\`
- **Livewire for UI**: Reactive components with server-side state

## Module Documentation

Each module has its documentation in `docs/modules/{module-name}/`:

| Module                 | Documentation Path                          | Status      |
| ---------------------- | ------------------------------------------- | ----------- |
| Approval Rule Template | `docs/modules/approval-rule-template/`      | ✅ Piloting |
| Purchase Order         | `docs/modules/purchase-order/`              | ✅ Active   |
| Purchase Request       | `docs/modules/purchase-request/`            | 📝 Todo     |
| Overtime Form          | `docs/modules/overtime-form/`               | 📝 Todo     |

## Documentation-as-Code Workflow

1. **Code & Document Together**: Every feature update includes documentation updates
2. **Version Controlled**: Docs are committed alongside code
3. **Living Documentation**: Updated with each PR/commit
4. **Reviewable**: Docs are part of code review process

See `docs/contributing.md` for detailed contributing guidelines.
