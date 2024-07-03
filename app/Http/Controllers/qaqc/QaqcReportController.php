<?php

namespace App\Http\Controllers\qaqc;

use App\DataTables\VqcReportsDataTable;
use App\Exports\ReportsExport;
use App\Exports\FormAdjustExport;
use App\Exports\MonthlyReportsExport;
use App\Http\Controllers\Controller;
use App\Mail\QaqcMail;
use App\Models\Defect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\File;
use App\Models\Report;
use App\Models\Detail;
use App\Models\DefectCategory;
use App\Models\HeaderFormAdjust;
use App\Models\MasterDataPartNumberPrice;
use App\Models\MasterDataRogCustomerName;
use App\Models\MasterDataRogPartName;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

use function PHPUnit\Framework\isEmpty;

class QaqcReportController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, VqcReportsDataTable $dataTable)
    {
        $this->resetEditSessions();

        $status = $request->status;

        if($status != null){
            if ($status === 'approved') {
                // Logic when the status is approved
                $reports = Report::approved()->orderBy('updated_at', 'desc')->paginate(9);
            } elseif ($status === 'rejected') {
                // Logic when the status is rejected
                $reports = Report::rejected()->orderBy('updated_at', 'desc')->paginate(9);
            } elseif ($status === 'waitingSignature') {
                // Logic when the status is waitingSignature
                $reports = Report::waitingSignature()->orderBy('updated_at', 'desc')->paginate(9);
            } elseif ($status === 'waitingApproval') {
                // Logic when the status is waitingApproval
                $reports = Report::waitingApproval()->orderBy('updated_at', 'desc')->paginate(9);
            } else {
                $reports = [];
            }
        } else {
            $reports = Report::orderBy('updated_at', 'desc')->paginate(9);
        }
        // return view('qaqc.reports.index', compact('reports', 'status'));

        return $dataTable->with('status', $status)->render('qaqc.reports.index', compact('reports', 'status'));
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
        $files = File::where('doc_id', $report->doc_num)->get();
        $adjustForm = HeaderFormAdjust::where('report_id', $report->id)->first();
        return view('qaqc.reports.detail', compact('report','user','autographNames', 'files', 'adjustForm'));
    }

    public function edit(Request $request, $id)
    {
        $header = $request->session()->get('header_edit');
        if($header == null){
            $request->session()->put('header_edit', Report::find($id));
            $header = $request->session()->get('header_edit');
        }

        return view('qaqc.reports.edit', compact('header', 'id'));
    }

    public function updateHeader(Request $request, $id){
        $validatedData = $request->validate([
            'rec_date' => 'date',
            'verify_date' => 'date',
            'customer' => 'string',
            'invoice_no' => 'string',
            'num_of_parts' => 'integer',
            'created_by' => 'string',
        ]);

        $validatedData['autograph_1'] = strtoupper(auth()->user()->name) . '.png';
        $validatedData['autograph_user_1'] = auth()->user()->name;
        $validatedData['autograph_3'] = null;
        $validatedData['autograph_user_3'] = null;
        $validatedData['is_approve'] = 2;

        $report = $request->session()->get('header_edit');

        $report->fill($validatedData);

        $request->session()->put('header_edit', $report);

        return redirect()->route('qaqc.report.editDetail', $id);
    }

    public function editDetail(Request $request, $id){
        // Retrieve the existing report from the session
        $report = $request->session()->get('header_edit');

        if($report){
            $report->update();
        }

        $details_data = Detail::where('report_id', $id)->get();
        $request->session()->put('details_edit', $details_data);
        $details = $request->session()->get('details_edit');

        return view('qaqc.reports.edit-detail', compact('details', 'id'));

    }

    public function destroyDetail($id){
        Detail::where('id', $id)->delete();
        return response()->json(['message'=>'Detail has been deleted.']);
    }

    public function updateDetail(Request $request, $id){
        $details = [];

        for($i = 1; $i <= $request->input('rowCount'); $i++){

            $request->validate([
                'itemName' . $i => 'required',
                'rec_quantity' . $i => 'required',
                'verify_quantity' . $i => 'required',
                'can_use' . $i => 'required',
                'cant_use' . $i => 'required',
                'price' . $i => 'required',
            ]);


            $rowData = [
                'report_id' => $id,
                'part_name' => $request->input("itemName$i"),
                'rec_quantity' => $request->input("rec_quantity$i"),
                'verify_quantity' => $request->input("verify_quantity$i"),
                'can_use' => $request->input("can_use$i"),
                'cant_use' => $request->input("cant_use$i"),
                'price' => (int) str_replace(['Rp. ', '.'], '', $request->input("price$i")),
                'do_num' => $request->input("do_num$i")
            ];
                $detail = Detail::where('report_id', $id)
                ->where('part_name', $rowData['part_name'])
                ->first();

                if (!$detail) {
                // If the detail doesn't exist, create a new one
                    MasterDataPartNumberPrice::create([
                        'name' => $request->input("itemName$i"),
                        'price' => (int) str_replace(['Rp. ', '.'], '', $request->input("price$i"))
                    ]);

                    $itemPrice = MasterDataPartNumberPrice::where('name', $request->input("itemName$i"))->latest()->first()->price;
                    $rowData['price'] = $itemPrice;
                    $detail = new Detail();
                    $detail->fill($rowData);
                    $detail->save();
                } else {
                // If the detail exists, update its attributes
                    MasterDataPartNumberPrice::create([
                        'name' => $request->input("itemName$i"),
                        'price' => (int) str_replace(['Rp. ', '.'], '', $request->input("price$i"))
                    ]);
                    $detail->update($rowData);
                }

                $details[] = $detail;

        }

        $request->session()->put('details_edit', $details);
        return redirect()->route('qaqc.report.editDefect', $id);
    }

    public function editDefect(Request $request, $id){
        $categories = DefectCategory::get();
        $defect = $request->session()->get('defects_edit');
        $details = Detail::where('report_id', $id)->with('defects', 'defects.category')->get();
        if (!Session::has('active_tab')) {
            if ($details->isNotEmpty()) {
                Session::put('active_tab', $details->first()->id);
            }
        }

        return view('qaqc.reports.edit-defect', compact('categories', 'details', 'id'));
    }

    public function create(Request $request)
    {
        $header = $request->session()->get('header');
        return view('qaqc.reports.create', compact('header'));
    }

    public function getCustomers(Request $request)
    {
        $Customername = $request->input('customer_name');
        $cust = MasterDataRogCustomerName::where('name', 'like', "%$Customername%")->distinct()->pluck('name')->toArray();

        return response()->json($cust);
    }

    public function getItems(Request $request)
    {
        $itemName = $request->input('item_name');
        $items = MasterDataRogPartName::where('name', 'like', "%$itemName%")->pluck('name')->toArray();

        return response()->json($items);
    }

    public function getItemPrice(Request $request)
    {
        $encodedItemName = $request->input('name');
        $itemName = urldecode($encodedItemName);

        // Log::info('decoded item name: ', $itemName);

        // Query the database to get the latest price for the given item name
        $latestPrice = MasterDataPartNumberPrice::where('name', $itemName)
            ->latest('created_at')
            ->value('price');

        return response()->json(['latest_price' => $latestPrice]);
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
                'can_use' . $i => 'required',
                'cant_use' . $i => 'required',
                'price' . $i => 'required',
            ]);

            $rowData = [
                'report_id' => $reportId,
                'part_name' => $request->input("itemName$i"),
                'rec_quantity' => $request->input("rec_quantity$i"),
                'verify_quantity' => $request->input("verify_quantity$i"),
                'can_use' => $request->input("can_use$i"),
                'cant_use' => $request->input("cant_use$i"),
            ];

            $detail = Detail::where('report_id', $reportId)
            ->where('part_name', $rowData['part_name'])
            ->first();

            if (!$detail) {
            // If the detail doesn't exist, create a new one
                MasterDataPartNumberPrice::create([
                    'name' => $request->input("itemName$i"),
                    'price' => (int) str_replace(['Rp. ', '.'], '', $request->input("price$i"))
                ]);

                $itemPrice = MasterDataPartNumberPrice::where('name', $request->input("itemName$i"))->latest()->first()->price;
                // dd($itemPrice);
                $rowData['price'] = $itemPrice;
                $detail = new Detail();
                $detail->fill($rowData);
                $detail->save();
            } else {
            // If the detail exists, update its attributes
                $itemPrice = MasterDataPartNumberPrice::where('name', $request->input("itemName$i"))->latest()->first()->price;
                $rowData['price'] = $itemPrice;
                $detail->update($rowData);
            }

            $details[] = $detail;

        }

        $request->session()->put('details', $details);

        return redirect()->route('qaqc.report.createdefect');
    }

    public function destroy($id){
        $report = Report::findOrFail($id);

        $report->details()->delete();
        $report->delete();


        return redirect()->route('qaqc.report.index')->with('success', 'Report has been deleted successfully!');
    }

    public function uploadAttachment(Request $request, $id)
    {
        $request->validate([
            'attachment' => 'required|mimes:pdf,doc,docx,xlsx,xls,png,jpg,jpeg', // Adjust allowed file types and size
        ]);

        Report::where('id', $id)->update([
            'is_approve' => 2,
            'description' => null,
        ]);

        $file = $request->file('attachment');

        // Generate a unique filename
        $filename = time() . '_' . $file->getClientOriginalName();

        // Move the uploaded file to a storage location (you can customize the storage path)
        $file->storeAs('public/attachments', $filename);

        // Update the reports table with the attachment filename
        Report::where('id', $id)->update(['attachment' => $filename]);

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
            'rec_date' => 'date',
            'verify_date' => 'date',
            'customer' => 'string',
            'invoice_no' => 'string',
            'created_by' => 'string',
        ]);

        $validatedData['autograph_1'] = strtoupper(auth()->user()->name) . '.png';
        $validatedData['autograph_user_1'] = auth()->user()->name;
        $validatedData['is_approve'] = 2;

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
        // dd(MasterDataPartNumberPrice::where('name', '(RM)-733-CVRDRLL-BL/COVER DRL LH')
        // ->latest('created_at')
        // ->value('updated_at'));
        $header = $request->session()->get('header');

        // Extract the customer name from the header
        $customerName = $header['customer'] ?? null;

        // Retrieve item names associated with the same customer name
        $data = MasterDataRogCustomerName::get()->pluck('item_name');

        // $data = MasterDataRog::pluck('item_name');
        $details = $request->session()->get('details');

        // $request->session()->forget('detail');
        // dd($details);


        return view('qaqc.reports.createdetail', compact('data', 'details'));
    }


    // dd($rowData);

    public function createDefect(Request $request)
    {
        $categories = DefectCategory::get();
        $defect = $request->session()->get('defects');
        $report = $request->session()->get('header') ?? $request->session()->get('header_edit');
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
            "remark" => "nullable|string",
            "other_remark" => 'nullable|string',
        ]);

        if($request->remark === "other")
        {
            // Common data for both customer and daijo defects
            $commonData = [
                'detail_id' => $request->detail_id,
                'remarks' => $request->other_remark,
            ];
        } else {
            $commonData = [
                'detail_id' => $request->detail_id,
                'remarks' => $request->remark,
            ];

        }

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

        return redirect()->back()->with(['success' => 'Defect added successfully!']);
    }

    public function deleteDefect($id)
    {
        Defect::find($id)->delete();
        return redirect()->back()->with(['success' => 'Defect deleted successfully!']);
    }

    public function updateActiveTab(Request $request)
    {
        $detailId = $request->input('detailId');
        Session::put('active_tab', $detailId);
        return response()->json(['message' => 'Active tab updated successfully']);
    }

    public function redirectToIndex()
    {
        $this->updateUpdatedAt();
        $id = session()->get('header')->id ?? session()->get('header_edit')->id;
        $cust = session()->get('header')->customer ?? session()->get('header_edit')->customer;
        $pdfName = 'pdfs/verification-report-' . $id . '.pdf';
        $pdfPath[] = Storage::url($pdfName);
        if($id != null)
        {

            $this->savePdf($id);

             // Get 'to' and 'cc' email addresses from the configuration file
            $to = Config::get('email.feature_qc.to');
            $cc = Config::get('email.feature_qc.cc');

            $mailData = [
                'to' => $to,
                'cc' =>   $cc,
                'subject' => 'QAQC Verification Report Mail ' . $cust,
                'body' => 'Mail from ' . env('APP_NAME'),
                'file_paths' => $pdfPath
            ];
            // dd($mailData);

            Mail::send(new QaqcMail($mailData));
        }

        session()->forget('header');
        session()->forget('details');
        $this->resetEditSessions();

        return redirect()->route('qaqc.report.index')->with(['success' => 'Report succesfully stored/updated!']);
    }

    private function updateUpdatedAt(){
        $id = session()->get('header')->id ?? session()->get('header_edit')->id;
        Report::find($id)->update(['updated_at' => now()]);
    }

    private function resetEditSessions(){
        session()->forget('header_edit');
        session()->forget('details_edit');
        session()->forget('active_tab');
    }

    public function savePdf($id)
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

        // Define the file path and name
        $fileName = 'verification-report-' . $report->id . '.pdf';
        $filePath = 'pdfs/' . $fileName; // Adjust the directory structure as needed

        // Save the PDF file to the public storage
        Storage::disk('public')->put($filePath, $pdf->output());

        // Optionally, you can return a response indicating that the PDF has been saved
        return redirect()->back()->with(['message' => 'PDF saved successfully', 'file_path' => $filePath]);
    }

    public function sendEmail($id, Request $request)
    {
        $this->savePdf($id);

        $report = Report::with('details')->find($id);
        $pdfName = 'pdfs/verification-report-' . $report->id . '.pdf';
        $pdfPath = Storage::url($pdfName);

        $files = File::where('doc_id', $report->doc_num)->get();
        $filePaths = collect($files)->map(function ($file) {
            return Storage::url('files/' . $file->name);
        })->toArray();

        $filePaths[] = $pdfPath;

        $mailData = [
            'to' =>  array_filter(array_map('trim', explode(';', $request->to))) ?? 'raymondlay023@gmail.com',
            'cc' =>  array_filter(array_map('trim', explode(';', $request->cc))) ?? ['andreasleonardo.al@gmail.com', 'raymondlay034@gmail.com'],
            'subject' => $request->subject ?? 'QAQC Verification Report Mail',
            'body' => $request->body ?? 'Mail from ' . env('APP_NAME') ,
            'file_paths' => $filePaths
        ];

        Mail::send(new QaqcMail($mailData));

        $report->update(['has_been_emailed' => true]);

        return redirect()->back()->with(['success' => 'Email sent successfully!']);
    }

    public function rejectAuto(Request $request, $id)
    {
        Report::find($id)->update([
            'is_approve' => false,
            'description' => $request->description,
        ]);

        return redirect()->back()->with(['success' => 'Report rejected automatically!']);
    }

    public function lock($id)
    {
        Report::find($id)->update(['is_locked' => true]);
        return redirect()->back()->with(['success' => 'Report locked successfully!']);
    }

    public function exportToExcel()
    {
        return Excel::download(new ReportsExport(), 'reports-all-data.xlsx');
    }


    public function exportFormAdjustToExcel()
    {
        return Excel::download(new FormAdjustExport(), 'formadjust-all-data.xlsx');
    }

    public function updateDoNumber(Request $request, $id)
    {
        Detail::find($id)->update([
            'do_num' => $request->do_num
        ]);

        return redirect()->back()->with('success', 'Update do number berhasil!');
    }




    public function monthlyreport()
    {
        $datas = Report::with('details','details.defects')->get();
        // dd($datas);
         // Group by month
    $groupedByMonth = $datas->groupBy(function($item) {
        return Carbon::parse($item->verify_date)->format('Y-m'); // Assuming 'created_at' as the date field
    });

    $result = [];

    foreach ($groupedByMonth as $month => $reports) {
        $result[$month] = [];

        // Group by customer within each month
        foreach ($reports as $report) {
            foreach ($report->details as $detail) {
                $customerId = $report->customer; // Assuming 'customer_id' is the customer identifier

                if (!isset($result[$month][$customerId])) {
                    $result[$month][$customerId] = [
                        'total_rec_quantity' => 0,
                        'total_price' => 0,
                        'daijo_defect' => 0,
                        'customer_defect' => 0,
                        'cant_use' => 0,
                        'details' => []
                    ];
                }

                $result[$month][$customerId]['details'][] = [
                    'detail_id' => $detail->id,
                    'rec_quantity' => $detail->rec_quantity,
                    'defects' => $detail->defects // Include defects if necessary
                ];

                foreach ($detail->defects as $defect) {
                    if ($defect->is_daijo) {
                        $result[$month][$customerId]['daijo_defect'] += $defect->quantity;
                    } else {
                        $result[$month][$customerId]['customer_defect'] += $defect->quantity;
                    }
                }

                $result[$month][$customerId]['total_rec_quantity'] += $detail->rec_quantity;

                $result[$month][$customerId]['cant_use'] += $detail->cant_use;

                $result[$month][$customerId]['total_price'] += $detail->verify_quantity * $detail->price;

            }
        }
    }

    // dd($result['2024-03']['YANFENG AUTOMOTIVE INTERIOR SYSTEMS INDONESIA PT.']);
    // dd($result);

        return view('qaqc.monthlyreport', compact('result'));
    }

    public function showDetails(Request $request)
    {

        $data = $request->monthData;
        $month = Carbon::parse($data)->month;
        $year = Carbon::parse($data)->year;

        // Query reports for the specified month and year
        $reports = Report::with('details', 'details.defects', 'details.defects.category')
                         ->whereMonth('verify_date', $month)
                         ->whereYear('verify_date', $year)
                         ->get();

        $reports = Report::with(['details', 'details.defects', 'details.defects.category'])
                            ->whereMonth('verify_date', $month)
                            ->whereYear('verify_date', $year)
                            ->get();

                            $summary = [];

                            foreach ($reports as $report) {
                                foreach ($report->details as $detail) {
                                    $partName = $detail->part_name;

                                    if (!isset($summary[$partName])) {
                                        // Initialize the summary for this part_name
                                        $summary[$partName] = [
                                            'part_name' => $partName,
                                            'rec_quantity' => 0,
                                            'defects' => []
                                        ];
                                    }

                                    // Sum the rec_quantity
                                    $summary[$partName]['rec_quantity'] += $detail->rec_quantity;

                                    foreach ($detail->defects as $defect) {
                                        $categoryName = $defect->category->name;
                                        $quantity = $defect->quantity; // Assuming 'quantity' is a field in the defect model

                                        if (!isset($summary[$partName]['defects'][$categoryName])) {
                                            // Initialize the defects summary for this part_name
                                            $summary[$partName]['defects'][$categoryName] = [
                                                'category_name' => $categoryName,
                                                'quantity' => 0
                                            ];
                                        }

                                        // Sum the quantity for each defect category
                                        $summary[$partName]['defects'][$categoryName]['quantity'] += $quantity;
                                    }
                                }
                            }

                            // Convert the summary array to a collection for easier manipulation if needed
                            // return collect($summary)->values();
                            // dd($summary);



        return view('qaqc.monthlyreportdetail',compact('reports'));
    }

    public function export(Request $request)
    {
        $data = $request->monthData;
        $month = Carbon::parse($data)->month;
        $year = Carbon::parse($data)->year;
        $monthName = Carbon::parse($data)->format('F');

        $filename = "VQC MonthlyReport {$monthName}-{$year}.xlsx";

        return Excel::download(new MonthlyReportsExport($month, $year), $filename);
    }
}
