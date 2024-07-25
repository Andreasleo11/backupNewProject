<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FixPurchaseRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // fix purchase requests
        $this->updateDepartmentIsOffice();
        $this->fixPurchaseRequetType();
        $this->updatePRPE();
        $this->fixPurchaseRequestDetails();
        $this->fixPRBranch();
    }

    private function updateDepartmentIsOffice()
    {
        $officeDepartments = ['BUSINESS', 'COMPUTER', 'PERSONALIA', 'ACCOUNTING', 'PURCHASING', 'PE'];
        \App\Models\Department::whereIn('name', $officeDepartments)->update([
            'is_office' => true
        ]);

        \App\Models\Department::whereNotIn('name', $officeDepartments)->update([
            'is_office' => false
        ]);
    }

    private function fixPurchaseRequetType()
    {
        $chunkSize = 100; // Adjust the chunk size as needed

        // Step 1: Retrieve office departments
        $officeDepartments = \App\Models\Department::where('is_office', true)->pluck('name')->toArray();

        // Update office types in chunks
        \App\Models\PurchaseRequest::whereIn('from_department', $officeDepartments)
            ->chunkById($chunkSize, function ($purchaseRequests) {
                foreach ($purchaseRequests as $purchaseRequest) {
                    $purchaseRequest->type = 'office';
                    $purchaseRequest->save();
                }
            });

        // Update factory types in chunks
        \App\Models\PurchaseRequest::whereNotIn('from_department', $officeDepartments)
            ->chunkById($chunkSize, function ($purchaseRequests) {
                foreach ($purchaseRequests as $purchaseRequest) {
                    $purchaseRequest->type = 'factory';
                    $purchaseRequest->save();
                }
            });
    }

    private function updatePRPE()
    {
        \App\Models\PurchaseRequest::where('from_department', 'PE')->where('to_department', 'Maintenance')->update(['type' => 'factory']);
    }

    private function fixPurchaseRequestDetails()
    {
        $prs = \App\Models\PurchaseRequest::where('status', 4)->where('type', 'factory')->get();
        foreach ($prs as $pr) {
            \App\Models\DetailPurchaseRequest
                ::where('purchase_request_id', $pr->id)
                ->where('deleted_at', null)
                ->where('is_approve_by_head', true)
                ->update([
                    'is_approve_by_gm' => true,
                    'is_approve' => true
                ]);
        }
    }

    private function fixPRBranch()
    {
        \App\Models\PurchaseRequest::whereNull('branch')->update(['branch' => 'JAKARTA']);
    }
}
