<?php

namespace App\Http\Controllers\qaqc;

use App\DataTables\VqcReportsDataTable;
use App\Exports\ReportsExport;
use App\Exports\FormAdjustExport;
use App\Exports\MonthlyReportsExport;
use App\Http\Controllers\Controller;
use App\Mail\QaqcMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\File;
use App\Models\Report;
use App\Models\Detail;
use App\Models\HeaderFormAdjust;
use App\Models\MasterDataPartNumberPrice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class QaqcReportController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, VqcReportsDataTable $dataTable)
    {
        $status = $request->status;

        if ($status != null) {
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
        $report = Report::with('details', 'details.defects', 'details.defects.category')->find($id);
        $user =  Auth::user();
        $autographNames = [
            'autograph_name_1' => $report->autograph_user_1 ?? null,
            'autograph_name_2' => $report->autograph_user_2 ?? null,
            'autograph_name_3' => $report->autograph_user_3 ?? null,
        ];
        $files = File::where('doc_id', $report->doc_num)->get();
        $adjustForm = HeaderFormAdjust::where('report_id', $report->id)->first();
        return view('qaqc.reports.detail', compact('report', 'user', 'autographNames', 'files', 'adjustForm'));
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

    public function destroy($id)
    {
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
        foreach ($report->details as $pd) {
            $data1 = json_decode($pd->daijo_defect_detail);
            $data2 = json_decode($pd->customer_defect_detail);
            $data3 = json_decode($pd->supplier_defect_detail);
            $data4 = json_decode($pd->remark);

            $pd->daijo_defect_detail = $data1;
            $pd->customer_defect_detail = $data2;
            $pd->supplier_defect_detail = $data3;
            $pd->remark = $data4;
        }

        $autographNames = [
            'autograph_name_1' => $report->autograph_user_1 ?? null,
            'autograph_name_2' => $report->autograph_user_2 ?? null,
            'autograph_name_3' => $report->autograph_user_3 ?? null,
        ];

        $pdf = Pdf::loadView('pdf/verification-report-pdf', compact('report', 'user', 'autographNames'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('verification-report-' . $report->id . '.pdf');
    }

    public function previewPdf($id)
    {
        $report = Report::with('details')->find($id);
        $user =  Auth::user();
        foreach ($report->details as $pd) {
            $data1 = json_decode($pd->daijo_defect_detail);
            $data2 = json_decode($pd->customer_defect_detail);
            $data3 = json_decode($pd->supplier_defect_detail);
            $data4 = json_decode($pd->remark);

            $pd->daijo_defect_detail = $data1;
            $pd->customer_defect_detail = $data2;
            $pd->supplier_defect_detail = $data3;
            $pd->remark = $data4;
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

    public function redirectToIndex()
    {
        $id = session()->get('header')->id ?? session()->get('header_edit')->id;
        
        if ($id != null) {
            $cust = session()->get('header')->customer ?? session()->get('header_edit')->customer;
            $pdfName = 'pdfs/verification-report-' . $id . '.pdf';
            $pdfPath[] = Storage::url($pdfName);

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
       
        return redirect()->route('qaqc.report.index')->with(['success' => 'Report succesfully stored/updated!']);
    }

    public function savePdf($id)
    {
        $report = Report::with('details')->find($id);
        $user =  Auth::user();
        foreach ($report->details as $pd) {
            $data1 = json_decode($pd->daijo_defect_detail);
            $data2 = json_decode($pd->customer_defect_detail);
            $data3 = json_decode($pd->supplier_defect_detail);
            $data4 = json_decode($pd->remark);

            $pd->daijo_defect_detail = $data1;
            $pd->customer_defect_detail = $data2;
            $pd->supplier_defect_detail = $data3;
            $pd->remark = $data4;
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
            'body' => $request->body ?? 'Mail from ' . env('APP_NAME'),
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
        $datas = Report::with('details', 'details.defects')->get();
        // dd($datas);
        // Group by month
        $groupedByMonth = $datas->groupBy(function ($item) {
            return Carbon::parse($item->rec_date)->format('Y-m'); // Assuming 'created_at' as the date field
        });

        $result = [];

        foreach ($groupedByMonth as $month => $reports) {
            // dd($groupedByMonth['2024-09']);
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
                            'supplier_defect' => 0,
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
                        } elseif ($defect->is_supplier) {
                            $result[$month][$customerId]['supplier_defect'] += $defect->quantity;
                        } elseif ($defect->is_customer) {
                            $result[$month][$customerId]['customer_defect'] += $defect->quantity;
                        }
                    }

                    $result[$month][$customerId]['total_rec_quantity'] += $detail->rec_quantity;

                    $result[$month][$customerId]['cant_use'] += $detail->cant_use;

                    $result[$month][$customerId]['total_price'] += $detail->verify_quantity * $detail->price;
                }
            }
        }

        // dd($result['2024-09']['YANFENG AUTOMOTIVE INTERIOR SYSTEMS INDONESIA PT.']);
        //   dd($result['2024-09']['INDONESIA THAI SUMMIT PLASTECH PT.']);
        // dd($result['2024-09']);

        return view('qaqc.monthlyreport', compact('result'));
    }

    public function showDetails(Request $request)
    {
        $data = $request->monthData;
        $month = Carbon::parse($data)->month;
        $year = Carbon::parse($data)->year;

        $reports = Report::with(['details', 'details.defects', 'details.defects.category'])
            ->whereMonth('rec_date', $month)
            ->whereYear('rec_date', $year)
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

        return view('qaqc.monthlyreportdetail', compact('reports'));
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
