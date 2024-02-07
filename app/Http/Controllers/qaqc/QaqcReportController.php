<?php

namespace App\Http\Controllers\qaqc;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Detail;
use Barryvdh\DomPDF\Facade\Pdf;

class QaqcReportController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $reports = Report::get();

        return view('qaqc.reports.index',compact('reports'));
    }

    public function detail($id)
    {
        $report = Report::with('details')->find($id);
        // $user = Auth::user();
        $user =  Auth::user();
        foreach($report->details as $pd){
            $data1 = json_decode($pd->daijo_defect_detail);
            $data2 = json_decode($pd->customer_defect_detail);
            $data3 = json_decode($pd->remark);

            $pd->daijo_defect_detail = $data1;
            $pd->customer_defect_detail = $data2;
            $pd->remark = $data3;
        }

        $autographNames = [
            'autograph_name_1' => $report->autograph_user_1 ?? null,
            'autograph_name_2' => $report->autograph_user_2 ?? null,
            'autograph_name_3' => $report->autograph_user_3 ?? null,
        ];
        return view('qaqc.reports.detail', compact('report','user','autographNames'));
    }

    public function edit($id)
    {
        $report = Report::find($id);
        $details = $report->details;

        return view('qaqc.reports.edit', compact('report','details'));
    }

    public function update(Request $request, $id)
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


        if (isset($data['customer_defect_detail'])) {
            $data['customer_defect_detail'] = array_map(function ($array) {
                $foundNull = false;
                return array_filter($array, function ($value) use (&$foundNull) {
                    if ($value === null && !$foundNull) {
                        $foundNull = true;
                        return false;
                    }
                    return true;
                });
            }, $data['customer_defect_detail']);
        }

        // Remove only the first null value from daijo_defect_detail
        if (isset($data['daijo_defect_detail'])) {
            $data['daijo_defect_detail'] = array_map(function ($array) {
                $foundNull = false;
                return array_filter($array, function ($value) use (&$foundNull) {
                    if ($value === null && !$foundNull) {
                        $foundNull = true;
                        return false;
                    }
                    return true;
                });
            }, $data['daijo_defect_detail']);
        }

        // dd($data);

        // Update the existing record
        $report = Report::findOrFail($id);

        // Update common attributes
        $report->update([
            'Rec_Date' => $data['rec_Date'],
            'Verify_Date' => $data['verify_date'],
            'Customer' => $data['customer'],
            'Invoice_No' => $data['invoice_no'],
            'num_of_parts' => $data['num_of_parts'],
            'is_approve' => null,
            'description' => null,
        ]);

        // Update details
        foreach ($data['part_names'] as $key => $partName) {
            $customerDefectDetails = $data['customer_defect_detail'][$key] ?? [];
            $daijoDefectDetails = $data['daijo_defect_detail'][$key] ?? [];
            $Remarks = $data['remark'][$key] ?? [];

            $detail = Detail::where('Report_Id', $id)->where('Part_Name', $partName)->first();

            // Update detail attributes
            $detail->update([
                'Part_Name' => $partName,
                'Rec_Quantity' => $data['rec_quantity'][$key],
                'Verify_Quantity' => $data['verify_quantity'][$key],
                'Prod_Date' => $data['prod_date'][$key],
                'Shift' => $data['shift'][$key],
                'Can_Use' => $data['can_use'][$key],
                'Cant_use' => $data['cant_use'][$key],
                // Extract defect details and remarks
                // Add other attributes as needed
                'Customer_Defect_Detail' => json_encode($customerDefectDetails),
                'Daijo_Defect_Detail' => json_encode($daijoDefectDetails),
                'Remark' => json_encode($Remarks),
            ]);
        }

        // Redirect to a view or route after the update
        return redirect()->route('qaqc.report.index')->with('success', 'Report has been updated successfully!');
    }

    public function create()
    {
        return view('qaqc.reports.create');
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


        return redirect()->route('qaqc.report.index')->with('success', 'Report has been stored successfully!');
    }

    public function destroy($id){
        $report = Report::findOrFail($id);

        $report->details()->delete();
        $report->delete();


        return redirect()->route('qaqc.report.index')->with('success', 'Report has been deleted successfully!');
    }

    public function uploadAttachment(Request $request)
    {
        $request->validate([
            'attachment' => 'required|mimes:pdf,doc,docx,xlsx,xls|max:5120', // Adjust allowed file types and size
            'reportId' => 'required|exists:reports,id',
        ]);

        $reportId = $request->input('reportId');

        Report::where('id', $reportId)->update([
            'is_approve' => null,
            'description' => null,
        ]);


        $file = $request->file('attachment');

        // Generate a unique filename
        $filename = time() . '_' . $file->getClientOriginalName();

        // Move the uploaded file to a storage location (you can customize the storage path)
        $file->storeAs('public/attachments', $filename);

        // Update the reports table with the attachment filename
        Report::where('id', $reportId)->update(['attachment' => $filename]);

        return redirect()->back()->with('success', 'Attachment uploaded and saved successfully!');
    }

    public function saveImagePath(Request $request, $reportId, $section)
    {
        $username = Auth::check() ? Auth::user()->name : '';
        $imagePath = $username . '.png';

        // Save $imagePath to the database for the specified $reportId and $section
        $report = Report::find($reportId);
            $report->update([
                "autograph_{$section}" => $imagePath
            ]);
            $report->update([
                "autograph_user_{$section}" => $username
            ]);

        return response()->json(['success' => 'Autograph saved successfully!']);
    }

    public function exportToPdf($id)
    {
        $report = Report::with('details')->find($id);
        $user =  Auth::user();
        foreach($report->details as $pd){
            $data1 = json_decode($pd->daijo_defect_detail);
            $data2 = json_decode($pd->customer_defect_detail);
            $data3 = json_decode($pd->remark);

            $pd->daijo_defect_detail = $data1;
            $pd->customer_defect_detail = $data2;
            $pd->remark = $data3;
        }

        $autographNames = [
            'autograph_name_1' => $report->autograph_user_1 ?? null,
            'autograph_name_2' => $report->autograph_user_2 ?? null,
            'autograph_name_3' => $report->autograph_user_3 ?? null,
        ];

        $pdf = Pdf::loadView('pdf/verification-report-pdf', compact('report', 'user', 'autographNames'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('verification-report-'. $report->id . '.pdf');
    }

    public function previewPdf($id)
    {
        $report = Report::with('details')->find($id);
        $user =  Auth::user();
        foreach($report->details as $pd){
            $data1 = json_decode($pd->daijo_defect_detail);
            $data2 = json_decode($pd->customer_defect_detail);
            $data3 = json_decode($pd->remark);

            $pd->daijo_defect_detail = $data1;
            $pd->customer_defect_detail = $data2;
            $pd->remark = $data3;
        }

        $autographNames = [
            'autograph_name_1' => $report->autograph_user_1 ?? null,
            'autograph_name_2' => $report->autograph_user_2 ?? null,
            'autograph_name_3' => $report->autograph_user_3 ?? null,
        ];

        return view('pdf/verification-report-pdf', compact('report', 'user', 'autographNames'));

        // $pdf = Pdf::loadView('pdf/verification-report-pdf', compact('report', 'user', 'autographNames'))
        // ->setPaper('a4', 'landscape');

        // return $pdf->stream('verification-report-'. $report->id . '.pdf');
    }
}
