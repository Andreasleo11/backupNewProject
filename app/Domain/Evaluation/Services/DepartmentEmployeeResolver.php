<?php

namespace App\Domain\Evaluation\Services;

use App\Domain\Evaluation\Repositories\EvaluationDataRepositoryContract;
use App\Enums\DepartmentCode;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Collection;

class DepartmentEmployeeResolver
{
    public function __construct(
        public EvaluationDataRepositoryContract $repository,
    ) {}

    /**
     * Resolve regular (non-Yayasan, non-Magang) employees for the given user.
     */
    public function resolveForUser(User $user): Collection
    {
        // Super Admins / HRD / GM see all regular employees
        if ($user->can('evaluation.view-any')) {
            return $this->repository->getAllNonYayasan();
        }

        if ($this->isSpecialAccessId($user)) {
            return $this->repository->getByDepartmentCodes([
                DepartmentCode::QC->value,
                DepartmentCode::QA->value,
            ]);
        }

        if ($user->department && $user->can('evaluation.view-department')) {
            $code = DepartmentCode::fromDepartmentName($user->department->name);

            if (! $code) {
                throw new \DomainException("Unknown department: {$user->department->name}");
            }

            $codes = $this->getDepartmentCodes($code, $user);

            return $this->repository->getByDepartmentCodes($codes);
        }

        throw new \Illuminate\Auth\Access\AuthorizationException('Only Department Heads can access this');
    }

    /**
     * Resolve Yayasan employees visible to the given user.
     */
    public function resolveYayasanForUser(User $user): Collection
    {
        if ($user->can('evaluation.view-any')) {
            return $this->repository->getAllYayasanEmployees();
        }

        if ($user->department && in_array($user->department->name, ['QC', 'QA'], true)) {
            return $this->repository->getYayasanEmployees($this->getQcQaCodes($user));
        }

        if ($user->department && $user->can('evaluation.view-department')) {
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
        if ($user->can('evaluation.view-any')) {
            return $this->repository->getAllMagangEmployees();
        }

        if ($user->department && in_array($user->department->name, ['QC', 'QA'], true)) {
            return $this->repository->getMagangEmployees($this->getQcQaCodes($user));
        }

        if ($user->department && $user->can('evaluation.view-department')) {
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
        $combined = config('evaluation.combined_dept_heads', []);

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
        $combined = config('evaluation.combined_dept_heads', []);

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
        return in_array($user->id, config('evaluation.special_access_ids', []), true);
    }
}

