# Approval Engine - Module Documentation

## Overview

The **Approval Engine** is the core service that orchestrates multi-step approval workflows for documents in the system. It manages the lifecycle of approval requests from submission through final approval or rejection, ensuring proper authorization, notifications, and audit trails.

## Key Features

- **Multi-step Workflows**: Supports sequential and parallel approval steps
- **Role-based & User-specific Approvals**: Flexible approver assignment
- **Immutable Versioning**: Uses specific rule template versions for stability
- **Signature Integration**: Attaches digital signatures to approval steps
- **Notification System**: Automated notifications to approvers and creators
- **Audit Logging**: Complete action history with remarks
- **Status Management**: Handles various states (DRAFT, IN_REVIEW, APPROVED, REJECTED, RETURNED, CANCELLED)
- **Authorization Guards**: Ensures only eligible users can act on steps
- **Transaction Safety**: Database transactions prevent inconsistent states

## Architecture

### Component Diagram

```
┌─────────────────────────────────────────────────────┐
│                   Application Layer                  │
│  Controllers, Commands, Jobs call ApprovalEngine    │
│  - OvertimeController::submitForApproval()          │
│  - PurchaseRequestController::approve()             │
└───────────────────┬─────────────────────────────┘
                    │ uses
                    ▼
┌─────────────────────────────────────────────────────┐
│            ApprovalEngine.php (Service)             │
│  - submit(): Creates request from rule template     │
│  - approve(): Advances to next step                │
│  - reject(): Rejects request                        │
│  - return(): Returns to creator for revision       │
│  - cancel(): Cancels request                        │
│  - canAct(): Checks user permissions                │
└───────────────────┬─────────────────────────────┘
                    │ uses
                    ▼
┌─────────────────────────────────────────────────────┐
│              Infrastructure Layer                    │
│  ApprovalRequest.php (Model)                        │
│  ApprovalStep.php (Model)                           │
│  RuleResolver.php (Service)                         │
│  UserRoles.php (Service)                            │
│  UserSignatureRepository.php (Repository)           │
│  Notifications: ApprovalActionRequired, etc.        │
└─────────────────────────────────────────────────────┘
```

### Key Files

| File                                                                 | Purpose                                        |
| -------------------------------------------------------------------- | ---------------------------------------------- |
| `app/Infrastructure/Approval/Services/ApprovalEngine.php`            | Main service class implementing approval logic |
| `app/Application/Approval/Contracts/Approvals.php`                   | Interface defining public methods              |
| `app/Domain/Approval/Contracts/Approvable.php`                       | Interface for documents that need approval     |
| `app/Domain/Approval/Contracts/RuleResolver.php`                     | Interface for resolving rule templates         |
| `app/Infrastructure/Persistence/Eloquent/Models/ApprovalRequest.php` | Model for approval requests                    |
| `app/Infrastructure/Persistence/Eloquent/Models/ApprovalStep.php`    | Model for individual approval steps            |
| `app/Infrastructure/Approval/Services/ApprovalScopingManager.php`    | Manages user eligibility and notifications     |
| `app/Notifications/ApprovalActionRequired.php`                       | Notification for pending approvals             |
| `app/Notifications/ReportApprovedNotification.php`                   | Notification for final approval                |
| `app/Notifications/ReportRejectedNotification.php`                   | Notification for rejection                     |

## Data Model

### Approvals Interface

```php
// app/Application/Approval/Contracts/Approvals.php
interface Approvals
{
    public function submit(Approvable $approvable, int $by, array $ctx = []): ApprovalInfo;
    public function approve(Approvable $approvable, int $by, ?string $remarks = null): void;
    public function reject(Approvable $approvable, int $by, ?string $remarks = null): void;
    public function return(Approvable $approvable, int $by, string $reason): void;
    public function cancel(Approvable $approvable, int $by, ?string $reason = null): void;
    public function currentRequest(Approvable $approvable): ?ApprovalInfo;
    public function canAct(Approvable $approvable, int $userId): bool;
}
```

### Approvable Interface

```php
// app/Domain/Approval/Contracts/Approvable.php
interface Approvable
{
    public function approvalRequest(): MorphOne;
    public function getApprovableDepartmentName(): string;
    public function getApprovableBranchValue(): mixed;
    public function resetItemApprovals(): void; // optional
}
```

### ApprovalRequest Model

```php
// app/Infrastructure/Persistence/Eloquent/Models/ApprovalRequest.php
class ApprovalRequest extends Model
{
    protected $fillable = [
        'status', 'rule_template_id', 'rule_template_version_id',
        'current_step', 'submitted_by', 'submitted_at', 'meta'
    ];

    // Relationships
    public function approvable(): MorphTo;
    public function steps(): HasMany;        // ApprovalStep
    public function actions(): HasMany;      // ApprovalAction (audit log)
    public function ruleTemplate(): BelongsTo; // RuleTemplate
    public function ruleVersion(): BelongsTo; // Specific RuleTemplate version
}
```

**Key Attributes:**
| Attribute | Type | Description |
|-----------|------|-------------|
| `status` | string | Current state: DRAFT, IN_REVIEW, APPROVED, REJECTED, RETURNED, CANCELLED |
| `rule_template_id` | string (UUID) | References rule template group |
| `rule_template_version_id` | int | References specific immutable version |
| `current_step` | int | Current step sequence number |
| `submitted_by` | int | User ID who submitted |
| `submitted_at` | timestamp | When submitted |
| `meta` | json | Additional context data |

### ApprovalStep Model

```php
class ApprovalStep extends Model
{
    protected $fillable = [
        'sequence', 'approver_type', 'approver_id',
        'approver_snapshot_name', 'approver_snapshot_role_slug', 'approver_snapshot_label',
        'status', 'acted_by', 'acted_at', 'remarks', 'return_reason',
        'user_signature_id', 'signature_image_path', 'signature_sha256'
    ];

    // Relationships
    public function request(): BelongsTo;   // ApprovalRequest
    public function signature(): BelongsTo; // UserSignature
}
```

**Key Attributes:**
| Attribute | Type | Description |
|-----------|------|-------------|
| `approver_type` | string | 'user' or 'role' |
| `approver_id` | int | User ID or Role ID |
| `approver_snapshot_*` | various | Cached approver info at creation time |
| `status` | string | Step state: PENDING, APPROVED, REJECTED, RETURNED, CANCELLED |
| `acted_by` | int | User who performed the action |
| `user_signature_id` | int | Digital signature used |

## Workflow: Approval Lifecycle

### 1. Document Submission

```
Creator submits document →
ApprovalEngine::submit($approvable, $userId, $context) →
  1. Resolve matching rule template via RuleResolver
  2. Get CURRENT active version of the rule
  3. Create ApprovalRequest with status='IN_REVIEW'
  4. Snapshot approval steps from rule template
  5. Set current_step = 1
  6. Notify first approver(s)
  7. Log submission action
```

### 2. Step-by-step Approval

```
Approver reviews document →
ApprovalEngine::approve($approvable, $userId, $remarks) →
  1. Validate user can act on current step
  2. Attach digital signature snapshot
  3. Mark current step as APPROVED
  4. If more steps exist: advance current_step, notify next approver
  5. If final step: mark request as APPROVED, notify creator
  6. Log approval action
```

### 3. Rejection or Return

```
Approver rejects document →
ApprovalEngine::reject($approvable, $userId, $remarks) →
  1. Validate user can act on current step
  2. Mark current step as REJECTED
  3. Mark request as REJECTED
  4. Notify creator with rejection details
  5. Log rejection action

Approver returns for revision →
ApprovalEngine::return($approvable, $userId, $reason) →
  1. Validate user can act on current step
  2. Mark current step as RETURNED with reason
  3. Mark request as RETURNED
  4. Log return action
  5. Allow resubmission from RETURNED state
```

### 4. Cancellation

```
Creator/Admin cancels request →
ApprovalEngine::cancel($approvable, $userId, $reason) →
  1. Mark current pending step as CANCELLED (if exists)
  2. Mark request as CANCELLED
  3. Log cancellation action
```

## Authorization & Permissions

### User Eligibility Checking

The engine uses a two-tier authorization system:

1. **Approver Assignment**: Steps are assigned to specific users or roles
2. **Jurisdictional Eligibility**: Users must be eligible based on department/branch scoping
3. **Personal Preferences**: Users can opt-out of notifications

### canAct() Method Logic

```php
public function canAct(Approvable $approvable, int $userId): bool
{
    $req = $approvable->approvalRequest()->first();
    if (! $req || $req->status !== 'IN_REVIEW') {
        return false; // No active request
    }

    $step = $req->currentStep();
    if (! $step) {
        return false; // No current step
    }

    try {
        $this->guardActor($step, $userId);
        return true; // User is authorized
    } catch (AuthorizationException $e) {
        return false; // Not authorized
    }
}
```

## Notification System

### Automated Notifications

- **ApprovalActionRequired**: Sent to current approvers when request reaches their step
- **ReportApprovedNotification**: Sent to creator when request is fully approved
- **ReportRejectedNotification**: Sent to creator when request is rejected

### Notification Targeting

Notifications respect:

- **Approver Type**: Direct user assignment vs role-based
- **Scoping Rules**: Department/branch eligibility
- **User Preferences**: Opt-in/opt-out settings
- **Notification Mode**: Immediate vs digest mode

## Signature Integration

### Digital Signature Attachment

When approving a step, the engine:

1. Finds the user's default active signature
2. Validates signature exists and is active
3. Snapshots signature data to the approval step
4. Records signature usage in audit log

### Signature Requirements

- Users must have an active signature to approve
- Signatures are immutable once attached
- Usage is tracked for compliance

## Audit & Logging

### Action Logging

Every state change creates an audit record:

```php
$req->actions()->create([
    'user_id' => $by,
    'from_status' => $from,
    'to_status' => $to,
    'remarks' => $remarks,
]);
```

### Signature Audit

Signature usage is logged separately:

```php
$this->userSignatures->recordEvent($sigId, 'used', [
    'feature' => 'approval_engine',
    'approval_step_id' => $step->id,
    'sequence' => $step->sequence,
    'remarks' => $remarks,
]);
```

## API Reference

### Public Methods

| Method                              | Parameters                 | Return        | Description                  |
| ----------------------------------- | -------------------------- | ------------- | ---------------------------- |
| `submit(Approvable, int, array)`    | $approvable, $by, $ctx     | ApprovalInfo  | Creates new approval request |
| `approve(Approvable, int, ?string)` | $approvable, $by, $remarks | void          | Approves current step        |
| `reject(Approvable, int, ?string)`  | $approvable, $by, $remarks | void          | Rejects the request          |
| `return(Approvable, int, string)`   | $approvable, $by, $reason  | void          | Returns for revision         |
| `cancel(Approvable, int, ?string)`  | $approvable, $by, $reason  | void          | Cancels the request          |
| `currentRequest(Approvable)`        | $approvable                | ?ApprovalInfo | Gets current request status  |
| `canAct(Approvable, int)`           | $approvable, $userId       | bool          | Checks if user can act       |

### Private Methods

| Method                      | Purpose                                   |
| --------------------------- | ----------------------------------------- |
| `mustGetInReview()`         | Validates request exists and is in review |
| `mustGetCurrentStep()`      | Gets the current active step              |
| `guardActor()`              | Authorizes user for step action           |
| `log()`                     | Records audit action                      |
| `notifyCurrentApprover()`   | Sends notifications to current approvers  |
| `notifyFinalApproval()`     | Notifies creator of approval              |
| `notifyRejection()`         | Notifies creator of rejection             |
| `attachSignatureSnapshot()` | Attaches digital signature                |
| `resolveApproverSnapshot()` | Caches approver info                      |

## Database Schema

### Table: `approval_requests`

| Column                     | Type            | Null | Default | Key     | Description          |
| -------------------------- | --------------- | ---- | ------- | ------- | -------------------- |
| `id`                       | bigint          | NO   | -       | Primary | Request ID           |
| `approvable_type`          | varchar(255)    | NO   | -       | Morph   | Model type           |
| `approvable_id`            | bigint unsigned | NO   | -       | Morph   | Model ID             |
| `status`                   | varchar(255)    | NO   | -       | Index   | Request status       |
| `rule_template_id`         | varchar(255)    | YES  | NULL    | -       | Rule group UUID      |
| `rule_template_version_id` | bigint unsigned | YES  | NULL    | FK      | Specific version     |
| `current_step`             | int             | NO   | 1       | -       | Active step sequence |
| `submitted_by`             | bigint unsigned | YES  | NULL    | -       | Submitter user ID    |
| `submitted_at`             | timestamp       | YES  | NULL    | -       | Submission time      |
| `meta`                     | json            | YES  | NULL    | -       | Context data         |
| `created_at`               | timestamp       | YES  | NULL    | -       |                      |
| `updated_at`               | timestamp       | YES  | NULL    | -       |                      |

### Table: `approval_steps`

| Column                   | Type            | Null | Default | Key     | Description       |
| ------------------------ | --------------- | ---- | ------- | ------- | ----------------- |
| `id`                     | bigint          | NO   | -       | Primary | Step ID           |
| `approval_request_id`    | bigint unsigned | NO   | -       | FK      | Parent request    |
| `sequence`               | int             | NO   | -       | Index   | Step order        |
| `approver_type`          | varchar(255)    | NO   | -       | -       | 'user' or 'role'  |
| `approver_id`            | int             | NO   | -       | -       | User/Role ID      |
| `approver_snapshot_name` | varchar(255)    | YES  | NULL    | -       | Cached name       |
| `status`                 | varchar(255)    | NO   | PENDING | -       | Step status       |
| `acted_by`               | bigint unsigned | YES  | NULL    | -       | Action user ID    |
| `acted_at`               | timestamp       | YES  | NULL    | -       | Action time       |
| `remarks`                | text            | YES  | NULL    | -       | Action remarks    |
| `user_signature_id`      | bigint unsigned | YES  | NULL    | FK      | Digital signature |
| `created_at`             | timestamp       | YES  | NULL    | -       |                   |
| `updated_at`             | timestamp       | YES  | NULL    | -       |                   |

### Table: `approval_actions` (Audit Log)

| Column                | Type            | Null | Default | Key     | Description     |
| --------------------- | --------------- | ---- | ------- | ------- | --------------- |
| `id`                  | bigint          | NO   | -       | Primary | Action ID       |
| `approval_request_id` | bigint unsigned | NO   | -       | FK      | Parent request  |
| `user_id`             | bigint unsigned | NO   | -       | -       | Actor user ID   |
| `from_status`         | varchar(255)    | YES  | NULL    | -       | Previous status |
| `to_status`           | varchar(255)    | NO   | -       | -       | New status      |
| `remarks`             | text            | YES  | NULL    | -       | Action details  |
| `created_at`          | timestamp       | YES  | NULL    | -       |                 |

## Error Handling

The engine throws specific exceptions:

- **DomainException**: Business logic violations
  - "No approval request." - No request exists
  - "Request is not in review." - Wrong status for action
  - "Already submitted." - Attempting duplicate submission
  - "No matching approval rule template." - No rule found
  - "Rule template version not found." - Invalid version
  - "No active signature found." - Missing signature for approval

- **AuthorizationException**: Permission violations
  - "Not the assigned approver." - Wrong user for user-assigned step
  - "Your role is not permitted to approve this step." - Wrong role

## How to Extend

### Adding New Approval Actions

1. Add method to `Approvals` interface
2. Implement in `ApprovalEngine`
3. Add corresponding status values
4. Update notification logic if needed

### Supporting New Approver Types

1. Extend `guardActor()` method
2. Add resolver logic in `resolveApproverSnapshot()`
3. Update scoping logic in `ApprovalScopingManager`

### Custom Notification Logic

Override notification methods or extend `ApprovalScopingManager` for custom eligibility rules.

See `docs/contributing.md` for full contributing guidelines.
