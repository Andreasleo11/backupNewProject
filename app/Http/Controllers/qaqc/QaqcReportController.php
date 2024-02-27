<?php

namespace App\Http\Controllers\qaqc;

use App\Http\Controllers\Controller;
use App\Models\Defect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Detail;
use App\Models\MasterDataRog;
use App\Models\DefectCategory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Session;

class QaqcReportController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $reports = Report::paginate(10);

        return view('qaqc.reports.index',compact('reports'));
    }

    public function detail($id)
    {
        $report = Report::with('details', 'details.defects', 'details.defects.category' )->find($id);
        $user =  Auth::user();
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

    public function create(Request $request)
    {
        $header = $request->session()->get('header');
        return view('qaqc.reports.create', compact('header'));
    }

    public function getCustomers(Request $request)
    {
        $Customername = $request->input('customer_name');
        $cust = MasterDataRog::where('customer_name', 'like', "%$Customername%")->distinct()->pluck('customer_name')->toArray();


        return response()->json($cust);
    }






    public function getItems(Request $request)
    {
        $itemName = $request->input('item_name');
        $header = $request->session()->get('header');

        // Extract the customer name from the header
        $customerName = $header['Customer'] ?? null;

        $items = MasterDataRog::where('item_name', 'like', "%$itemName%")->where('customer_name', $customerName)->pluck('item_name')->toArray();


        return response()->json($items);
    }


    public function postDetail(Request $request)
    {

        $report = $request->session()->get('header');

        // Check if the report exists in the database
        if (!$report->exists) {
            // If the report exists, update its details
            $report->save();
        } else {
            // If the report doesn't exist, save it to get the ID
            $report->update();
        }

        // Retrieve the report_id from the saved or updated report
        $reportId = $report->id;

        $details = [];

        for($i = 1; $i <= $request->input('rowCount'); $i++){

            $request->validate([
                'itemName' . $i => 'required',
                'rec_quantity' . $i => 'required',
                'verify_quantity' . $i => 'required',
                'prod_date' . $i => 'required',
                'shift' . $i => 'required',
                'can_use' . $i => 'required',
                'cant_use' . $i => 'required',
            ]);

            $rowData = [
                'Report_Id' => $reportId,
                'Part_Name' => $request->input("itemName$i"),
                'Rec_Quantity' => $request->input("rec_quantity$i"),
                'Verify_Quantity' => $request->input("verify_quantity$i"),
                'Prod_Date' => $request->input("prod_date$i"),
                'Shift' => $request->input("shift$i"),
                'Can_Use' => $request->input("can_use$i"),
                'Cant_Use' => $request->input("cant_use$i"),
            ];
                $detail = Detail::where('Report_Id', $reportId)
                ->where('Part_Name', $rowData['Part_Name'])
                ->first();

                if (!$detail) {
                // If the detail doesn't exist, create a new one
                    $detail = new Detail();
                    $detail->fill($rowData);
                    $detail->save();
                } else {
                // If the detail exists, update its attributes
                    $detail->update($rowData);
                }

                $details[] = $detail;

        }

        $request->session()->put('details', $details);

        return redirect()->route('qaqc.report.createdefect');
    }

    public function showNewDefect()
    {
        $defectcat = DefectCategory::get();
        // dd($defectcat);

        return view('qaqc.reports.create-new-defect', compact('defectcat'));
    }

    public function addNewDefect(Request $request)
    {
        $request->validate(
            [
                'category_name' => 'required|string',
            ]
        );


        $newdefect = new DefectCategory();
        $newdefect->name = $request->input('category_name');
        $newdefect->save();

        return redirect()->route('qaqc.report.index')->with('success', 'Category added successfully!');

    }

    public function store(Request $request)
    {
            $data = $request->all();


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

    public function postCreateHeader(Request $request)
    {
        $validatedData = $request->validate([
            'Rec_Date' => 'date',
            'Verify_Date' => 'date',
            'Customer' => 'string',
            'Invoice_No' => 'string',
            'num_of_parts' => 'integer',
            'created_by' => 'string',
        ]);

        // $data = $request->session()->get('header');

        $report = $request->session()->get('header');

        // Check if the report exists in the session
        if ($report) {
            // If the report exists, update its attributes with the validated data
            $report->fill($validatedData);
        } else {
            // If the report doesn't exist, create a new report instance with the validated data
            $report = new Report($validatedData);
        }

        // Store the updated or new report in the session
        $request->session()->put('header', $report);

        return redirect()->route('qaqc.report.createdetail');
    }

    public function createDetail(Request $request)
    {

        $header = $request->session()->get('header');

        // Extract the customer name from the header
        $customerName = $header['Customer'] ?? null;

        // Retrieve item names associated with the same customer name
        $data = MasterDataRog::where('customer_name', $customerName)->pluck('item_name');

        // $data = MasterDataRog::pluck('item_name');
        $details = $request->session()->get('details');

        // $request->session()->forget('detail');
        // dd($detail);


        return view('qaqc.reports.createdetail', compact('data', 'details'));
    }


    // dd($rowData);

    public function createDefect(Request $request)
    {
        $categories = DefectCategory::get();
        $defect = $request->session()->get('defects');
        $report = $request->session()->get('header');
        $reportId = $report->id;
        $details = Detail::where('Report_Id', $reportId)->with('defects', 'defects.category')->get();
        if (!Session::has('active_tab')) {
            if ($details->isNotEmpty()) {
                Session::put('active_tab', $details->first()->id);
            }
        }

        return view('qaqc.reports.createdefect', compact('categories', 'details'));
    }

    public function postDefect(Request $request)
    {
        $request->validate([
            "detail_id" => "required|int",
            "quantity_customer" => "nullable|int",
            "quantity_daijo" => 'nullable|int',
            "customer_defect_category" => 'nullable|int',
            "daijo_defect_category" => 'nullable|int',
            "remark" => "required|string",
            "other_remark" => 'nullable|string',
        ]);

        // Common data for both customer and daijo defects
        $commonData = [
            'detail_id' => $request->detail_id,
            'remarks' => $request->remark,
        ];

        // Create customer defect if checkbox is checked
        if ($request->has('check_customer') && $request->has('check_daijo')) {
            Defect::create(array_merge($commonData, [
                'category_id' => $request->daijo_defect_category,
                'is_daijo' => true,
                'quantity' => $request->quantity_daijo,
            ]));

            Defect::create(array_merge($commonData, [
                'category_id' => $request->customer_defect_category,
                'is_daijo' => false,
                'quantity' => $request->quantity_customer,
            ]));
        } else if ($request->has('check_customer')) {
            Defect::create(array_merge($commonData, [
                'category_id' => $request->customer_defect_category,
                'is_daijo' => false,
                'quantity' => $request->quantity_customer,
            ]));
        } else if ($request->has('check_daijo')) {
            Defect::create(array_merge($commonData, [
                'category_id' => $request->daijo_defect_category,
                'is_daijo' => true,
                'quantity' => $request->quantity_daijo,
            ]));
        }

        return redirect()->route('qaqc.report.createdefect')->with(['success' => 'Defect added successfully!']);
    }

    public function deleteDefect($id)
    {
        Defect::find($id)->delete();
        return redirect()->route('qaqc.report.createdefect')->with(['success' => 'Defect deleted successfully!']);
    }

    public function updateActiveTab(Request $request)
    {
        $detailId = $request->input('detailId');
        Session::put('active_tab', $detailId);
        return response()->json(['message' => 'Active tab updated successfully']);
    }

    public function redirectToIndex()
    {
        session()->forget('header');
        session()->forget('details');
        session()->forget('active_tab');
        return redirect()->route('qaqc.report.index')->with(['success' => 'Report succesfully added!']);
    }

}
