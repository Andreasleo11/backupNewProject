<?php

namespace App\Livewire\DailyReports;

use App\Domain\DailyReport\Services\DailyReportUploadService;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadOverlay extends Component
{
    use WithFileUploads;

    public $isOpen = false;

    public $step = 'upload'; // upload, preview, results

    public $report_file;

    public $previewData = [];

    public $logs = [];

    protected $listeners = ['openUpload' => 'open'];

    public function open()
    {
        $this->reset(['report_file', 'previewData', 'logs', 'step']);
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function processUpload(DailyReportUploadService $uploadService)
    {
        $this->validate([
            'report_file' => 'required|file|mimes:xlsx,csv,txt|max:10240',
        ]);

        $result = $uploadService->processExcelUpload($this->report_file->getRealPath());

        if (! $result['success']) {
            $this->addError('report_file', $result['message']);

            return;
        }

        $this->previewData = $result['data'];
        $this->step = 'preview';
    }

    public function confirm(DailyReportUploadService $uploadService)
    {
        $this->logs = $uploadService->confirmUpload($this->previewData);
        $this->step = 'results';

        $this->dispatch('refreshIndex')->to(Index::class);
    }

    public function render()
    {
        return view('livewire.daily-reports.upload-overlay');
    }
}
