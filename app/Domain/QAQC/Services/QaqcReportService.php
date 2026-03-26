<?php

declare(strict_types=1);

namespace App\Domain\QAQC\Services;

use App\Models\Detail;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class QaqcReportService
{
    /**
     * Get reports with optional status filtering.
     */
    public function getReports(?string $status = null): LengthAwarePaginator|Collection
    {
        if (! $status) {
            return Report::orderBy('updated_at', 'desc')->paginate(9);
        }

        $query = match ($status) {
            'approved' => Report::approved(),
            'rejected' => Report::rejected(),
            'waitingSignature' => Report::waitingSignature(),
            'waitingApproval' => Report::waitingApproval(),
            default => Report::query(),
        };

        return $query->orderBy('updated_at', 'desc')->paginate(9);
    }

    /**
     * Delete report and its details.
     */
    public function deleteReport(int $id): void
    {
        $report = Report::findOrFail($id);
        $report->details()->delete();
        $report->delete();
    }

    /**
     * Save autograph for report.
     */
    public function saveAutograph(int $reportId, int $section, string $username): void
    {
        $imagePath = $username . '.png';
        $report = Report::findOrFail($reportId);

        $report->update([
            "autograph_{$section}" => $imagePath,
            "autograph_user_{$section}" => $username,
        ]);
    }

    /**
     * Upload attachment for report.
     */
    public function uploadAttachment(int $reportId, object $file): string
    {
        Report::where('id', $reportId)->update([
            'is_approve' => 2,
            'description' => null,
        ]);

        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('public/attachments', $filename);

        Report::where('id', $reportId)->update(['attachment' => $filename]);

        return $filename;
    }

    /**
     * Reject report automatically.
     */
    public function rejectReport(int $id, string $description): void
    {
        Report::find($id)->update([
            'is_approve' => false,
            'description' => $description,
        ]);
    }

    /**
     * Lock report.
     */
    public function lockReport(int $id): void
    {
        Report::find($id)->update(['is_locked' => true]);
    }

    /**
     * Update DO number for detail.
     */
    public function updateDoNumber(int $detailId, string $doNum): void
    {
        Detail::find($detailId)->update(['do_num' => $doNum]);
    }

    /**
     * Get monthly report data grouped by month and customer.
     */
    public function getMonthlyReportData(): array
    {
        $datas = Report::with('details', 'details.defects')->get();

        $groupedByMonth = $datas->groupBy(fn ($item) => Carbon::parse($item->rec_date)->format('Y-m'));
        $result = [];

        foreach ($groupedByMonth as $month => $reports) {
            $result[$month] = [];

            foreach ($reports as $report) {
                foreach ($report->details as $detail) {
                    $customerId = $report->customer;

                    if (! isset($result[$month][$customerId])) {
                        $result[$month][$customerId] = [
                            'total_rec_quantity' => 0,
                            'total_price' => 0,
                            'daijo_defect' => 0,
                            'customer_defect' => 0,
                            'supplier_defect' => 0,
                            'cant_use' => 0,
                            'details' => [],
                        ];
                    }

                    $result[$month][$customerId]['details'][] = [
                        'detail_id' => $detail->id,
                        'rec_quantity' => $detail->rec_quantity,
                        'defects' => $detail->defects,
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

        return $result;
    }

    /**
     * Get monthly report details for a specific month and year.
     */
    public function getMonthlyReportDetails(string $monthData): Collection
    {
        $month = Carbon::parse($monthData)->month;
        $year = Carbon::parse($monthData)->year;

        return Report::with(['details', 'details.defects', 'details.defects.category'])
            ->whereMonth('rec_date', $month)
            ->whereYear('rec_date', $year)
            ->get();
    }

    /**
     * Mark report as emailed.
     */
    public function markAsEmailed(int $reportId): void
    {
        Report::find($reportId)->update(['has_been_emailed' => true]);
    }
}
