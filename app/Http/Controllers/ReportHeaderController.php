<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Detail;
use App\Models\MasterDatafgDaijo;

class ReportHeaderController extends Controller
{
    public function create()
    {
        $data = MasterDatafgDaijo::all();
        dd($data);

        return view('qaqc.reports.create_header');
    }

    public function store(Request $request)
    {
            $data = $request->all();

            foreach ($data['remark'] as $key => &$values) {
                $modifiedValues = [];

                $index = 0;
                while ($index < count($values)) {
                    // Check if the value is "other"
                    if ($values[$index] === 'other') {
                        // Check if there is a next index and a next value
                        if (isset($values[$index + 1])) {
                            // Replace "other" with the value from the next index
                            $modifiedValues[] = $values[$index + 1];
                            // Skip the next value
                            $index += 2;
                        }
                    } else {
                        // Keep non-"other" values
                        $modifiedValues[] = $values[$index];
                        $index++;
                    }
                }

                // Update the original array with the modified values
                $data['remark'][$key] = $modifiedValues;
            }

            // Remove the reference to $values
            unset($values);

            // dd($data);


            // Extract common attributes
            $commonAttributes = [
                'Rec_Date' => $data['Rec_Date'],
                'Verify_Date' => $data['Verify_Date'],
                'Customer' => $data['Customer'],
                'Invoice_No' => $data['Invoice_No'],
                'created_by' => auth()->user()->name,
                'num_of_parts' => $data['num_of_parts'],
            ];

            // Create the VerificationReportHeader and get its doc_num

            $report = Report::create($commonAttributes);


            // Save the main data to the database, including defect details
            foreach ($data['part_names'] as $key => $partName) {
                $customerDefectDetails = $data['customer_defect_detail'][$key] ?? [];
                $daijoDefectDetails = $data['daijo_defect_detail'][$key] ?? [];
                $Remarks = $data['remark'][$key] ?? [];


                $attributes = [
                    'Report_Id' => $report->id,
                    'Part_Name' => $partName,
                    'Rec_Quantity' => $data['rec_quantity'][$key],
                    'Verify_Quantity' => $data['verify_quantity'][$key],
                    'Prod_Date' => $data['prod_date'][$key],
                    'Shift' => $data['shift'][$key],
                    'Can_Use' => $data['can_use'][$key],
                    'Cant_use' => $data['cant_use'][$key],
                    // Extract defect details and remarks
                    // Assign values to attributes
                    'Customer_Defect_Detail' => json_encode($customerDefectDetails),
                    'Daijo_Defect_Detail' => json_encode($daijoDefectDetails),
                    'Remark' => json_encode($Remarks),
                ];

            Detail::create($attributes);
            }


        return redirect()->back()->with('success', 'Verification report has been stored successfully.');
    }
}
