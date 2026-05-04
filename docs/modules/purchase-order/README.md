# Purchase Order Module — Implementation Documentation

> **Living Document** · Last Updated: May 4, 2026 · v3.0

This document describes the **current, implemented** state of the Purchase Order (PO) module. It covers every layer of the stack: database schema, Eloquent model, service layer, background jobs, Livewire components, views, and routing.

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [Database Schema](#2-database-schema)
3. [Eloquent Model](#3-eloquent-model-purchaseorder)
4. [Status Enum](#4-status-enum-purchaseorderstatus)
5. [Service Layer](#5-service-layer)
6. [Background Jobs](#6-background-jobs)
7. [Livewire Components](#7-livewire-components)
   - [PurchaseOrderIndex](#71-purchaseorderindex)
   - [PurchaseOrderShow](#72-purchaseordershow)
8. [File Attachments](#8-file-attachments)
9. [HTTP Controller](#9-http-controller-purchaseordercontroller)
10. [Routing Reference](#10-routing-reference)
11. [Approval Workflow Integration](#11-approval-workflow-integration)
12. [Change Log](#12-change-log)

---

## 1. Architecture Overview

```
┌──────────────────────────────────────────────────────────────┐
│                    Presentation Layer                        │
│  Livewire: PurchaseOrderIndex  │  PurchaseOrderShow          │
│  Blade Views + Alpine.js       │  Partials (attachments...)  │
└────────────────────┬─────────────────────────────────────────┘
                     │ Livewire actions / controller actions
┌────────────────────▼─────────────────────────────────────────┐
│                   Application Layer                          │
│  PurchaseOrderService     │  PdfProcessingService            │
│  Approvals contract       │  Background Jobs (Queue)          │
└────────────────────┬─────────────────────────────────────────┘
                     │ Eloquent ORM
┌────────────────────▼─────────────────────────────────────────┐
│                    Data Layer                                │
│  purchase_orders table    │  files table                     │
│  approval_requests table  │  approval_steps table            │
│  purchase_order_categories│  purchase_order_download_logs    │
└──────────────────────────────────────────────────────────────┘
```

**Key design decisions:**
- Business logic lives in `PurchaseOrderService`, **not** in the controller or Livewire component.
- Approval state is derived from the `approval_requests` polymorphic relation — **no status column** in `purchase_orders`.
- Heavy approval/rejection operations are dispatched to **background jobs** to keep the UI responsive.
- File attachments are stored in the `files` table linked by `doc_id = po_number`.

---

## 2. Database Schema

### `purchase_orders`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | Auto-increment |
| `po_number` | varchar(255) | Human-readable PO identifier |
| `filename` | varchar(255) | Stored PDF filename (`PO_{po_number}_{timestamp}.pdf`) |
| `vendor_name` | varchar(255) | Vendor name |
| `invoice_date` | date | Cast to `date` |
| `invoice_number` | varchar(255) | Nullable |
| `currency` | varchar(255) | Default: `IDR` |
| `total` | decimal(8,2) | Cast to `decimal:2` |
| `tanggal_pembayaran` | date | Payment due date, cast to `date` |
| `purchase_order_category_id` | bigint FK | References `purchase_order_categories.id` |
| `creator_id` | bigint FK | References `users.id` |
| `approved_date` | timestamp | Nullable, cast to `datetime` |
| `reason` | text | Nullable — cancellation/rejection notes |
| `parent_po_number` | varchar(255) | Nullable — set on revisions |
| `revision_count` | int | Tracks how many revisions have been created |
| `downloaded_at` | timestamp | Nullable, cast to `datetime` |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

> **No `status` column.** Workflow status is derived at runtime from the `approval_requests` polymorphic relationship via `getWorkflowStatusAttribute()`.

### `purchase_order_categories`

| Column | Type |
|---|---|
| `id` | bigint PK |
| `name` | varchar(255) |
| `created_at` | timestamp |
| `updated_at` | timestamp |

### `purchase_order_download_logs`

| Column | Type |
|---|---|
| `id` | bigint PK |
| `purchase_order_id` | bigint FK |
| `user_id` | bigint FK |
| `created_at` | timestamp |

### `files` (Attachments)

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `doc_id` | varchar | Stores the `po_number` of the associated PO |
| `name` | varchar | Stored filename on disk |
| `mime_type` | varchar | Nullable |
| `size` | bigint | File size in bytes, nullable |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

> The original `data` (LONGBLOB) column was dropped in migration `2024_08_13`. Files are now stored on disk under `storage/files/`.

---

## 3. Eloquent Model: `PurchaseOrder`

**File:** `app/Models/PurchaseOrder.php`  
**Implements:** `Approvable` (approval engine contract)

### Fillable Attributes
```
po_number, approved_date, filename, reason, creator_id,
downloaded_at, vendor_name, invoice_date, invoice_number,
currency, total, tanggal_pembayaran, purchase_order_category_id,
parent_po_number, revision_count
```

### Casts
| Attribute | Cast |
|---|---|
| `invoice_date` | `date` |
| `approved_date` | `datetime` |
| `tanggal_pembayaran` | `date` |
| `downloaded_at` | `datetime` |
| `total` | `decimal:2` |
| `revision_count` | `integer` |

### Relationships

| Method | Type | Notes |
|---|---|---|
| `user()` | `belongsTo(User)` | Via `creator_id` |
| `category()` | `belongsTo(PurchaseOrderCategory)` | Via `purchase_order_category_id` |
| `approvalRequest()` | `morphOne(ApprovalRequest)` | Polymorphic — the source of workflow status |
| `downloadLogs()` | `hasMany(PurchaseOrderDownloadLog)` | All download events |
| `latestDownloadLog()` | `hasOne(PurchaseOrderDownloadLog)` | Latest only |

### Computed Attributes (Dynamic, not stored)

| Attribute | Source | Returns |
|---|---|---|
| `workflow_status` | `approvalRequest.status` or `'DRAFT'` | String: `DRAFT`, `IN_REVIEW`, `APPROVED`, `REJECTED`, `CANCELLED` |
| `workflow_step` | Current pending `ApprovalStep` label | Approver name/role currently holding the step |
| `current_approver` | Alias for `workflow_step` | Same as above |

### Key Methods

```php
// Get the typed status enum (maps workflow_status → PurchaseOrderStatus)
$po->getStatusEnum(): PurchaseOrderStatus

// Check state
$po->canBeEdited(): bool   // true only for REJECTED/CANCELLED
$po->isTerminal(): bool    // true for APPROVED/REJECTED/CANCELLED
```

### Scopes

```php
// Filter by workflow status string
PurchaseOrder::withWorkflowStatus('IN_REVIEW')

// Filter POs eligible for revision (REJECTED/CANCELLED)
PurchaseOrder::editable()

// Filter POs approved in the current calendar month
PurchaseOrder::approvedThisMonth()
```

---

## 4. Status Enum: `PurchaseOrderStatus`

**File:** `app/Enums/PurchaseOrderStatus.php`  
**Type:** `int`-backed PHP enum

### Cases

| Case | Int Value | Description |
|---|---|---|
| `PENDING_APPROVAL` | `1` | Submitted and waiting for director approval (`IN_REVIEW`) |
| `APPROVED` | `2` | Director has approved and signed |
| `REJECTED` | `3` | Rejected by an approver |
| `CANCELLED` | `4` | Cancelled or returned by workflow |
| `DRAFT` | `5` | Not yet submitted (no approval request) |

### Workflow Status Mapping

The enum bridges the string-based approval engine to the integer-based PO status:

| Workflow Status String | PurchaseOrderStatus |
|---|---|
| `DRAFT` | `DRAFT` |
| `IN_REVIEW` | `PENDING_APPROVAL` |
| `APPROVED` | `APPROVED` |
| `REJECTED` | `REJECTED` |
| `CANCELLED`, `RETURNED` | `CANCELLED` |

### Capability Matrix

| Status | `canEdit()` | `canApprove()` | `canReject()` | `isTerminal()` |
|---|---|---|---|---|
| `DRAFT` | ❌ | ❌ | ❌ | ❌ |
| `PENDING_APPROVAL` | ❌ | ✅ | ✅ | ❌ |
| `APPROVED` | ❌ | ❌ | ❌ | ✅ |
| `REJECTED` | ✅ | ❌ | ❌ | ✅ |
| `CANCELLED` | ✅ | ❌ | ❌ | ✅ |

### CSS Classes (for Blade templates)

| Status | Classes |
|---|---|
| `PENDING_APPROVAL` | `bg-amber-100 text-amber-800 border-amber-200` |
| `APPROVED` | `bg-emerald-100 text-emerald-800 border-emerald-200` |
| `REJECTED` | `bg-rose-100 text-rose-800 border-rose-200` |
| `CANCELLED` | `bg-orange-100 text-orange-800 border-orange-200` |
| `DRAFT` | `bg-slate-100 text-slate-800 border-slate-200` |

---

## 5. Service Layer

### `PurchaseOrderService`

**File:** `app/Services/PurchaseOrderService.php`  
**DI Dependency:** `Approvals` contract (injected via constructor)

All operations are wrapped in `DB::transaction()` and emit structured log entries.

| Method | Signature | Description |
|---|---|---|
| `create` | `(array $data): PurchaseOrder` | Creates PO, assigns creator, saves PDF filename, handles parent revision logic, then calls `$approvals->submit()` |
| `update` | `(int $id, array $data): PurchaseOrder` | Validates `canEdit()`, updates fields and optional PDF filename |
| `delete` | `(int $id): bool` | Deletes the record (no status guard currently) |
| `approve` | `(int $id, int $userId, ?string $remarks = null): void` | Sets `approved_date`, calls `$approvals->approve()` |
| `reject` | `(int $id, int $userId, string $reason): void` | Calls `$approvals->reject()` |
| `approveAll` | `(array $ids, int $userId): void` | Loops `approve()` inside a single transaction |
| `rejectAll` | `(array $ids, int $userId, string $reason): void` | Loops `reject()` inside a single transaction |
| `cancel` | `(int $id, string $reason): void` | Calls `$approvals->cancel()` |
| `getDashboardData` | `(?string $month = null): array` | Returns vendor totals, top vendors, monthly totals, available months, status counts, and category chart data |
| `getVendorDetails` | `(string $vendorName, string $month): Collection` | Returns POs for a vendor in a given month |

### `PdfProcessingService`

**File:** `app/Services/PdfProcessingService.php`

| Method | Description |
|---|---|
| `sign(PurchaseOrder $po, int $userId)` | Stamps an approval signature onto the PDF |
| `reject(PurchaseOrder $po, string $reason)` | Marks the PDF as rejected |
| `download(int $poId, int $userId)` | Returns a `BinaryFileResponse` for secure PDF download |

---

## 6. Background Jobs

Located in `app/Jobs/PurchaseOrder/`.

### `ProcessPurchaseOrderApprovalJob`

Dispatched by `PurchaseOrderIndex::approveSelected()` for bulk approvals. Processes a single PO through `PurchaseOrderService::approve()` in the background queue. On failure, caches the error under `po_process_error_{id}` so the polling mechanism can surface it in the UI.

### `ProcessPurchaseOrderRejectionJob`

Dispatched by `PurchaseOrderIndex::rejectSelected()` for bulk rejections. Processes a single PO through `PurchaseOrderService::reject()`. Same error caching pattern as the approval job.

### Background Status Polling

`PurchaseOrderIndex::checkProcessingStatus()` is called by Livewire's polling mechanism. It:
1. Checks the `po_process_error_{id}` cache key for each processing PO and surfaces any errors.
2. Queries whether remaining POs are still `IN_REVIEW` to detect completion.
3. Flashes a success message when all background operations finish.

---

## 7. Livewire Components

### 7.1 `PurchaseOrderIndex`

**File:** `app/Livewire/PurchaseOrder/PurchaseOrderIndex.php`  
**Route:** `GET /purchaseOrders` → `po.index`  
**View:** `resources/views/livewire/purchase-order/index.blade.php`

#### Public State Properties

| Property | Type | Default | URL Synced |
|---|---|---|---|
| `search` | string | `''` | ✅ |
| `statusFilter` | string | `''` | ✅ |
| `vendorFilter` | string | `''` | ✅ |
| `monthFilter` | string | `''` | ✅ |
| `dateFrom` | string | `''` | ✅ |
| `dateTo` | string | `''` | ✅ |
| `amountFrom` | string | `''` | ✅ |
| `amountTo` | string | `''` | ✅ |
| `creatorFilter` | string | `''` | ✅ |
| `categoryFilter` | string | `''` | ✅ |
| `sortBy` | string | `'created_at'` | ✅ |
| `sortDirection` | string | `'desc'` | ✅ |
| `perPage` | int | `10` | ❌ |
| `perPageOptions` | array | `[10, 25, 50, 100]` | ❌ |
| `selectedIds` | array | `[]` | ❌ |
| `selectAll` | bool | `false` | ❌ |
| `processingIds` | array | `[]` | ❌ |
| `showDetailModal` | bool | `false` | ❌ |
| `selectedPurchaseOrder` | mixed | `null` | ❌ |
| `pdfUrl` | string\|null | `null` | ❌ |

> All filter properties reset the page on change via `updatingXxx()` lifecycle hooks.

#### Computed Properties

| Property | Returns | Description |
|---|---|---|
| `$purchaseOrders` | `LengthAwarePaginator` | Paginated result of `getPurchaseOrdersQuery()` |
| `$filteredTotal` | `float` | `SUM(total)` of all records matching current filters (not just current page) |
| `$stats` | `array` | `pending_me`, `in_review`, `rejected_month`, `total_valuation` counts/sums |
| `$filters` | `array` | Filter option lists for dropdowns: statuses, vendors, months, creators, categories |
| `$canBulkAction` | `bool` | `true` when `$bulkActionReason === null` |
| `$bulkActionReason` | `string\|null` | Human-readable reason why bulk actions are disabled, or `null` if allowed |

#### Query Builder (`getPurchaseOrdersQuery()`)

Selects a minimal set of columns for performance:
```
id, po_number, invoice_date, invoice_number, vendor_name,
creator_id, total, approved_date, created_at,
tanggal_pembayaran, purchase_order_category_id
```
Eager loads: `user:id,name`, `approvalRequest.steps`, `approvalRequest.actions`.

**Filtering logic:**

| Filter | Mechanism |
|---|---|
| `search` | LIKE on `po_number`, `vendor_name`, `invoice_number`, related `user.name` |
| `statusFilter` | `withWorkflowStatus()` scope (polymorphic subquery) |
| `vendorFilter` | Exact match on `vendor_name` |
| `categoryFilter` | Exact match on `purchase_order_category_id` |
| `creatorFilter` | LIKE on related `user.name` |
| `monthFilter` | `DATE_FORMAT(invoice_date, '%Y-%m')` |
| `dateFrom` / `dateTo` | `invoice_date >= / <=` |
| `amountFrom` / `amountTo` | `total >= / <=` |

**Sortable columns whitelist:**
```
po_number, invoice_date, invoice_number, vendor_name,
total, approved_date, created_at, tanggal_pembayaran,
purchase_order_category_id
```
Calling `sortByColumn($column)` toggles direction or sets a new column.

#### Bulk Action Guard (`getBulkActionReasonProperty`)

Returns a string (disabling actions) when:
- `selectedIds` is empty → `"No items selected."`
- Any selected PO has no approval request or its status is not `IN_REVIEW` → `"Selection contains items already processed or in Draft."`

Returns `null` when bulk approve/reject is safe to proceed.

#### Key Actions

| Method | Description |
|---|---|
| `sortByColumn($column)` | Toggle sort direction or change sort column |
| `clearFilters()` | Resets all filters, sort, and selection |
| `openDetailModal($poId)` | Eager loads PO with approval chain and generates PDF URL |
| `closeDetailModal()` | Clears modal state |
| `approvePurchaseOrder()` | Single-record approval from modal (via `PurchaseOrderService`) |
| `rejectPurchaseOrder($reason)` | Single-record rejection from modal |
| `approveSelected()` | Dispatches `ProcessPurchaseOrderApprovalJob` for each selected PO |
| `rejectSelected($reason)` | Dispatches `ProcessPurchaseOrderRejectionJob` for each selected PO |
| `checkProcessingStatus()` | Polled by Livewire to update background processing indicators |
| `exportSelected()` | Streams a CSV for the selected PO IDs |
| `exportFiltered()` | Streams a CSV for all POs matching current filters |
| `filterByStat($type)` | Shortcuts: `pending_me`, `in_review`, `rejected` |
| `updatedSelectAll($value)` | Selects all IDs on current page, or clears |
| `updatedSelectedIds()` | Resets `selectAll` checkbox to unchecked |

#### Active Filter Pills

The view computes `$activePills` from the current state. Each pill shows the active filter label + value and has an `×` button that calls `$set('filterKey', '')` to remove it individually. A "Clear All" button calls `clearFilters()`.

### 7.2 `PurchaseOrderShow`

**File:** `app/Livewire/PurchaseOrder/PurchaseOrderShow.php`  
**Route:** `GET /purchaseOrder/{id}` → `po.view`  
**View:** `resources/views/livewire/purchase-order/purchase-order-show.blade.php`  
**Layout:** `new.layouts.app`

#### Mount

```php
public function mount($id)
{
    $this->purchaseOrderId = $id;
}
```

#### Computed Properties

| Property | Eager Loads | Description |
|---|---|---|
| `$purchaseOrder` | `user`, `category`, `approvalRequest.actions.causer`, `approvalRequest.steps`, `downloadLogs.user`, `latestDownloadLog.user` | The full PO with all related data |
| `$activities` | — | Timeline built from `approvalRequest.actions`, download logs, and submission timestamp |
| `$revisions` | — | POs where `parent_po_number = this PO's po_number` |
| `$files` | — | `File::where('doc_id', $po->po_number)->get()` |

#### Activities Timeline

Aggregates three event types, sorted newest-first:
1. **Submission** — from `approvalRequest.submitted_at`
2. **Approval actions** — from each `ApprovalAction` on the approval request
3. **Downloads** — from each `PurchaseOrderDownloadLog`

#### Actions

| Method | Description |
|---|---|
| `approve(Approvals, PdfProcessingService)` | Runs inside DB transaction: calls approval engine → PDF signing → sets `approved_date` |
| `reject(Approvals)` | Validates `reason` (required, 3–500 chars), calls approval engine rejection |

#### View Structure

```
Header (PO #ID, breadcrumb, Export as PDF button)
├── Main content (left column: PDF preview, activity timeline)
└── Sidebar (right column: approval actions, PO details card)
    ├── Approve / Reject buttons (for directors)
    ├── PO Number, Vendor, Category
    └── Invoice Date / Payment Date
Attachments section (full width, below main layout)
└── file-attachments partial
Creator info footer (dark card)
Reject modal (slide-in panel)
Upload modal (dispatched via Alpine event)
```

#### Reject Modal

- Triggered by Alpine `showReject` boolean
- Textarea for rejection reason bound to `wire:model="reason"`
- Submit button calls `wire:click="reject"`, disabled until reason is non-empty

---

## 8. File Attachments

**Partial:** `resources/views/partials/file-attachments.blade.php`

Accepts:
- `$files` — collection of `File` models
- `$showDelete` — boolean, shows delete button if `true`
- `$title` — optional section title
- `$gridCols` — optional Tailwind grid class (defaults to `grid-cols-1 sm:grid-cols-2`)

**File type icons** (Bootstrap Icons):
| Extension | Icon | Color |
|---|---|---|
| jpg, jpeg, png, gif, webp | `bi-file-earmark-image` | purple |
| pdf | `bi-file-earmark-pdf` | rose |
| xls, xlsx, csv | `bi-file-earmark-spreadsheet` | emerald |
| doc, docx | `bi-file-earmark-word` | blue |
| other | `bi-file-earmark-text` | slate |

**Download link:** `asset('storage/files/' . $file->name)`  
**Delete form:** POSTs to `route('file.destroy', $file->id)` with `@method('DELETE')`

**Attachment upload** is triggered by dispatching the `open-upload-modal` Alpine event. The upload button is shown only to the PO creator or users with the `purchaser` role.

---

## 9. HTTP Controller: `PurchaseOrderController`

**File:** `app/Http/Controllers/PurchaseOrderController.php`

Delegates all business logic to injected services.

| Method | Route | Description |
|---|---|---|
| `create` | `GET po.create` | Returns creation view with categories |
| `store` | `POST po.store` | Converts date format, stores PDF, calls `PurchaseOrderService::create()` |
| `edit` | `GET po.edit` | Returns edit view |
| `update` | `PUT po.update` | Handles optional new PDF upload, calls `PurchaseOrderService::update()` |
| `destroy` | `DELETE po.destroy` | Calls `PurchaseOrderService::delete()` |
| `sign` | `POST po.sign` | DB transaction: approval engine → PDF sign |
| `rejectPDF` | `POST po.reject` | Calls `PdfProcessingService::reject()` |
| `rejectAll` | `POST po.rejectAll` | Calls `PurchaseOrderService::rejectAll()` |
| `approveSelected` | `POST purchase_orders.approve_selected` | Calls `PurchaseOrderService::approveAll()` |
| `rejectSelected` | `POST purchase_orders.reject_selected` | Calls `PurchaseOrderService::rejectAll()` |
| `downloadPDF` | `GET po.download` | Calls `PdfProcessingService::download()`, logs download for creator |
| `exportExcel` | `GET po.export` | Exports filtered results via `PurchaseOrderExport` (Maatwebsite Excel) |
| `dashboard` | `GET po.dashboard` | Calls `PurchaseOrderService::getDashboardData()` |
| `cancel` | `PUT po.cancel` | Calls `PurchaseOrderService::cancel()` |

PDF files are stored under `storage/app/public/pdfs/` with the naming convention:
```
PO_{po_number}_{unix_timestamp}.pdf
```

---

## 10. Routing Reference

All routes are grouped under `auth` middleware in `routes/procurement.php`.

| Route Name | Method | URI | Handler |
|---|---|---|---|
| `po.index` | GET | `/purchaseOrders` | `PurchaseOrderIndex` (Livewire) |
| `po.create` | GET | `/purchaseOrder/create` | `PurchaseOrderController@create` |
| `po.store` | POST | `/purchaseOrder/store` | `PurchaseOrderController@store` |
| `po.view` | GET | `/purchaseOrder/{id}` | `PurchaseOrderShow` (Livewire) |
| `po.edit` | GET | `/purchaseOrder/{id}/edit` | `PurchaseOrderController@edit` |
| `po.update` | PUT | `/purchaseOrder/{po}` | `PurchaseOrderController@update` |
| `po.destroy` | DELETE | `/purchaseOrder/{id}` | `PurchaseOrderController@destroy` |
| `po.sign` | POST | `/purchaseOrder/sign` | `PurchaseOrderController@sign` |
| `po.reject` | POST | `/purchaseOrder/reject-pdf` | `PurchaseOrderController@rejectPDF` |
| `po.rejectAll` | POST | `/purchaseOrder/rejectAll` | `PurchaseOrderController@rejectAll` |
| `po.download` | GET | `/download-pdf/{filename}` | `PurchaseOrderController@downloadPDF` |
| `po.export` | GET | `/purchase-orders/export` | `PurchaseOrderController@exportExcel` |
| `po.cancel` | PUT | `/purchase-orders/cancel/{id}` | `PurchaseOrderController@cancel` |
| `po.dashboard` | GET | `/purchaseOrders/dashboard` | `PurchaseOrderDashboard` (Livewire) |
| `purchase_orders.approve_selected` | POST | `/purchase-orders/approve-selected` | `PurchaseOrderController@approveSelected` |
| `purchase_orders.reject_selected` | POST | `/purchase-orders/reject-selected` | `PurchaseOrderController@rejectSelected` |
| `po.vendor-monthly-totals` | GET | `/purchase-orders/vendor-monthly-totals` | `PurchaseOrderController@vendorMonthlyTotals` |

---

## 11. Approval Workflow Integration

The PO module uses the **unified approval engine** (`App\Application\Approval\Contracts\Approvals`) for all state transitions. There is no bespoke approval code in the PO module itself.

### How Status is Derived

```
PurchaseOrder (no status column)
    └── approvalRequest (polymorphic MorphOne)
            └── status: 'DRAFT' | 'IN_REVIEW' | 'APPROVED' | 'REJECTED' | 'CANCELLED'

$po->workflow_status    // Reads from approvalRequest.status (or 'DRAFT' if null)
$po->getStatusEnum()    // Maps workflow_status → PurchaseOrderStatus enum
```

### Workflow Lifecycle

```
[Creator submits]
    → approvals->submit($po, $userId)
    → ApprovalRequest created with status = 'IN_REVIEW'
    → ApprovalStep(s) created per configured flow

[Director approves]
    → approvals->approve($po, $userId, $remarks)
    → ApprovalStep marked complete
    → ApprovalRequest status → 'APPROVED'

[Director rejects]
    → approvals->reject($po, $userId, $reason)
    → ApprovalRequest status → 'REJECTED'

[Creator cancels]
    → approvals->cancel($po, $userId, $reason)
    → ApprovalRequest status → 'CANCELLED'
```

### Setup Commands

```bash
# Seed the PO approval workflow rules
php artisan db:seed --class=PoWorkflowSeeder

# Set up approval relationships for all existing POs (idempotent)
php artisan po:setup-approval

# Force recreation of baseline rules
php artisan po:setup-approval --force
```

### Notifications

All approval notifications (action required, approved, rejected) are dispatched through the unified approval engine. The PO module has no separate notification logic.

---

## 12. Change Log

| Version | Date | Summary |
|---|---|---|
| v3.0 | 2026-05-04 | Complete rewrite to reflect actual implemented state. Removed speculative/roadmap content. Added accurate Index, Show, Service, Jobs, Schema, and Routing documentation. |
| v2.2 | 2026-04-29 | Documented removal of redundant `approval_request_id` column |
| v2.1 | 2026-04-29 | Documented unified notification architecture |
| v2.0 | 2026-04-27 | Updated to reflect actual implementation, removed fictional timelines |
| v1.0 | 2026-04-24 | Initial planning document |
