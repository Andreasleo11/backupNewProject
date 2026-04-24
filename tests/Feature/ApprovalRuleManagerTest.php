<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalRuleManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_rule_manager()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/admin/approval-rules');
        $response->assertStatus(200);
    }

    public function test_user_can_create_rule()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/admin/approval-rules', [
            'rule_model_type' => 'App\\Models\\PurchaseRequest',
            'rule_code' => 'TEST_RULE_001',
            'rule_name' => 'Test Rule',
            'rule_active' => true,
            'rule_priority' => 10,
            'rule_match_expr_raw' => '{"status": "PENDING"}',
        ]);

        $response->assertRedirect('/admin/approval-rules');
        $this->assertDatabaseHas('rule_templates', [
            'code' => 'TEST_RULE_001',
            'name' => 'Test Rule',
        ]);
    }
}
