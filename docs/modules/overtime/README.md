# Overtime Management Module

## Overview

The Overtime Management module handles the complete lifecycle of employee overtime requests, from submission through approval and payroll processing. It provides a comprehensive workflow for managing overtime forms, employee details, and integration with external payroll systems.

## Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Livewire UI   │    │  Domain Layer   │    │ Infrastructure  │
│                 │    │                 │    │                 │
│ - Index Page    │◄──►│ - OvertimeForm  │◄──►│ - Eloquent      │
│ - Detail Views  │    │ - OvertimeDetail│    │   Models        │
│ - Consolidated  │    │ - Services      │    │ - Repositories  │
│   View          │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 ▼
                    ┌─────────────────┐
                    │   Approval      │
                    │   Engine        │
                    └─────────────────┘
```

## Key Components

### Livewire Components
- `Index`: Main overtime forms listing with filtering and bulk actions
- `Detail`: Individual form view with approval workflow
- `ConsolidatedDetail`: Multi-form consolidated view for bulk operations

### Domain Models
- `OvertimeForm`: Main form entity with approval workflow
- `OvertimeFormDetail`: Individual employee overtime entries

### Key Services
- `OvertimeApprovalService`: Handles approval workflow
- `OvertimeJPayrollService`: Integration with payroll systems

## Data Model

### OvertimeForm (header_form_overtime)
| Attribute | Type | Description |
| --------- | ---- | ----------- |
| id | int | Primary key |
| user_id | int | Form submitter |
| dept_id | int | Department ID |
| branch | string | Office branch |
| workflow_status | string | Current approval status |

### OvertimeFormDetail (detail_form_overtime)
| Attribute | Type | Description |
| --------- | ---- | ----------- |
| id | int | Primary key |
| header_id | int | Parent form ID |
| NIK | string | Employee ID |
| name | string | Employee name |
| status | string | Approval status (Approved/Rejected/Pending) |
| reason | string | Rejection reason (for Rejected status) |
| start_date/time | datetime | Overtime start |
| end_date/time | datetime | Overtime end |
| job_desc | string | Task description |

## Workflow

### Form Submission Flow
```
1. Employee creates overtime form
   ↓
2. Form enters IN_REVIEW status
   ↓
3. Approval workflow (configurable steps)
   ↓
4. Final approval → APPROVED status
   ↓
5. Payroll integration
```

### Consolidated View Features
- **Bulk Approval**: Approve multiple forms at once
- **Push to Payroll**: Asynchronous bulk push to JPayroll
- **Real-time Progress**: Live tracking of background operations
- **Department Filtering**: Filter by department
- **View Modes**: Flattened (all details) vs Grouped (by form)

## API Reference

### ConsolidatedDetail Component Methods

| Method | Parameters | Description |
| ------ | ---------- | ----------- |
| `getPushAllSummary()` | - | Returns summary of eligible forms for bulk push |
| `pushAllToJPayroll()` | - | Initiates asynchronous bulk push to JPayroll |
| `cancelPushAllJob()` | `int $jobProgressId` | Cancels running bulk push operation |
| `refreshData()` | - | Refreshes component data after job completion |

### Job Classes

| Class | Purpose |
| ----- | ------- |
| `PushAllOvertimeToJPayroll` | Asynchronous bulk push to JPayroll |

## Rejection Reason Feature

### Overview
When overtime details are rejected, the system now displays the rejection reason directly in the UI, providing immediate feedback to users without requiring navigation to separate logs or contacting administrators.

### Implementation
- **Status Display**: Shows rejection reason below "REJECTED" status badge
- **Data Source**: Uses `reason` field from `OvertimeFormDetail` model
- **UI Enhancement**: Compact layout with truncation and tooltips for long reasons
- **Fallback Handling**: Shows "No reason provided" when reason is missing

### Visual Design
```
┌─────────────┐
│  REJECTED  │  ← Status badge
├─────────────┤
│ Invalid NIK │  ← Rejection reason
└─────────────┘
```

### Error Handling
- Gracefully handles null/empty reason fields
- Truncates long reasons with full text in tooltips
- Maintains table layout integrity

## Database Schema

### job_progress Table
Tracks progress of asynchronous operations:
| Column | Type | Description |
| ------ | ---- | ----------- |
| id | int | Primary key |
| job_id | string | Laravel job ID |
| job_type | string | Operation type |
| user_id | int | User who initiated |
| status | enum | pending/processing/completed/failed/cancelled |
| progress_percentage | int | Completion percentage |
| current_task | string | Current operation description |
| results | json | Operation results |
| error_message | string | Error details |

## Related Documentation

- **[API Reference](api-reference.md)**: Complete method signatures and technical specifications
- **[Workflows](workflows.md)**: Detailed process diagrams and user journey flows
- **[Contributing Guide](../../contributing.md)**: Documentation standards and processes

## Version History

- **2026-05-13**: Added rejection reason display in consolidated detail view
  - Enhanced status column to show rejection reasons below REJECTED status badges
  - Implemented in both flattened and grouped view modes
  - Added proper error handling for missing reason fields
  - Improved user experience with immediate rejection feedback

- **2026-05-13**: Implemented asynchronous global push-to-JPayroll system
  - Added bulk push functionality for all forms in consolidated view
  - Implemented real-time progress tracking with polling
  - Added job cancellation support
  - Enhanced error handling and user feedback
  - Fixed routing and authentication issues for progress tracking

- **2026-05-13**: Enhanced consolidated detail view with view mode toggle
  - Added flattened vs grouped view modes
  - Improved header layout for better discoverability
  - Removed bulk approval from flattened mode for cleaner UX
  - Maintained all existing functionality across both modes