<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use App\Models\PurchaseRequest;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SpecificationSeeder::class,
            PermissionSeeder::class
        ]);

        // fix purchase requests
        $this->updateDepartmentIsOffice();
        $this->fixPurchaseRequetType();
    }

    private function updateDepartmentIsOffice()
    {
        $officeDepartments = ['BUSINESS', 'COMPUTER', 'PERSONALIA', 'ACCOUNTING', 'PURCHASING', 'PE'];
        Department::whereIn('name', $officeDepartments)->update([
            'is_office' => true
        ]);

        Department::whereNotIn('name', $officeDepartments)->update([
            'is_office' => false
        ]);
    }

    private function fixPurchaseRequetType()
    {
        $chunkSize = 100; // Adjust the chunk size as needed

        // Step 1: Retrieve office departments
        $officeDepartments = Department::where('is_office', true)->pluck('name')->toArray();

        // Update office types in chunks
        PurchaseRequest::whereIn('from_department', $officeDepartments)
            ->chunkById($chunkSize, function ($purchaseRequests) {
                foreach ($purchaseRequests as $purchaseRequest) {
                    $purchaseRequest->type = 'office';
                    $purchaseRequest->save();
                }
            });

        // Update factory types in chunks
        PurchaseRequest::whereNotIn('from_department', $officeDepartments)
            ->chunkById($chunkSize, function ($purchaseRequests) {
                foreach ($purchaseRequests as $purchaseRequest) {
                    $purchaseRequest->type = 'factory';
                    $purchaseRequest->save();
                }
            });

    }
}
