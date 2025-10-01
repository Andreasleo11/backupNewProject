<?php

namespace App\Livewire;

use App\Mail\QaqcMail;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ReportWizardPreview extends Component
{
    public $report;

    public $details;

    public $reportId;

    public function mount($reportId)
    {
        $this->reportId = $reportId;

        $this->report = [
            'rec_date' => session('report.rec_date'),
            'verify_date' => session('report.verify_date'),
            'customer' => session('report.customer'),
            'invoice_no' => session('report.invoice_no'),
        ];

        $this->details = collect(Session::get('report.details', []))->map(function ($item, $i) {
            $item['id'] = $i;
            $item['defects'] = $item['defects'] ?? [];

            return (object) $item;
        });
    }

    public function submitAll()
    {
        $isUpdate = (bool) $this->reportId;

        $report = DB::transaction(function () use (&$isUpdate) {
            if ($isUpdate) {
                $report = Report::findOrFail($this->reportId);
                $report->update([
                    'rec_date' => $this->report['rec_date'],
                    'verify_date' => $this->report['verify_date'],
                    'customer' => $this->report['customer'],
                    'invoice_no' => $this->report['invoice_no'],
                ]);

                // Delete defects and details (cascade preferred, but doing manually)
                foreach ($report->details as $detail) {
                    $detail->defects()->delete(); // delete defects per detail
                }

                $report->details()->delete();
            } else {
                $report = Report::create([
                    'rec_date' => $this->report['rec_date'],
                    'verify_date' => $this->report['verify_date'],
                    'customer' => $this->report['customer'],
                    'invoice_no' => $this->report['invoice_no'],
                    'created_by' => Auth::user()->name,
                    'autograph_1' => Auth::user()->name.'.png',
                ]);
            }

            foreach ($this->details as $detailData) {
                $detail = $report->details()->create([
                    'part_name' => $detailData->part_name,
                    'rec_quantity' => $detailData->rec_quantity,
                    'verify_quantity' => $detailData->verify_quantity,
                    'can_use' => $detailData->can_use,
                    'cant_use' => $detailData->cant_use,
                    'price' => $detailData->price,
                    'currency' => $detailData->currency ?? 'IDR',
                ]);

                foreach ($detailData->defects ?? [] as $defect) {
                    $detail->defects()->create($defect);
                }
            }

            if ($report) {
                $customer = $report->customer;
                $pdfName = 'pdfs/verification-report-'.$report->id.'.pdf';
                $pdfPath[] = Storage::url($pdfName);

                $this->savePdf($report->id);

                // Get 'to' and 'cc' email addresses from the configuration file
                $to = Config::get('email.feature_qc.to');
                $cc = Config::get('email.feature_qc.cc');

                $mailData = [
                    'to' => $to,
                    'cc' => $cc,
                    'subject' => 'QAQC Verification Report Mail '.$customer,
                    'body' => 'Mail from '.env('APP_NAME'),
                    'file_paths' => $pdfPath,
                ];
                // dd($mailData);

                Mail::send(new QaqcMail($mailData));
            }

            return $report;
        });

        session()->flash(
            'success',
            $isUpdate ? 'Report successfully updated!' : 'Report successfully created!',
        );
        $this->dispatch('resetWizard')->to('report-wizard');

        return redirect()->route('qaqc.report.detail', $report->id);
    }

    private function savePdf($id)
    {
        $report = Report::with('details')->find($id);
        $user = Auth::user();
        foreach ($report->details as $detail) {
            $data1 = json_decode($detail->daijo_defect_detail);
            $data2 = json_decode($detail->customer_defect_detail);
            $data3 = json_decode($detail->supplier_defect_detail);
            $data4 = json_decode($detail->remark);

            $detail->daijo_defect_detail = $data1;
            $detail->customer_defect_detail = $data2;
            $detail->supplier_defect_detail = $data3;
            $detail->remark = $data4;
        }

        $autographNames = [
            'autograph_name_1' => $report->autograph_user_1 ?? null,
            'autograph_name_2' => $report->autograph_user_2 ?? null,
            'autograph_name_3' => $report->autograph_user_3 ?? null,
        ];

        $pdf = Pdf::loadView(
            'pdf/verification-report-pdf',
            compact('report', 'user', 'autographNames'),
        )->setPaper('a4', 'landscape');

        // Define the file path and name
        $fileName = 'verification-report-'.$report->id.'.pdf';
        $filePath = 'pdfs/'.$fileName; // Adjust the directory structure as needed

        // Save the PDF file to the public storage
        Storage::disk('public')->put($filePath, $pdf->output());

        // Optionally, you can return a response indicating that the PDF has been saved
        // return redirect()->back()->with(['message' => 'PDF saved successfully', 'file_path' => $filePath]);
    }

    public function goBack()
    {
        $this->dispatch('goBack')->to('report-wizard');
    }

    public function render()
    {
        return view('livewire.report-wizard-preview', [
            'report' => $this->report,
            'details' => $this->details,
            'categories' => \App\Models\DefectCategory::pluck('name', 'id')->toArray(),
        ]);
    }
}
