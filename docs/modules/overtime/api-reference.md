# Overtime Management Module - API Reference

## Livewire Component Methods

### ConsolidatedDetail Component

#### Public Methods

| Method | Signature | Description |
| ------ | --------- | ----------- |
| `mount` | `mount(string $date)` | Initialize component with consolidated date view |
| `getPushAllSummary` | `getPushAllSummary(): array` | Calculate summary of forms eligible for bulk push |
| `pushAllToJPayroll` | `pushAllToJPayroll(): void` | Initiate asynchronous bulk push to JPayroll |
| `cancelPushAllJob` | `cancelPushAllJob(int $jobProgressId): void` | Cancel running bulk push operation |
| `refreshData` | `refreshData(): void` | Refresh component data after job completion |
| `toggleViewMode` | `toggleViewMode(): void` | Toggle between flattened and grouped view modes |
| `pushAll` | `pushAll(int $formId): void` | Push all details for a specific form (legacy) |
| `sign` | `sign(int $formId, int $stepId): void` | Approve a specific form step |
| `signSelected` | `signSelected(): void` | Bulk approve selected forms |

#### Event Listeners

| Event | Handler | Description |
| ----- | ------- | ----------- |
| `openPushAllConfirmation` | `openPushAllConfirmation` | Show bulk push confirmation modal |
| `pushAllJobStarted` | `handlePushAllJobStarted` | Handle job initiation |
| `pushAllJobCancelled` | `handlePushAllJobCancelled` | Handle job cancellation |

### Detail Component

#### Public Methods

| Method | Signature | Description |
| ------ | --------- | ----------- |
| `mount` | `mount(int $id)` | Initialize component with specific form ID |
| `sign` | `sign(int $stepId): void` | Approve current approval step |
| `pushAll` | `pushAll(): void` | Push all approved details to JPayroll |

## Job Classes

### PushAllOvertimeToJPayroll

Asynchronous job for bulk pushing overtime details to JPayroll.

#### Constructor Parameters
- `array $formIds`: Array of overtime form IDs to process
- `int $userId`: User who initiated the operation
- `int $jobProgressId`: JobProgress record ID for tracking

#### Job Properties
- `$timeout = 3600`: 1 hour maximum execution time
- `$tries = 1`: No automatic retries (manual intervention required)

#### Execution Flow
1. Updates job progress to "processing" status
2. Iterates through each form ID
3. For each form: validates, pushes details, updates progress
4. Handles cancellation requests during execution
5. Updates final status and results

## Model Methods

### OvertimeForm (Domain Model)

| Method | Return Type | Description |
| ------ | ----------- | ----------- |
| `getWorkflowStatusAttribute` | `string` | Get current workflow status from approval request |

### OvertimeFormDetail (Domain Model)

| Method | Return Type | Description |
| ------ | ----------- | ----------- |
| `header` | `BelongsTo` | Relationship to parent OvertimeForm |
| `employee` | `BelongsTo` | Relationship to employee record |

## Service Methods

### OvertimeApprovalService

| Method | Signature | Description |
| ------ | --------- | ----------- |
| `sign` | `sign(int $formId, int $stepId): array` | Sign/approve a specific approval step |
| `reject` | `reject(int $formId, int $approvalId, string $description): array` | Reject a form with reason |

### OvertimeJPayrollService

| Method | Signature | Description |
| ------ | --------- | ----------- |
| `pushAllDetails` | `pushAllDetails(int $formId): array` | Push all approved details for a form |
| `pushSingleDetail` | `pushSingleDetail(OvertimeFormDetail $detail): array` | Push individual detail |

## Database Schema

### header_form_overtime (OvertimeForm)
```sql
- id: bigint primary key
- user_id: bigint (foreign key to users)
- dept_id: bigint (foreign key to departments)
- branch: varchar
- workflow_status: varchar (computed from approval_request)
- created_at, updated_at: timestamps
```

### detail_form_overtime (OvertimeFormDetail)
```sql
- id: bigint primary key
- header_id: bigint (foreign key to header_form_overtime)
- NIK: varchar (employee ID)
- name: varchar (employee name)
- status: enum('Approved', 'Rejected', 'Pending')
- reason: text (rejection reason)
- start_date, start_time, end_date, end_time: datetime components
- job_desc: text (task description)
- created_at, updated_at: timestamps
```

### job_progress (Async Job Tracking)
```sql
- id: bigint primary key
- job_id: varchar (Laravel job ID)
- job_type: varchar (operation type)
- user_id: bigint (foreign key to users)
- status: enum('pending', 'processing', 'completed', 'failed', 'cancelled')
- progress_percentage: integer
- current_task: varchar
- results: json
- error_message: text
- started_at, completed_at, cancelled_at: timestamps
```

## API Endpoints

### Job Progress Polling
```
GET /job-progress/{id}
```
Returns real-time progress information for asynchronous operations.

**Response:**
```json
{
  "id": 123,
  "status": "processing",
  "progress_percentage": 45,
  "current_task": "Processing form #456",
  "results": {
    "total_forms": 10,
    "successful_forms": 4,
    "failed_forms": 1
  },
  "error_message": null,
  "started_at": "2026-05-13T09:30:00Z",
  "estimated_time_remaining": 120
}
```

## Blade Components

### Consolidated Table Views
- `_consolidated_table.blade.php`: Main table with view mode support
- `_consolidated_header.blade.php`: Header with filters and view toggle
- `_push_all_confirmation_modal.blade.php`: Bulk push confirmation
- `_push_all_progress_modal.blade.php`: Real-time progress display

### Data Properties Passed to Views
- `$pushAllSummary`: Summary of eligible forms for bulk operations
- `$canPushToPayroll`: User permission for payroll operations
- `$viewMode`: Current view mode ('flattened' or 'grouped')
- `$canApprove`: User permission for approval operations

## Error Handling

### Job-Level Errors
- Circuit breaker prevents cascading failures
- Individual form failures don't stop entire batch
- Comprehensive logging for debugging

### UI-Level Errors
- Polling failures show user-friendly messages
- Progress tracking errors provide refresh guidance
- Network issues handled gracefully with retry suggestions

## Performance Considerations

### Batch Processing
- Forms processed sequentially to prevent API overload
- Configurable delays between operations
- Memory usage monitoring

### Progress Tracking
- Lightweight polling (every 2 seconds)
- Efficient database queries
- Cached progress data when possible

### Scalability
- Job timeout prevents long-running operations
- Cancellation support for user control
- Resource monitoring prevents system overload