<?php

namespace App\Domain\Discipline\Services;

use App\Domain\Discipline\Repositories\EvaluationDataRepositoryContract;
use App\Enums\DepartmentCode;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Policies\DisciplineAccessPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class DepartmentEmployeeResolver
{
    public function __construct(
        public EvaluationDataRepositoryContract $repository,
        public DisciplineAccessPolicy           $policy
    ) {}

    /**
     * Resolve regular (non-Yayasan, non-Magang) employees for the given user.
     */
    public function resolveForUser(User $user): Collection
    {
        // Super Admins see all regular employees regardless of department
        if ($this->policy->viewAny($user)) {
            return $this->repository->getAllNonYayasan(); // Defaults to level 5
        }

        if ($this->isSpecialAccessId($user)) {
            return $this->repository->getByDepartmentCodes([
                DepartmentCode::QC->value,
                DepartmentCode::QA->value,
            ]);
        }

        if ($user->department && $this->policy->viewDepartment($user)) {
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
     * Resolve Yayasan employees visible to the given user.
     */
    public function resolveYayasanForUser(User $user): Collection
    {
        // GM, Super Admins, or HRD approvers see all Yayasan
        if ($this->policy->viewAny($user)) {
            return $this->repository->getAllYayasanEmployees();
        }

        if ($user->department && in_array($user->department->name, ['QC', 'QA'], true)) {
            return $this->repository->getYayasanEmployees($this->getQcQaCodes($user));
        }

        if ($user->department && $this->policy->viewDepartment($user)) {
            $code = DepartmentCode::fromDepartmentName($user->department->name);

            if (! $code) {
                throw new \DomainException("Unknown department: {$user->department->name}");
            }

            return $this->repository->getYayasanEmployees(
                $this->getDepartmentCodesForType($code, $user)
            );
        }

        return collect();
    }

    /**
     * Resolve Magang (internship) employees visible to the given user.
     */
    public function resolveMagangForUser(User $user): Collection
    {
        // GM or Super Admins see all Magang
        if ($this->policy->viewAny($user)) {
            return $this->repository->getAllMagangEmployees();
        }

        if ($user->department && in_array($user->department->name, ['QC', 'QA'], true)) {
            return $this->repository->getMagangEmployees($this->getQcQaCodes($user));
        }

        if ($user->department && $this->policy->viewDepartment($user)) {
            $code = DepartmentCode::fromDepartmentName($user->department->name);

            if (! $code) {
                throw new \DomainException("Unknown department: {$user->department->name}");
            }

            return $this->repository->getMagangEmployees(
                $this->getDepartmentCodesForType($code, $user)
            );
        }

        return collect();
    }

    /**
     * Get the department codes a user can manage for Yayasan or Magang.
     * Reads combined_dept_heads from config — no hardcoded usernames.
     */
    private function getDepartmentCodesForType(DepartmentCode $code, User $user): array
    {
        $combined = config('discipline.combined_dept_heads', []);

        // Check if this user has a custom multi-department mapping (values are dept codes)
        if (isset($combined[$user->name])) {
            return $combined[$user->name];
        }

        // Logistic heads also see Store by default
        if ($code === DepartmentCode::LOGISTIC) {
            return [DepartmentCode::LOGISTIC->value, DepartmentCode::STORE->value];
        }

        return [$code->value];
    }

    /**
     * Get department codes for regular employees.
     * Logistic heads see Logistic + Store.
     */
    private function getDepartmentCodes(DepartmentCode $code, User $user): array
    {
        if ($code === DepartmentCode::LOGISTIC) {
            return [DepartmentCode::LOGISTIC->value, DepartmentCode::STORE->value];
        }

        return [$code->value];
    }

    /**
     * Get QC/QA codes for the user.
     * 'yuli' sees both QC and QA — read from config/discipline.combined_dept_heads.
     */
    private function getQcQaCodes(User $user): array
    {
        $combined = config('discipline.combined_dept_heads', []);

        if (isset($combined[$user->name])) {
            return $combined[$user->name];
        }

        return [DepartmentCode::QC->value];
    }

    /**
     * Check if user has a special hardcoded access ID (reads from config).
     */
    private function isSpecialAccessId(User $user): bool
    {
        return in_array($user->id, config('discipline.special_access_ids', []), true);
    }

    /**
     * Fetch filtered employees for department head based on department and month.
     */
    public function fetchForDepartmentHead(string $deptNo, int $month): Collection
    {
        return $this->repository->getByDepartmentAndMonth($deptNo, $month);
    }

    /**
     * Fetch filtered Yayasan employees for GM based on dept+month.
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

