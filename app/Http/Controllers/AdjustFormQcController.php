<?php

namespace App\Http\Controllers;

use App\Domain\QAQC\Services\AdjustFormService;
use App\Models\HeaderFormAdjust;
use App\Models\Report;
use Illuminate\Http\Request;

class AdjustFormQcController extends Controller
{
    public function __construct(
        private readonly AdjustFormService $adjustFormService
    ) {}

    public function index(Request $request)
    {
        $reportId = $request->input('reports');
        $datas = Report::with('details')->findOrFail($reportId);
        $masterDataCollection = $this->adjustFormService->getMasterDataForReport($reportId);
        $found = $this->adjustFormService->getOrCreateHeader($reportId);

        return view('qaqc.reports.adjustindex', compact('datas', 'masterDataCollection', 'found'));
    }

    public function save(Request $request)
    {
        $this->adjustFormService->saveAdjustment([
            'detail_id' => $request->detail_id,
            'master_id' => $request->MasterId,
            'header_id' => $request->header_id,
            'rm_warehouse' => $request->rm_warehouse,
        ]);

        return redirect()->back();
    }

    public function savewarehouse(Request $request)
    {
        $this->adjustFormService->saveWarehouse($request->detail_id, $request->fg_warehouse);

        return redirect()->back();
    }

    public function adjustformview(Request $request)
    {
        $datas = HeaderFormAdjust::with('report', 'report.details', 'report.details.adjustdetail')
            ->where('report_id', $request->report_id)
            ->firstOrFail();

        return view('qaqc.reports.adjustformview', compact('datas'));
    }

    public function addremarkadjust(Request $request)
    {
        $this->adjustFormService->addRemark($request->detail_id, $request->remark);

        return redirect()->back();
    }

    public function saveAutographPath(Request $request, $reportId, $section)
    {
        $this->adjustFormService->saveAutograph($reportId, (int) $section);

        return response()->json(['success' => 'Autograph saved successfully!']);
    }

    public function listformadjust()
    {
        $datas = HeaderFormAdjust::with('report', 'report.details', 'report.details.adjustdetail')->get();

        return view('qaqc.formadjustlistall', compact('datas'));
    }
}
