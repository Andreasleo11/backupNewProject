<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\DisciplineAccessPolicy;
use Tests\TestCase;

class DisciplineAccessPolicyTest extends TestCase
{
    private DisciplineAccessPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new DisciplineAccessPolicy;
    }

    /** @test */
    public function department_head_can_view_any_discipline()
    {
        $user = new User;
        $user->is_head = 1;

        $this->assertTrue($this->policy->viewAnyDiscipline($user));
    }

    /** @test */
    public function non_department_head_cannot_view_any_discipline()
    {
        $user = new User(['is_head' => 0]);

        $this->assertFalse($this->policy->viewAnyDiscipline($user));
    }

    /** @test */
    public function special_user_id_120_can_view_any_discipline()
    {
        $user = new User;
        $user->id = 120;
        $user->is_head = 0;

        $this->assertTrue($this->policy->viewAnyDiscipline($user));
    }

    /** @test */
    public function special_email_users_can_view_all_discipline()
    {
        $user1 = new User([
            'email' => 'ani_apriani@daijo.co.id',
            'is_head' => 0,
        ]);

        $user2 = new User([
            'email' => 'bernadett@daijo.co.id',
            'is_head' => 0,
        ]);

        $this->assertTrue($this->policy->viewAllDiscipline($user1));
        $this->assertTrue($this->policy->viewAllDiscipline($user2));
    }

    /** @test */
    public function regular_users_cannot_view_all_discipline()
    {
        $user = new User([
            'email' => 'regular@daijo.co.id',
            'is_head' => 0,
        ]);

        $this->assertFalse($this->policy->viewAllDiscipline($user));
    }

    /** @test */
    public function special_email_users_can_view_any_discipline()
    {
        $user = new User([
            'email' => 'ani_apriani@daijo.co.id',
            'is_head' => 0,
        ]);

        // Special email users should also pass viewAnyDiscipline check
        $this->assertTrue($this->policy->viewAnyDiscipline($user));
    }

    /** @test */
    public function department_head_can_view_yayasan_discipline()
    {
        $user = new User;
        $user->is_head = 1;

        $this->assertTrue($this->policy->viewYayasanDiscipline($user));
    }

    /** @test */
    public function special_users_can_view_yayasan_discipline()
    {
        $user = new User([
            'email' => 'bernadett@daijo.co.id',
            'is_head' => 0,
        ]);

        $this->assertTrue($this->policy->viewYayasanDiscipline($user));
    }

    /** @test */
    public function regular_users_cannot_view_yayasan_discipline()
    {
        $user = new User([
            'email' => 'regular@daijo.co.id',
            'is_head' => 0,
        ]);

        $this->assertFalse($this->policy->viewYayasanDiscipline($user));
    }
}
