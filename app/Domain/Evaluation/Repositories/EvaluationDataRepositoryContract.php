<?php

declare(strict_types=1);

namespace App\Domain\Evaluation\Repositories;

use Illuminate\Support\Collection;

interface EvaluationDataRepositoryContract
{
    /**
     * Get evaluation data by department and month.
     *
     * @param string $deptNo Department number
     * @param int $month Month to filter
     * @param int|null $year Year to filter (optional)
     * @param array $statuses Employee status filter (e.g., ['YAYASAN', 'KONTRAK'])
     */
    public function getByDepartmentAndMonth(
        string $deptNo,
        int $month,
        ?int $year = null,
        array $statuses = []
    ): Collection;

    /**
     * Get evaluation data by department codes.
     *
     * @param array $codes Array of department codes
     */
    public function getByDepartmentCodes(array $codes): Collection;

    /**
     * Get Yayasan employees by department codes.
     *
     * @param array $codes Array of department codes
     */
    public function getYayasanEmployees(array $codes): Collection;

    /**
     * Get Magang (intern) employees by department codes.
     *
     * @param array $codes Array of department codes
     */
    public function getMagangEmployees(array $codes): Collection;

    /**
     * Get all non-Yayasan employees.
     */
    public function getAllNonYayasan(): Collection;

    /**
     * Get all Yayasan employees.
     */
    public function getAllYayasanEmployees(): Collection;

    /**
     * Get all Magang employees.
     */
    public function getAllMagangEmployees(): Collection;

    /**
     * Find evaluation data with relations loaded.
     */
    public function findWithRelations(int $id): ?object;
}
