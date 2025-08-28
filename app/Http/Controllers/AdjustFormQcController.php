<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Report;
use App\Models\MasterDataAdjust;
use App\Models\HeaderFormAdjust;
use App\Models\FormAdjustMaster;
use App\Models\Detail;

class AdjustFormQcController extends Controller
{
    public function index(Request $request)
    {
        $reports = $request->input("reports");

        $datas = Report::with("details")->find($reports);

        foreach ($datas->details as $detail) {
            $partName = $detail->part_name;
            // Now you can work with $partName, such as echoing it or storing it in an array
            // Split the string by space
            $parts = explode("/", $partName);

            // Extract the first part
            $firstPart = $parts[0];
            $firstParts[] = $firstPart;
        }
        $masterDataCollection = collect(); // Create an empty collection to store the results

        foreach ($firstParts as $part) {
            $masterData = MasterDataAdjust::where("fg_code", $part)->get();
            $masterDataCollection = $masterDataCollection->merge($masterData); // Merge the results into the collection
        }

        $found = HeaderFormAdjust::where("report_id", $reports)->first();

        if (!$found) {
            HeaderFormAdjust::create([
                "report_id" => $reports,
            ]);
        }

        return view("qaqc.reports.adjustindex", compact("datas", "masterDataCollection", "found"));
    }

    public function save(Request $request)
    {
        // dd($request->all());
        $detailid = $request->detail_id;
        $masterid = $request->MasterId;
        $headerid = $request->header_id;
        $reportid = $request->report_id;
        $rmwarehouse = $request->rm_warehouse;
        $fgwarehouse = $request->fg_warehouse;

        $masterData = MasterDataAdjust::find($masterid);
        // dd($masterData);

        FormAdjustMaster::create([
            "detail_id" => $detailid,
            "header_id" => $headerid,
            "rm_code" => $masterData->rm_code,
            "rm_description" => $masterData->rm_description,
            "rm_quantity" => $masterData->rm_quantity,
            "fg_measure" => $masterData->fg_measure,
            "rm_measure" => $masterData->rm_measure,
            "warehouse_name" => $rmwarehouse,
        ]);

        Detail::where("id", $detailid)->update([
            "fg_measure" => $masterData->fg_measure,
        ]);

        return redirect()->back();
    }

    public function savewarehouse(Request $request)
    {
        $detailid = $request->detail_id;
        $fgwarehouse = $request->fg_warehouse;

        Detail::where("id", $detailid)->update([
            "fg_warehouse_name" => $fgwarehouse,
        ]);
        return redirect()->back();
    }

    public function adjustformview(Request $request)
    {
        $reportid = $request->report_id;
        // dd($reportid);

        $datas = HeaderFormAdjust::with("report", "report.details", "report.details.adjustdetail")
            ->where("report_id", $reportid)
            ->first();

        // dd($datas);
        // dd(Auth::user());

        foreach ($datas->report->details as $detail) {
            // dd($detail->part_name);

            foreach ($detail->adjustdetail as $adjustDetail) {
                // Process the data inside adjust_detail relation
                $adjustDetailId = $adjustDetail->id;
                $rmCode = $adjustDetail->rm_code;
                $rmDescription = $adjustDetail->rm_description;
            }
        }

        // // Loop through each evaluation data
        // foreach ($datas->evaluationData as $index => $dataadjust) {
        //     // Concatenate the part_name from Detail with rm_code and rm_description from FormAdjustMaster
        //     $concatenatedString = $dataadjust->rm_code . ' - ' . $dataadjust->rm_description;

        //     // Check if the part_name already exists in the grouped data array
        //     if (isset($groupedData[$dataadjust->detail->part_name])) {
        //         // If it exists, append the concatenated string to the existing array
        //         $groupedData[$dataadjust->detail->part_name][] = $concatenatedString;
        //     } else {
        //         // If it doesn't exist, create a new array with the concatenated string
        //         $groupedData[$dataadjust->detail->part_name] = [$concatenatedString];
        //     }
        // }

        // // Output the grouped data with part_name concatenated using "/"
        // foreach ($groupedData as $partName => $concatenatedStrings) {
        //     // Remove the redundant part_name from the concatenated strings
        //     foreach ($concatenatedStrings as &$string) {
        //         $string = str_replace($partName . ' - ', '', $string);
        //     }
        //     // Output the part_name and concatenated strings
        //     echo $partName . ' => ' . implode(' - ', $concatenatedStrings) . '<br>';
        // }

        // dd($groupedData);
        return view("qaqc.reports.adjustformview", compact("datas"));
    }

    public function addremarkadjust(Request $request)
    {
        $detailid = $request->detail_id;
        $remark = $request->remark;

        Detail::where("id", $detailid)->update([
            "remark" => $remark,
        ]);
        return redirect()->back();
    }

    public function saveAutographPath(Request $request, $reportId, $section)
    {
        $username = Auth::user()->name;
        // Log::info('Username:', ['username' => $username]);
        $imagePath = $username . ".png";
        // Log::info('imagepath : ', $imagePath);

        // Save $imagePath to the database for the specified $reportId and $section
        $report = HeaderFormAdjust::find($reportId);
        $report->update([
            "autograph_{$section}" => $imagePath,
        ]);

        return response()->json(["success" => "Autograph saved successfully!"]);
    }

    public function listformadjust()
    {
        $datas = HeaderFormAdjust::with(
            "report",
            "report.details",
            "report.details.adjustdetail",
        )->get();
        // dd($datas);

        return view("qaqc.formadjustlistall", compact("datas"));
    }
}
