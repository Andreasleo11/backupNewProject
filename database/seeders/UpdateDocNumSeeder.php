<?php

namespace Database\Seeders;

use App\Models\File;
use App\Models\PurchaseRequest;
use DB;
use Illuminate\Database\Seeder;

class UpdateDocNumSeeder extends Seeder
{
    public function run()
    {
        // Map department names to codes
        $departmentCodes = [
            'Accounting' => 'ACU',
            'Assembly' => 'ASM',
            'Business' => 'BUS',
            'Computer' => 'CP',
            'HRD' => 'HRD',
            'Personnel' => 'HRD',
            'Maintenance' => 'MT',
            'Maintenance Machine' => 'MTM',
            'Moulding' => 'MLD',
            'Plastic Injection' => 'PI',
            'PPIC' => 'PIC',
            'Purchasing' => 'PUR',
            'QA' => 'QA',
            'QC' => 'QC',
            'Second Process' => 'SPC',
            'Store' => 'STR',
            'Logistic' => 'LOG',
            'PE' => 'PE',
        ];

        $branchCodes = [
            'JAKARTA' => 'JKT',
            'KARAWANG' => 'KRW',
        ];

        DB::transaction(function () use ($departmentCodes, $branchCodes) {
            $purchaseRequests = PurchaseRequest::all();

            foreach ($purchaseRequests as $purchaseRequest) {
                // Store the old doc_num
                $oldDocNum = $purchaseRequest->doc_num;

                // Get the date portion
                $date = $purchaseRequest->created_at->format('ymd');

                // Get the department code
                $department = $purchaseRequest->to_department;
                $branchCode = $departmentCodes[$department] ?? 'UNK';

                $branch = $purchaseRequest->branch;
                $areaCode = $branchCodes[$branch] ?? 'UNK';

                // Fetch the last record's doc_num for the current date and branch code
                $latest = PurchaseRequest::where('doc_num', 'like', "%/PR/%/{$date}/%")
                    ->orderBy('id', 'desc')
                    ->first();

                if ($latest) {
                    $lastIncrement = (int) substr($latest->doc_num, -3);
                } else {
                    $lastIncrement = 0;
                }

                $increment = str_pad($lastIncrement + 1, 3, '0', STR_PAD_LEFT);
                $newDocNum = "{$branchCode}/PR/{$areaCode}/{$date}/{$increment}";

                // Update the purchase request with the new doc_num
                $purchaseRequest->update(['doc_num' => $newDocNum]);

                $updated = File::where('doc_id', $oldDocNum)->update(['doc_id' => $newDocNum]);
            }
        });
    }
}
