<?php

declare(strict_types=1);

namespace App\Domain\PurchaseRequest\Services;

use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;

/**
 * Resolves the type of Purchase Request (office or factory)
 * based on the requesting department.
 */
class PurchaseRequestTypeResolver
{
    public function __construct(
        private PurchaseRequestRepository $repository
    ) {}

    /**
     * Resolve the PR type based on requesting department.
     *
     * @param string $fromDepartment The requesting department name
     * @return string 'office' or 'factory'
     */
    public function resolve(string $fromDepartment): string
    {
        $from = strtoupper($fromDepartment);

        // PE (Product Engineering) is always factory
        if ($from === 'PE') {
            return 'factory';
        }

        // Check if department is marked as office
        $officeDepartments = $this->repository->getOfficeDepartmentNames();

        if (in_array($from, $officeDepartments, true)) {
            return 'office';
        }

        // Default to factory
        return 'factory';
    }

    /**
     * Check if a department is an office department.
     *
     * @param string $departmentName The department name
     * @return bool True if office, false if factory
     */
    public function isOfficeDepartment(string $departmentName): bool
    {
        return $this->resolve($departmentName) === 'office';
    }

    /**
     * Check if a department is a factory department.
     *
     * @param string $departmentName The department name
     * @return bool True if factory, false if office
     */
    public function isFactoryDepartment(string $departmentName): bool
    {
        return $this->resolve($departmentName) === 'factory';
    }
}
