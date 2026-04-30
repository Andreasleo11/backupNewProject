# Purchase Order - API Reference

## PurchaseOrderService

Main service for handling business logic related to Purchase Orders.

| Method | Parameters | Return Type | Description |
| :--- | :--- | :--- | :--- |
| `create` | `array $data` | `PurchaseOrder` | Creates a new PO and submits it for approval. |
| `update` | `int $id`, `array $data` | `PurchaseOrder` | Updates an existing PO (if allowed by status). |
| `delete` | `int $id` | `bool` | Deletes a PO. |
| `approve` | `int $id`, `int $userId`, `?string $remarks` | `void` | Processes approval via the unified approval engine. |
| `reject` | `int $id`, `int $userId`, `string $reason` | `void` | Processes rejection via the unified approval engine. |
| `getDashboardData` | `?string $month` | `array` | Retrieves analytics and totals for the PO dashboard. |
| `getVendorDetails` | `string $vendorName`, `string $month` | `Collection` | Retrieves list of POs for a specific vendor and month. |

---

## PdfProcessingService

Service for handling PDF-specific operations like signing, storage, and validation.

| Method | Parameters | Return Type | Description |
| :--- | :--- | :--- | :--- |
| `sign` | `PurchaseOrder $po`, `int $userId` | `string` | Signs a PDF with a digital signature and saves it. |
| `reject` | `PurchaseOrder $po`, `string $reason` | `PurchaseOrder` | Rejects a PO using the unified approval system. |
| `download` | `int $poId`, `int $userId` | `BinaryFileResponse` | Performs security checks and initiates PDF download. |
| `validatePdfFile` | `UploadedFile $file` | `bool` | Validates file type (PDF) and size (max 5MB). |
| `storePdfFile` | `UploadedFile $file`, `int $poNumber` | `string` | Stores an uploaded PDF with a unique filename. |
| `extractMetadata` | `string $filename` | `array` | Extracts basic metadata (size, pages) from a PDF. |

---

## Constants & Enums

### PurchaseOrderStatus

| Case | Value | Label | Description |
| :--- | :--- | :--- | :--- |
| `PENDING_APPROVAL` | 1 | Pending Approval | Waiting for director approval |
| `APPROVED` | 2 | Approved | Director has approved and signed |
| `REJECTED` | 3 | Rejected | Rejected by approver |
| `CANCELLED` | 4 | Cancelled | Cancelled or returned |
| `DRAFT` | 5 | Draft | Draft - not yet submitted |
