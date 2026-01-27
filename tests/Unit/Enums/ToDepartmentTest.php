<?php

namespace Tests\Unit\Enums;

use App\Enums\ToDepartment;
use Tests\TestCase;

class ToDepartmentTest extends TestCase
{
    /** @test */
    public function it_has_correct_values_and_labels()
    {
        $this->assertEquals('Personnel', ToDepartment::PERSONALIA->value);
        $this->assertEquals('Personalia', ToDepartment::PERSONALIA->label());

        $this->assertEquals('Maintenance', ToDepartment::MAINTENANCE->value);
        $this->assertEquals('Maintenance', ToDepartment::MAINTENANCE->label());
    }

    /** @test */
    public function it_can_be_instantiated_from_valid_strings()
    {
        $this->assertEquals(ToDepartment::PERSONALIA, ToDepartment::from('Personnel'));
        $this->assertEquals(ToDepartment::MAINTENANCE, ToDepartment::from('Maintenance'));
    }

    /** @test */
    public function it_thows_error_for_invalid_casing()
    {
        $this->expectException(\ValueError::class);
        ToDepartment::from('personnel'); // PHP Enums are case-sensitive
    }
}
