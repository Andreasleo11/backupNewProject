<?php

declare(strict_types=1);

namespace App\Domain\PurchaseRequest\Services;

use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use Carbon\Carbon;

class PurchaseRequestNumberGenerator
{
    private const DEPT_CODES = [
        'COMPUTER' => 'CP',
        'PERSONALIA' => 'HRD',
        'MAINTENANCE' => 'MT',
        'PURCHASING' => 'PUR',
    ];

    private const BRANCH_CODES = [
        'JAKARTA' => 'JKT',
        'KARAWANG' => 'KRW',
    ];

    public function __construct(
        private PurchaseRequestRepository $repository
    ) {}

    public function generateDocNum(string $department, string $branch, ?Carbon $date = null): string
    {
        $date = $date ?? Carbon::now();
        $dateStr = $date->format('ymd');

        $deptCode = self::DEPT_CODES[strtoupper($department)] ?? 'UNK';
        $areaCode = self::BRANCH_CODES[strtoupper($branch)] ?? 'UNK';

        // Pattern for query: %/PR/{AreaCode}/{Date}/%
        // But the previous implementation queried: "%/PR/{$areaCode}/{$date}/%"
        // And the doc_num format is: "{$toDepartmentCode}/PR/{$areaCode}/{$date}/{$increment}"
        
        $prefixKey = "/PR/{$areaCode}/{$dateStr}/";
        
        $latest = $this->repository->getLatestByDocNumPrefix("%{$prefixKey}");

        $lastIncrement = 0;
        if ($latest) {
             // Extract 001 from CP/PR/JKT/240101/001
             $parts = explode('/', $latest->doc_num);
             $lastPart = end($parts);
             if (is_numeric($lastPart)) {
                 $lastIncrement = (int) $lastPart;
             }
        }

        $increment = str_pad((string)($lastIncrement + 1), 3, '0', STR_PAD_LEFT);

        return "{$deptCode}/PR/{$areaCode}/{$dateStr}/{$increment}";
    }

    public function generatePrNo(string $department, int|string $id): string
    {
        return substr(strtoupper($department), 0, 4) . '-' . $id;
    }
}
