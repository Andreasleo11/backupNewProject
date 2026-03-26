<?php

declare(strict_types=1);

namespace App\Domain\MonthlyBudget\Services;

use App\Exports\MonthlyBudgetReportTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class BudgetExportService
{
    /**
     * Download Excel template for budget report.
     */
    public function downloadTemplate(int $deptNo): BinaryFileResponse
    {
        return Excel::download(
            new MonthlyBudgetReportTemplateExport($deptNo),
            'monthly_budget_template.xlsx'
        );
    }
}
