# Overtime Management Workflows

## Form Submission & Approval Workflow

```
┌─────────────────┐
│   Employee      │
│   Creates Form  │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐
│   Form Status:  │
│   IN_REVIEW     │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐     ┌─────────────────┐
│   Approval      │────►│   Step 1        │
│   Workflow      │     │   (Manager)     │
│   Engine        │     └─────────┬───────┘
└─────────────────┘               │
                                  ▼
                         ┌─────────────────┐
                         │   Approved?     │
                         └─────────┬───────┘
                                  │
                    ┌─────────────┼─────────────┐
                    │             │             │
                    ▼             ▼             ▼
           ┌─────────────┐ ┌─────────────┐ ┌─────────────┐
           │   Next Step │ │   Rejected  │ │   Approved  │
           │             │ │   (Return)  │ │   (Final)   │
           └─────────────┘ └─────────────┘ └─────────────┘
                    │             │             │
                    ▼             ▼             ▼
           ┌─────────────┐ ┌─────────────┐ ┌─────────────┐
           │ Continue    │ │   Status:   │ │   Status:   │
           │ Workflow    │ │   RETURNED  │ │   APPROVED  │
           └─────────────┘ └─────────────┘ └─────────────┘
```

## Consolidated View Operations

### Bulk Approval Workflow
```
┌─────────────────┐
│   User Selects  │
│   Multiple      │
│   Forms         │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐
│   System        │
│   Validates     │
│   Permissions   │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐     ┌─────────────────┐
│   For Each      │────►│   Check if     │
│   Form          │     │   Current      │
│                 │     │   Step can be  │
└─────────────────┘     │   Approved     │
                        └─────────┬───────┘
                                  │
                    ┌─────────────┼─────────────┐
                    │             │             │
                    ▼             ▼             ▼
           ┌─────────────┐ ┌─────────────┐ ┌─────────────┐
           │   Approve    │ │   Skip Form │ │   Error     │
           │   Step       │ │   (No       │ │   (Invalid  │
           │              │ │    Action)  │ │    State)   │
           └─────────────┘ └─────────────┘ └─────────────┘
```

### Bulk Push to JPayroll Workflow
```
┌─────────────────┐
│   User Clicks   │
│   "Push All"    │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐
│   Pre-flight    │
│   Validation    │
│   & Summary     │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐
│   User Confirms │
│   Operation     │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐
│   Create Job    │
│   Progress      │
│   Record        │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐     ┌─────────────────┐
│   Dispatch      │────►│   Queue Job    │
│   Async Job     │     │   (Database)   │
└─────────────────┘     └─────────┬───────┘
                                  │
                                  ▼
                         ┌─────────────────┐
                         │   Job Worker    │
                         │   Processes     │
                         │   Forms         │
                         └─────────┬───────┘
                                  │
                    ┌─────────────┼─────────────┐
                    │             │             │
                    ▼             ▼             ▼
           ┌─────────────┐ ┌─────────────┐ ┌─────────────┐
           │   Success    │ │   Partial   │ │   Failed    │
           │   (All       │ │   Success   │ │   (No       │
           │    Forms)    │ │              │ │    Forms)   │
           └─────────────┘ └─────────────┘ └─────────────┘
                                  │
                                  ▼
                         ┌─────────────────┐
                         │   Update Job    │
                         │   Progress      │
                         │   Record        │
                         └─────────┬───────┘
                                  │
                                  ▼
                         ┌─────────────────┐
                         │   UI Polls      │
                         │   Progress      │
                         │   Updates       │
                         └─────────────────┘
```

## Data Rejection Workflow

### Individual Detail Rejection
```
┌─────────────────┐
│   Approver      │
│   Reviews       │
│   Detail        │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐
│   Finds Issue   │
│   (Invalid NIK, │
│    Wrong Time)  │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐
│   Clicks Reject │
│   Button        │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐
│   Provides      │
│   Reason        │
│   (Required)    │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐
│   Status:       │
│   "Rejected"    │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐
│   Reason Saved  │
│   to Database   │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐
│   UI Shows      │
│   Reason        │
│   Immediately   │
└─────────────────┘
```

### Rejection Reason Display
```
Detail Status: Rejected
├─ Status Badge (Red)
└─ Reason Text (Small, below badge)
   ├─ "Invalid NIK" (if provided)
   └─ "No reason provided" (fallback)
```

## View Mode Workflows

### Flattened View (Default)
```
┌─────────────────┐
│   All Details   │
│   in Single     │
│   Continuous    │
│   Table         │
└─────────────────┘
         │
         ▼
┌─────────────────┐
│   Individual    │
│   Approvals     │
│   Only          │
└─────────────────┘
         │
         ▼
┌─────────────────┐
│   No Bulk       │
│   Selection     │
│   UI            │
└─────────────────┘
```

### Grouped View (Advanced)
```
┌─────────────────┐
│   Forms Grouped │
│   by Header     │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐
│   Expandable    │
│   Form          │
│   Sections      │
└─────────┬───────┘
          │
          ▼
┌─────────────────┐
│   Bulk Form     │
│   Selection     │
│   Available     │
└─────────────────┘
         │
         ▼
┌─────────────────┐
│   Bulk Approval │
│   Operations    │
└─────────────────┘
```

## Error Handling Workflows

### Job Failure Scenarios
```
Job Execution Error
├─ Network Timeout
├─ API Rate Limiting
├─ Invalid Form Data
├─ JPayroll System Error
└─ Database Connection Issues

↓

Error Handling
├─ Individual Form Isolation
├─ Comprehensive Logging
├─ User Notification
├─ Manual Retry Options
└─ Circuit Breaker Activation
```

### Progress Tracking Failures
```
Polling Errors
├─ Network Connectivity
├─ Authentication Issues
├─ Server Unavailable
└─ Database Query Failures

↓

UI Responses
├─ Show Error Message
├─ Suggest Page Refresh
├─ Stop Automatic Polling
└─ Provide Manual Status Check
```

## Performance Optimization

### Batch Processing Strategy
```
Form Processing Queue
├─ Process One Form at a Time
├─ 2-5 Second Delays Between Forms
├─ Memory Usage Monitoring
├─ Automatic Circuit Breaker
└─ Progress Update After Each Form
```

### UI Responsiveness
```
User Experience
├─ Immediate Modal Display
├─ Background Processing
├─ Real-time Progress Updates
├─ Cancellation Available
└─ Non-blocking Interface
```