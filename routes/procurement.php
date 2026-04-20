<?php

use App\Http\Controllers\DetailPurchaseRequestController;
use App\Http\Controllers\DirectorPurchaseRequestController;
use App\Http\Controllers\materialPredictionController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\PurchasingDetailController;
use App\Http\Controllers\PurchasingMaterialController;
use App\Http\Controllers\PurchasingSupplierEvaluationController;
use App\Livewire\DeliveryNote\DeliveryNoteForm;
use App\Livewire\DeliveryNote\DeliveryNoteIndex;
use App\Livewire\DeliveryNote\DeliveryNotePrint;
use App\Livewire\DeliveryNoteShow;
use App\Livewire\PurchaseRequest\PurchaseRequestIndex;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Procurement Routes
|--------------------------------------------------------------------------
|
| Routes for managing purchase requests, purchase orders, delivery notes,
| supplier evaluations, and material prediction.
|
| RECOMMENDED PERMISSIONS:
| - procurement.view-requests
| - procurement.create-requests
| - procurement.approve-requests
| - procurement.manage-orders
| - procurement.manage-deliveries
| - procurement.evaluate-suppliers
|
| RECOMMENDED ROLES: admin, super-admin, procurement, accounting, director, manager
|
*/

Route::middleware('auth')->group(function () {
    // Purchase Requests
    Route::get('purchase-requests/get-item-names', [PurchaseRequestController::class, 'getItemNames'])->name('purchase-requests.get-item-names');
    Route::get('purchase-requests', PurchaseRequestIndex::class)->name('purchase-requests.index');
    Route::get('purchase-requests/create', [PurchaseRequestController::class, 'create'])->name('purchase-requests.create');
    Route::post('purchase-requests', [PurchaseRequestController::class, 'store'])->name('purchase-requests.store');
    Route::get('purchase-requests/{id}/edit', [PurchaseRequestController::class, 'edit'])->name('purchase-requests.edit');
    Route::put('purchase-requests/{id}', [PurchaseRequestController::class, 'update'])->name('purchase-requests.update');
    Route::put('purchase-requests/{id}/cancel', [PurchaseRequestController::class, 'cancel'])->name('purchase-requests.cancel');
    Route::post('/purchaseRequestsInsert', [PurchaseRequestController::class, 'store'])->name('purchaserequest.insert');
    Route::delete('purchase-requests/{id}', [PurchaseRequestController::class, 'destroy'])->name('purchase-requests.destroy');
    Route::get('purchase-requests/export-excel', [PurchaseRequestController::class, 'exportExcel'])->name('purchase-requests.export-excel');
    Route::get('purchase-requests/{id}', [PurchaseRequestController::class, 'show'])->name('purchase-requests.show');
    Route::get('/purchase-requests/{id}/quick-view', [PurchaseRequestController::class, 'quickView'])->name('purchase-requests.quick-view');
    Route::post('purchase-requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])->name('purchase-requests.approve');
    Route::post('purchase-requests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject'])->name('purchase-requests.reject');
    Route::post('purchase-requests/{purchaseRequest}/return', [PurchaseRequestController::class, 'returnForRevision'])->name('purchase-requests.return');
    Route::post('purchase-requests/{purchaseRequest}/sign-and-submit', [PurchaseRequestController::class, 'signAndSubmit'])->name('purchase-requests.sign-and-submit');
    Route::post('purchase-requests/items/{item}/approve', [DetailPurchaseRequestController::class, 'approve'])->name('purchase-requests.items.approve');
    Route::post('purchase-requests/items/{item}/reject', [DetailPurchaseRequestController::class, 'reject'])->name('purchase-requests.items.reject');

    // Batch approve / reject — requires 'pr.batch-approve' permission (director-level only)
    Route::put('purchase-requests/batch-approve', [PurchaseRequestController::class, 'batchApprove'])->name('purchase-requests.batch-approve');
    Route::put('purchase-requests/batch-reject', [PurchaseRequestController::class, 'batchReject'])->name('purchase-requests.batch-reject');
    Route::get('purchase-requests/batch-status', [PurchaseRequestController::class, 'batchStatus'])->name('purchase-requests.batch-status');

    // Purchase Request Details
    Route::get('/purchaseRequestsDetail/{id}', [DetailPurchaseRequestController::class, 'detailpr'])->name('pr.detail');
    Route::post('pr/markasreceived/', [DetailPurchaseRequestController::class, 'receivedItem'])->name('pr.receive.item');
    Route::post('/pr/markasreceivedall', [DetailPurchaseRequestController::class, 'receivedAll'])->name('pr.receive.all');

    // Purchase Orders
    Route::get('purchaseOrders', [PurchaseOrderController::class, 'index'])->name('po.index');
    Route::post('purchaseOrder/create', [PurchaseOrderController::class, 'create'])->name('po.create');
    Route::post('/purchaseOrder/store', [PurchaseOrderController::class, 'store'])->name('po.store');
    Route::get('/purchaseOrder/{id}', [PurchaseOrderController::class, 'view'])->name('po.view');
    Route::post('/purchaseOrder/sign', [PurchaseOrderController::class, 'sign'])->name('po.sign');
    Route::post('/purchaseOrder/reject-pdf', [PurchaseOrderController::class, 'rejectPDF'])->name('po.reject');
    Route::get('/download-pdf/{filename}', [PurchaseOrderController::class, 'downloadPDF'])->name('po.download');
    Route::delete('/purchaseOrder/{id}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
    Route::post('/purchaseOrder/rejectAll', [PurchaseOrderController::class, 'rejectAll'])->name('po.rejectAll');
    Route::get('/purchase-orders/export', [PurchaseOrderController::class, 'exportExcel'])->name('po.export');
    Route::get('/purchaseOrder/{id}/edit', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::put('/purchaseOrder/{po}', [PurchaseOrderController::class, 'update'])->name('po.update');
    Route::post('purchase-orders/approve-selected', [PurchaseOrderController::class, 'approveSelected'])->name('purchase_orders.approve_selected');
    Route::post('purchase-orders/reject-selected', [PurchaseOrderController::class, 'rejectSelected'])->name('purchase_orders.reject_selected');
    Route::get('purchaseOrders/dashboard', [PurchaseOrderController::class, 'dashboard'])->name('po.dashboard');
    Route::get('/purchase-orders/filter', [PurchaseOrderController::class, 'filter']);
    Route::get('/purchase-orders/vendor-monthly-totals', [PurchaseOrderController::class, 'vendorMonthlyTotals'])->name('po.vendor-monthly-totals');
    Route::get('/purchase-orders/vendor-details', [PurchaseOrderController::class, 'getVendorDetails']);
    Route::put('/purchase-orders/cancel/{id}', [PurchaseOrderController::class, 'cancel'])->name('po.cancel');

    // Delivery Notes
    Route::get('/delivery-notes', DeliveryNoteIndex::class)->name('delivery-notes.index');
    Route::get('/delivery-note/create', DeliveryNoteForm::class)->name('delivery-notes.create');
    Route::get('/delivery-notes/{note}', DeliveryNoteShow::class)->name('delivery-notes.show');
    Route::get('/delivery-notes/{note}/print', DeliveryNotePrint::class)->name('delivery-notes.print');

    // Supplier Evaluation
    Route::get('purc/evaluationsupplier/index', [PurchasingSupplierEvaluationController::class, 'index'])->name('purchasing.evaluationsupplier.index');
    Route::post('purc/evaluationsupplier/generate', [PurchasingSupplierEvaluationController::class, 'calculate'])->name('purchasing.evaluationsupplier.calculate');
    Route::get('purc/evaluationsupplier/details/{id}', [PurchasingSupplierEvaluationController::class, 'details'])->name('purchasing.evaluationsupplier.details');
    Route::get('purc/vendorclaim', [PurchasingSupplierEvaluationController::class, 'kriteria1'])->name('kriteria1');
    Route::get('purc/vendoraccuracygood', [PurchasingSupplierEvaluationController::class, 'kriteria2'])->name('kriteria2');
    Route::get('purc/vendorontimedelivery', [PurchasingSupplierEvaluationController::class, 'kriteria3'])->name('kriteria3');
    Route::get('purc/vendorurgentrequest', [PurchasingSupplierEvaluationController::class, 'kriteria4'])->name('kriteria4');
    Route::get('purc/vendorclaimresponse', [PurchasingSupplierEvaluationController::class, 'kriteria5'])->name('kriteria5');
    Route::get('purc/vendorlistcertificate', [PurchasingSupplierEvaluationController::class, 'kriteria6'])->name('kriteria6');

    // Purchasing Details & Material
    Route::get('/foremind-detail', [PurchasingController::class, 'indexhome'])->name('purchasing_home');
    Route::get('/foremind-detail/print', [PurchasingDetailController::class, 'index']);
    Route::get('/foremind-detail/printCustomer', [PurchasingDetailController::class, 'indexcustomer']);
    Route::get('/foremind-detail/print/excel/{vendor_code}', [PurchasingDetailController::class, 'exportExcel']);
    Route::get('/foremind-detail/print/customer/excel/{vendor_code}', [PurchasingDetailController::class, 'exportExcelcustomer']);

    Route::get('/purchasing/material/{vendor_code}', [PurchasingMaterialController::class, 'index'])->name('purchasing.material.index');

    // Material Prediction
    Route::get('/materialPrediction', [materialPredictionController::class, 'index'])->name('material.prediction.index');
    Route::get('/materialPrediction/detail/{code}', [materialPredictionController::class, 'detail'])->name('material.prediction.detail');
});
