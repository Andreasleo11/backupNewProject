<?php

namespace App\Domain\Discipline\Services;

use App\Domain\Discipline\Repositories\EvaluationDataRepositoryContract;
use App\Enums\DepartmentCode;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class DepartmentEmployeeResolver
{
    public function __construct(
        public EvaluationDataRepositoryContract $repository
    ) {}

    /**
     * Resolve employees for the given user based on their role and department.
     */
    public function resolveForUser(User $user): Collection
    {
        // Special case: User ID 120 sees QC + QA
        if ($user->id === 120) {
            return $this->repository->getByDepartmentCodes([
                DepartmentCode::QC->value,
                DepartmentCode::QA->value,
            ]);
        }

        // Special users (HR managers) see Personalia department
        if ($this->isSpecialAccessUser($user)) {
            return $this->repository->getByDepartmentCodes([
                DepartmentCode::PERSONALIA->value,
            ]);
        }

        // Department heads see their own department employees
        if ($user->is_head === 1) {
            $code = DepartmentCode::fromDepartmentName($user->department->name);

            if (! $code) {
                throw new \DomainException("Unknown department: {$user->department->name}");
            }

            $codes = $this->getDepartmentCodes($code, $user);

            return $this->repository->getByDepartmentCodes($codes);
        }

        throw new AuthorizationException('Only Department Heads can access this');
    }

    /**
     * Resolve Yayasan employees for the given user.
     */
    public function resolveYayasanForUser(User $user): Collection
    {
        // Special case: QC/QA department heads
        if (in_array($user->department->name, ['QC', 'QA'], true)) {
            $codes = $this->getQcQaCodes($user);

            return $this->repository->getYayasanEmployees($codes);
        }

        // GM or special users see all Yayasan
        if ($user->is_gm || $user->name === 'Bernadett') {
            return $this->repository->getYayasanEmployees(DepartmentCode::values());
        }

        // Regular department heads see their department's Yayasan
        $code = DepartmentCode::fromDepartmentName($user->department->name);

        if (! $code) {
            throw new \DomainException("Unknown department: {$user->department->name}");
        }

        $codes = $this->getDepartmentCodesForYayasan($code, $user);

        return $this->repository->getYayasanEmployees($codes);
    }

    /**
     * Resolve Magang (internship) employees for the given user.
     */
    public function resolveMagangForUser(User $user): Collection
    {
        // Similar logic to Yayasan but for MAGANG status
        if (in_array($user->department->name, ['QC', 'QA'], true)) {
            $codes = $this->getQcQaCodes($user);

            return $this->repository->getMagangEmployees($codes);
        }

        if ($user->is_gm) {
            return $this->repository->getMagangEmployees(DepartmentCode::values());
        }

        $code = DepartmentCode::fromDepartmentName($user->department->name);

        if (! $code) {
            throw new \DomainException("Unknown department: {$user->department->name}");
        }

        $codes = $this->getDepartmentCodesForMagang($code, $user);

        return $this->repository->getMagangEmployees($codes);
    }

    /**
     * Get department codes for the given department, handling special combinations.
     */
    private function getDepartmentCodes(DepartmentCode $code, User $user): array
    {
        return match ($code) {
            DepartmentCode::LOGISTIC => [
                DepartmentCode::LOGISTIC->value,
                DepartmentCode::STORE->value,
            ],
            default => [$code->value],
        };
    }

    /**
     * Get department codes for Yayasan employees.
     */
    private function getDepartmentCodesForYayasan(DepartmentCode $code, User $user): array
    {
        return match ($code) {
            DepartmentCode::LOGISTIC => [
                DepartmentCode::LOGISTIC->value,
                DepartmentCode::STORE->value,
            ],
            DepartmentCode::SECOND_PROCESS => $user->name === 'popon'
                ? [DepartmentCode::SECOND_PROCESS->value, DepartmentCode::ASSEMBLY->value]
                : [DepartmentCode::SECOND_PROCESS->value],
            DepartmentCode::STORE => $user->name === 'catur'
                ? [DepartmentCode::STORE->value, DepartmentCode::LOGISTIC->value]
                : [DepartmentCode::STORE->value],
            default => [$code->value],
        };
    }

    /**
     * Get department codes for Magang employees.
     */
    private function getDepartmentCodesForMagang(DepartmentCode $code, User $user): array
    {
        // Same logic as Yayasan for now
        return $this->getDepartmentCodesForYayasan($code, $user);
    }

    /**
     * Get QC/QA department codes based on user.
     */
    private function getQcQaCodes(User $user): array
    {
        if ($user->name === 'yuli') {
            return [DepartmentCode::QC->value, DepartmentCode::QA->value];
        }

        return [DepartmentCode::QC->value];
    }

    /**
     * Check if user has special access (HR managers).
     */
    private function isSpecialAccessUser(User $user): bool
    {
        return in_array($user->email, [
            'ani_apriani@daijo.co.id',
            'bernadett@daijo.co.id',
        ], true);
    }

    /**
     * Fetch filtered employees for department head based on department and month.
     *
     * @param string $deptNo Department number
     * @param int $month Month to filter
     */
    public function fetchForDepartmentHead(string $deptNo, int $month): Collection
    {
        return $this->repository->getByDepartmentAndMonth($deptNo, $month);
    }

    /**
     * Fetch filtered employees for general manager (Yayasan only).
     *
     * @param string $deptNo Department number
     * @param int $month Month to filter
     */
    public function fetchForGeneralManager(string $deptNo, int $month): Collection
    {
        return $this->repository->getByDepartmentAndMonth(
            $deptNo,
            $month,
            statuses: ['YAYASAN', 'YAYASAN KARAWANG']
        );
    }

    /**
     * Fetch Yayasan employees based on user's role and filters.
     *
     * @param int $month Month to filter
     * @param int $year Year to filter
     * @param bool $isGM Whether user is GM
     * @param string|null $deptNo Department number (null for GM)
     */
    public function fetchYayasanEmployees(
        int $month,
        int $year,
        bool $isGM,
        ?string $deptNo = null
    ): Collection {
        if ($isGM) {
            return $this->repository->getYayasanByMonthAndYear($month, $year);
        }

        if (! $deptNo) {
            throw new \InvalidArgumentException('Department number required for non-GM users');
        }

        return $this->repository->getByDepartmentAndMonth(
            $deptNo,
            $month,
            $year,
            ['YAYASAN', 'YAYASAN KARAWANG']
        );
    }
}
