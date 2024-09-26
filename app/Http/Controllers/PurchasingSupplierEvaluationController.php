<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchasingListPo;
use App\Models\PurchasingVendorAccuracyGood;
use App\Models\PurchasingVendorClaim;
use App\Models\PurchasingVendorClaimResponse;
use App\Models\PurchasingVendorListCertificate;
use App\Models\PurchasingVendorOntimeDelivery;
use App\Models\PurchasingVendorUrgentRequest;
use App\Models\PurchasingHeaderEvaluationSupplier;
use App\Models\PurchasingDetailEvaluationSupplier;
use Carbon\Carbon;


class PurchasingSupplierEvaluationController extends Controller
{
    public function index()
    {
        $master = PurchasingListPo::get();
        $point1 = PurchasingVendorClaim::get();
        $point2 = PurchasingVendorAccuracyGood::get();
        $point3 = PurchasingVendorOntimeDelivery::get();
        $point4 = PurchasingVendorUrgentRequest::get();
        $point5 = PurchasingVendorClaimResponse::get();
        $point6 = PurchasingVendorListCertificate::get();
        $header = PurchasingHeaderEvaluationSupplier::get();

        $supplierData = [];

            $masters = PurchasingListPo::select('supplier_name', 'posting_date')
                ->distinct()
                ->get();

            // Group by supplier names
            foreach ($masters->groupBy('supplier_name') as $supplier_name => $records) {
                $years = [];

                // Get distinct years from posting_date for each supplier
                foreach ($records as $record) {
                    // Extract year from posting_date
                    $year = Carbon::parse($record->posting_date)->format('Y');
                    $years[] = $year;
                }

                // Store distinct years in the array for each supplier, with re-indexing
                $supplierData[$supplier_name] = array_values(array_unique($years));
            }
            

        return view('purchasing.evaluationsupplier.supplier_selection', compact('supplierData', 'header'));
        
    }   
    
    public function calculate(Request $request)
    {
        $request->validate([
            'supplier' => 'required|string',
            'year' => 'required|numeric',
        ]);
    
        // Extract the supplier and year from the request
        $supplierName = $request->input('supplier');
        $year = $request->input('year');
    
        // Step 1: Find the supplier in PurchasingListPo to get the supplier_code (vendor_code)
        $supplier = PurchasingListPo::where('supplier_name', $supplierName)->first();
    
        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }
    
        // dd($supplier);
        // Step 2: Create the header using the supplier's code as vendor_code
        $header = PurchasingHeaderEvaluationSupplier::create([
            'doc_num' => $this->generateDocNum(), // Custom method to generate doc_num
            'vendor_code' => $supplier->supplier_code, // Assign the supplier_code as vendor_code
            'vendor_name' => $supplierName,
            'year' => $year,
            'grade' => null, // Grade will be updated later
            'status' => null, // Status will be updated later
        ]);
    
        // Step 3: Find all months from PurchasingListPo where supplier_name matches and posting_date is in the same year
        $matchingPurchasingList = PurchasingListPo::where('supplier_name', $supplierName)
            ->whereYear('posting_date', $year)
            ->orderBy('posting_date') // Order by posting_date
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->posting_date)->format('Y-m'); // Group by Year-Month
            });
        // dd($matchingPurchasingList);
        // Step 4: Create details for each month found in the matchingPurchasingList
        foreach ($matchingPurchasingList as $monthGroup) {
            // Get the first record of the month
            $firstRecord = $monthGroup->first();
            $month = Carbon::parse($firstRecord->posting_date)->format('F'); // Get the month name (e.g., "January")
    
            PurchasingDetailEvaluationSupplier::create([
                'header_id' => $header->id, // Associate with the created header
                'month' => $month, // Month name from posting_date
                'kualitas_barang' => null, // Placeholder for now, will be updated later
                'ketepatan_kuantitas_barang' => null, // Placeholder for now, will be updated later
                'ketepatan_waktu_pengiriman' => null, // Placeholder for now, will be updated later
                'kerjasama_permintaan_mendadak' => null, // Placeholder for now, will be updated later
                'respon_klaim' => null, // Placeholder for now, will be updated later
                'sertifikasi' => null, // Placeholder for now, will be updated later
            ]);
        }

        $data = PurchasingHeaderEvaluationSupplier::with('details')
        ->where('id', $header->id)->first();
        // dd($data);




        //kriteria 1
        // Step 5: Check if the vendor is found in the PurchasingVendorClaim table
        $claims = PurchasingVendorClaim::where('vendor_name', $supplierName)
        ->whereYear('claim_start_date', $year)
        ->get();

        if ($claims->isEmpty()) {
            // Vendor not found, set kualitas_barang to 30 for all details
            foreach ($data->details as $detail) {
                $detail->kualitas_barang = 30;
                $detail->save();
            }
        } else {
            // Vendor found, process claims
            foreach ($data->details as $detail) {
                $month = Carbon::parse($detail->month)->format('m'); // Extract month from detail
                $monthlyClaims = $claims->filter(function($claim) use ($month) {
                    return Carbon::parse($claim->claim_start_date)->format('m') == $month;
                });

                if ($monthlyClaims->isEmpty()) {
                    $detail->kualitas_barang = 30; // No claims for this month
                } else {
                    // Calculate points based on can_use status
                    $totalPoints = 0;
                    $claimCount = $monthlyClaims->count();

                    foreach ($monthlyClaims as $claim) {
                        if (is_null($claim->risk) || $claim->risk == '') {
                            $totalPoints += 100; // risk is null or blank
                        } else if ($claim->risk == 'Low') {
                            $totalPoints += 50;  // risk is 'Low'
                        } else if ($claim->risk == 'High') {
                            $totalPoints += 0;   // risk is 'High'
                        }
                    }

                    // Calculate average and apply 30%
                    $averagePoints = $totalPoints / $claimCount;
                    $detail->kualitas_barang = $averagePoints * 0.30; // Apply 30%
                }

                $detail->save(); // Save the updated detail
            }
        }
        //kriteria 1 

        //kriteria 2 
        $accuracyGoods = PurchasingVendorAccuracyGood::where('vendor_name', $supplierName)
        ->whereYear('incoming_date', $year)
        ->get();

        if ($accuracyGoods->isEmpty()) {
            // Vendor not found, set ketepatan_kuantitas_barang to 20 for all details
            foreach ($data->details as $detail) {
                $detail->ketepatan_kuantitas_barang = 20;
                $detail->save();
            }
        } else {
            // Vendor found, process the accuracy data
            foreach ($data->details as $detail) {
                $month = Carbon::parse($detail->month)->format('m'); // Extract month from detail
                $monthlyAccuracyGoods = $accuracyGoods->filter(function($good) use ($month) {
                    return Carbon::parse($good->incoming_date)->format('m') == $month;
                });

                if ($monthlyAccuracyGoods->isEmpty()) {
                    // No data found for this month, set ketepatan_kuantitas_barang to 100 * 20% = 20
                    $detail->ketepatan_kuantitas_barang = 20;
                } else {
                    // Deduct 5 points for each data entry found for the month
                    $deductions = $monthlyAccuracyGoods->count() * 5;
                    $finalScore = max(0, 100 - $deductions); // Ensure score doesn't go below 0

                    // Apply 20% of the final score
                    $detail->ketepatan_kuantitas_barang = ceil($finalScore * 0.20); // Round up
                }

                $detail->save(); // Save the updated detail
            }
        }
        //kriteria 2 

        //kriteria 3 
        $ontimeDeliveries = PurchasingVendorOntimeDelivery::where('vendor_name', $supplierName)
        ->whereYear('actual_date', $year)
        ->get();

        if ($ontimeDeliveries->isEmpty()) {
            // Vendor not found, set ketepatan_waktu_pengiriman to 20 for all details
            foreach ($data->details as $detail) {
                $detail->ketepatan_waktu_pengiriman = 20;
                $detail->save();
            }
        } else {
            // Vendor found, process the on-time delivery data
            foreach ($data->details as $detail) {
                $month = Carbon::parse($detail->month)->format('m'); // Extract month from detail
                $monthlyDeliveries = $ontimeDeliveries->filter(function($delivery) use ($month) {
                    return Carbon::parse($delivery->actual_date)->format('m') == $month;
                });

                if ($monthlyDeliveries->isEmpty()) {
                    // No data found for this month, set ketepatan_waktu_pengiriman to 100 * 20% = 20
                    $detail->ketepatan_waktu_pengiriman = 20;
                } else {
                    // Process each delivery for the month
                    $totalScore = 0;
                    $count = $monthlyDeliveries->count();

                    foreach ($monthlyDeliveries as $delivery) {
                        $daysDifference = Carbon::parse($delivery->actual_date)->diffInDays(Carbon::parse($delivery->request_date));

                        if ($daysDifference == 1) {
                            $totalScore += 90;
                        } elseif ($daysDifference == 2) {
                            $totalScore += 80;
                        } elseif ($daysDifference == 3) {
                            $totalScore += 70;
                        } else {
                            $totalScore += 50;
                        }
                    }

                    // Calculate the average score for the month
                    $averageScore = $totalScore / $count;

                    // Apply 20% of the average score
                    $finalScore = ceil($averageScore * 0.20); // Round up

                    // Update the detail with the final score
                    $detail->ketepatan_waktu_pengiriman = $finalScore;
                }

                $detail->save(); // Save the updated detail
            }
        }
        //kriteria 3 


        //kriteria 4 
        $urgentRequests = PurchasingVendorUrgentRequest::where('vendor_name', $supplierName)
        ->whereYear('request_date', $year)
        ->get();

        if ($urgentRequests->isEmpty()) {
            // Vendor not found, set kerjasama_permintaan_mendadak to 10 for all details
            foreach ($data->details as $detail) {
                $detail->kerjasama_permintaan_mendadak = 10;
                $detail->save();
            }
        } else {
            // Vendor found, process the urgent request data
            foreach ($data->details as $detail) {
                $month = Carbon::parse($detail->month)->format('m'); // Extract month from detail
                $monthlyRequests = $urgentRequests->filter(function($request) use ($month) {
                    return Carbon::parse($request->request_date)->format('m') == $month;
                });

                if ($monthlyRequests->isEmpty()) {
                    // No data found for this month, set kerjasama_permintaan_mendadak to 10
                    $detail->kerjasama_permintaan_mendadak = 10;
                } else {
                    // Process each urgent request for the month
                    $totalScore = 0;
                    $count = $monthlyRequests->count();

                    foreach ($monthlyRequests as $request) {
                        // Convert the string dates to Carbon instances
                        $requestDate = Carbon::parse($request->request_date);
                        $incomingDate = Carbon::parse($request->incoming_date);
                    
                        if ($requestDate->eq($incomingDate)) {
                            if ($request->special_price === 'No') {
                                $totalScore += 100;
                            } else {
                                $totalScore += 80;
                            }
                        } else {
                            $totalScore += 50;
                        }
                    }

                    // Calculate the average score for the month
                    $averageScore = $totalScore / $count;

                    // Apply 10% of the average score
                    $finalScore = ceil($averageScore * 0.10); // Round up

                    // Update the detail with the final score
                    $detail->kerjasama_permintaan_mendadak = $finalScore;
                }

                $detail->save(); // Save the updated detail
            }
        }
        //kriteria 4 

        //kriteria 5 
        $claimResponses = PurchasingVendorClaimResponse::where('vendor_name', $supplierName)
        ->whereYear('cpar_sent_date', $year)
        ->get();

        // Step 2: Collect months available in PurchasingDetailEvaluationSupplier
        $monthsInDetails = PurchasingDetailEvaluationSupplier::where('header_id', $header->id)
            ->pluck('month') // Collect all months from the details
            ->map(function ($month) {
                return Carbon::parse($month)->format('Y-m'); // Normalize to Year-Month
            })
            ->toArray();

        if ($claimResponses->isEmpty()) {
            // If no data found, update all details in data to 10 for respon_klaim
            PurchasingDetailEvaluationSupplier::where('header_id', $header->id)
                ->update(['respon_klaim' => 10]);
        } else {
            $monthlyClaimResponses = $claimResponses->groupBy(function ($item) {
                return Carbon::parse($item->cpar_sent_date)->format('Y-m'); // Group by Year-Month
            });

            foreach ($monthlyClaimResponses as $month => $responses) {
                $totalScore = 0;
                $count = 0;

                foreach ($responses as $response) {
                    $sentDate = Carbon::parse($response->cpar_sent_date);
                    $responseDate = Carbon::parse($response->cpar_response_date);
                    $daysDifference = $responseDate->diffInDays($sentDate);

                    if ($response->close_status === 'Yes') {
                        if ($daysDifference <= 7) {
                            $totalScore += 90;
                        } elseif ($daysDifference <= 14) {
                            $totalScore += 80;
                        } else {
                            $totalScore += 50;
                        }
                    } else {
                        $totalScore += 50;
                    }
                    $count++;
                }

                $averageScore = ($count > 0) ? ($totalScore / $count) * 0.10 : 0;

                PurchasingDetailEvaluationSupplier::where('header_id', $header->id)
                ->update(['respon_klaim' => 10]);
                // Update existing months with calculated scores
                PurchasingDetailEvaluationSupplier::where('header_id', $header->id)
                    ->where('month', Carbon::parse($month)->format('F'))
                    ->update(['respon_klaim' => round($averageScore)]);
            }

            // Check for months in claim responses not found in details and update them
            foreach ($monthlyClaimResponses as $month => $responses) {
                if (!in_array($month, $monthsInDetails)) {
                    // If month from claim responses is not found in details, update existing records for that month
                    PurchasingDetailEvaluationSupplier::where('header_id', $header->id)
                        ->where('month', Carbon::parse($month)->format('F'))
                        ->update(['respon_klaim' => 10]);
                }
            }
        }
        //kriteria 5 



        //kriteria 6 
        $certificates = PurchasingVendorListCertificate::where('vendor_name', $supplierName)->first();

        if (!$certificates) {
            // If no certificates found, update all details in data to 0 for sertifikasi
            PurchasingDetailEvaluationSupplier::where('header_id', $header->id)
                ->update(['sertifikasi' => 0]);
        } else {
            $sertifikasiScore = 5; // Default value if both documents are null

            if ($certificates->iatf_16949_doc !== null && trim($certificates->iatf_16949_doc) !== '') {
                $sertifikasiScore = 10;
            } elseif ($certificates->iso_9001_doc !== null && trim($certificates->iso_9001_doc) !== '') {
                $sertifikasiScore = 8;
            }

            // Update all details in data with the calculated sertifikasi score
            PurchasingDetailEvaluationSupplier::where('header_id', $header->id)
                ->update(['sertifikasi' => $sertifikasiScore]);
        }
        //kriteria 6 

        $details = PurchasingDetailEvaluationSupplier::where('header_id', $header->id)->get();
        // dd($details);

        $totalSum = $details->sum(function ($detail) {
            return $detail->kualitas_barang + 
                   $detail->ketepatan_kuantitas_barang + 
                   $detail->ketepatan_waktu_pengiriman + 
                   $detail->kerjasama_permintaan_mendadak + 
                   $detail->respon_klaim + 
                   $detail->sertifikasi;
        });
    
        $count = $details->count();
        $averageScore = ($count > 0) ? $totalSum / ($count) : 0;

        // dd($averageScore);

        $grade = $this->calculateGrade($averageScore); // Implement this function as needed
        $status = $this->determineStatus($averageScore); // Implement this function as needed
    
        // Update header
        PurchasingHeaderEvaluationSupplier::where('id', $header->id)
            ->update([
                'grade' => $grade,
                'status' => $status
            ]);
    
        // Redirect or return a success message

        
        return redirect()->route('purchasing.evaluationsupplier.index')
                     ->with('success', 'Header and details updated successfully.');

        // return response()->json([
        //     'message' => 'Header and details created successfully.',
        //     'header_id' => $header->id,
        // ]);
    }

    private function generateDocNum()
    {
        // You can implement any custom logic to generate the doc_num
        return 'DOC-' . now()->format('YmdHis'); // Example: DOC-20240917123000
    }

    protected function calculateGrade($averageScore)
    {
        // Implement your logic to determine the grade based on averageScore
        if ($averageScore >= 81) {
            return 'A';
        } elseif ($averageScore >= 61) {
            return 'B';
        } else{
            return 'C';
        } 
    }

    protected function determineStatus($averageScore)
    {
        // Implement your logic to determine the status based on averageScore
        if ($averageScore >= 81) {
            return 'Diteruskan';
        } elseif ($averageScore >= 61) {
            return 'Dipertahankan dan dilakukan Audit Supplier setelah 1-3 bulan dari Evaluasi Supplier tahunan';
        }  else {
            return 'Dilakukan Monitoring performa selama 3 bulan dan dilakukan Audit Supplier di bulan berikutnya. Gradenya harus naik, bila gradenya tidak naik, akan dipertimbangkan untuk pemutusan kerjasama.';
        }
    }


    public function details($id)
    {
        // Fetch the header along with the related details
        $header = PurchasingHeaderEvaluationSupplier::with('details')->findOrFail($id);
        
        $detailsCount = $header->details->count();
        
        // Define all 12 months as an array
        $allMonths = [
            'January', 'February', 'March', 'April', 'May', 'June', 
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Initialize the result array for months and rata-rata
        $result = [];

        // Initialize sums and counts for each category (for rata-rata calculation)
        $categorySums = [
            'kualitas_barang' => 0,
            'ketepatan_kuantitas_barang' => 0,
            'ketepatan_waktu_pengiriman' => 0,
            'kerjasama_permintaan_mendadak' => 0,
            'respon_klaim' => 0,
            'sertifikasi' => 0
        ];

        $categoryCounts = [
            'kualitas_barang' => 0,
            'ketepatan_kuantitas_barang' => 0,
            'ketepatan_waktu_pengiriman' => 0,
            'kerjasama_permintaan_mendadak' => 0,
            'respon_klaim' => 0,
            'sertifikasi' => 0
        ];

        // Loop through each month and populate the result array
        foreach ($allMonths as $month) {
            // Get the detail for the specific month, or default to zero if no data exists
            $detailsForMonth = $header->details->firstWhere('month', $month);

            $result[$month] = [
                'kualitas_barang' => $detailsForMonth->kualitas_barang ?? 0,
                'ketepatan_kuantitas_barang' => $detailsForMonth->ketepatan_kuantitas_barang ?? 0,
                'ketepatan_waktu_pengiriman' => $detailsForMonth->ketepatan_waktu_pengiriman ?? 0,
                'kerjasama_permintaan_mendadak' => $detailsForMonth->kerjasama_permintaan_mendadak ?? 0,
                'respon_klaim' => $detailsForMonth->respon_klaim ?? 0,
                'sertifikasi' => $detailsForMonth->sertifikasi ?? 0,
            ];

            // Add to sums and counts (only if the value is greater than 0)
            foreach ($result[$month] as $category => $value) {
                if ($value > 0) {
                    $categorySums[$category] += $value;
                    $categoryCounts[$category]++;
                }
            }
        }

        // Calculate rata-rata for each category
        $result['rata-rata'] = [];
        foreach ($categorySums as $category => $sum) {
            $result['rata-rata'][$category] = $categoryCounts[$category] > 0 
                ? $sum / $detailsCount
                : 0; // If no non-zero values, set rata-rata to 0
        }
        // dd($result);
        // Pass the grouped monthly data to the view
        return view('purchasing.evaluationsupplier.supplier_detail', compact('header', 'result'));
    }

    public function kriteria1(Request $request)
    {
        $query = PurchasingVendorClaim::query();

        // Apply filter if vendor_name is provided
        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'like', '%' . $request->vendor_name . '%');
        }

        $datas = $query->get()->map(function ($data) {
            $data->incoming_date = \Carbon\Carbon::parse($data->incoming_date)->format('d-m-Y');
            $data->claim_start_date = \Carbon\Carbon::parse($data->claim_start_date)->format('d-m-Y');
            $data->claim_finish_date = \Carbon\Carbon::parse($data->claim_finish_date)->format('d-m-Y');
            return $data;
        });

        $vendorNames = PurchasingVendorClaim::distinct('vendor_name')->pluck('vendor_name');

        return view('purchasing.evaluationsupplier.kriteria1', compact('datas', 'vendorNames'));
    }

    public function kriteria2(Request $request)
    {
        // Query to get data
        $query = PurchasingVendorAccuracyGood::query();

        // Apply filter if vendor_name is provided
        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'like', '%' . $request->vendor_name . '%');
        }

        // Fetch data
        $datas = $query->get();

        // Get unique vendor names for the dropdown
        $vendorNames = PurchasingVendorAccuracyGood::distinct('vendor_name')->pluck('vendor_name');

        return view('purchasing.evaluationsupplier.kriteria2', compact('datas', 'vendorNames'));
    }

    public function kriteria3(Request $request)
    {
        // Query to get data
        $query = PurchasingVendorOntimeDelivery::query();

        // Apply filter if vendor_name is provided
        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'like', '%' . $request->vendor_name . '%');
        }

        // Fetch data
        $datas = $query->get();

        // Get unique vendor names for the dropdown
        $vendorNames = PurchasingVendorOntimeDelivery::distinct('vendor_name')->pluck('vendor_name');

        return view('purchasing.evaluationsupplier.kriteria3', compact('datas', 'vendorNames'));
    }

    public function kriteria4(Request $request)
    {
        // Query to get data
        $query = PurchasingVendorUrgentRequest::query();

        // Apply filter if vendor_name is provided
        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'like', '%' . $request->vendor_name . '%');
        }

        // Fetch data
        $datas = $query->get();

        // Get unique vendor names for the dropdown
        $vendorNames = PurchasingVendorUrgentRequest::distinct('vendor_name')->pluck('vendor_name');

        return view('purchasing.evaluationsupplier.kriteria4', compact('datas', 'vendorNames'));
    }

    public function kriteria5(Request $request)
    {
        // Query to get data
        $query = PurchasingVendorClaimResponse::query();

        // Apply filter if vendor_name is provided
        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'like', '%' . $request->vendor_name . '%');
        }

        // Fetch data
        $datas = $query->get();

        // Get unique vendor names for the dropdown
        $vendorNames = PurchasingVendorClaimResponse::distinct('vendor_name')->pluck('vendor_name');

        return view('purchasing.evaluationsupplier.kriteria5', compact('datas', 'vendorNames'));
    }

    public function kriteria6(Request $request)
    {
        // Query to get data
        $query = PurchasingVendorListCertificate::query();

        // Apply filter if vendor_name is provided
        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'like', '%' . $request->vendor_name . '%');
        }

        // Fetch data
        $datas = $query->get();

        // Get unique vendor names for the dropdown
        $vendorNames = PurchasingVendorListCertificate::distinct('vendor_name')->pluck('vendor_name');

        return view('purchasing.evaluationsupplier.kriteria6', compact('datas', 'vendorNames'));
    }
}
