<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed approval system for Purchase Request tests
        if ($this->shouldSeedApprovalSystem()) {
            $this->seed(\Database\Seeders\PrRoleMappingSeeder::class);
            $this->seed(\Database\Seeders\PrApprovalRulesSeeder::class);
        }
    }

    /**
     * Determine if we should seed the approval system.
     * Only seed for PR-related tests to keep other tests fast.
     */
    protected function shouldSeedApprovalSystem(): bool
    {
        $testClass = static::class;

        return str_contains($testClass, 'PurchaseRequest')
            || str_contains($testClass, '\\\\PR\\\\')
            || str_contains($testClass, 'Approval');
    }
}
