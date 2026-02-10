<?php

namespace Tests\Unit\Infrastructure\Approval\Services;

use App\Infrastructure\Approval\Services\ApprovalEngine;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Infrastructure\Persistence\Eloquent\Models\UserSignature;
use App\Models\PurchaseRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApprovalEngineSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_approver_snapshot_leaves_name_null_for_roles()
    {
        // Setup
        $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);
        $engine = app(ApprovalEngine::class);

        // Reflection to test private method
        $reflection = new \ReflectionClass($engine);
        $method = $reflection->getMethod('resolveApproverSnapshot');
        $method->setAccessible(true);

        $result = $method->invoke($engine, 'role', $role->id);

        $this->assertNull($result['name'], 'Snapshot name should be null for roles');
        $this->assertEquals('test-role', $result['role_slug']);
    }

    public function test_approve_updates_snapshot_name_with_actual_user_name()
    {
        // Setup User & Role
        $user = User::factory()->create(['name' => 'Actual Signer']);
        $role = Role::create(['name' => 'director', 'guard_name' => 'web']);
        $user->assignRole($role);

        // Setup Signature
        UserSignature::create([
            'user_id' => $user->id,
            'signature_file' => 'sig.png',
            'is_active' => true,
            'is_default' => 1,
            'sha256' => 'hash'
        ]);

        // Setup Request & Step
        $pr = PurchaseRequest::factory()->create();
        $tpl = RuleTemplate::create(['name' => 'Tpl', 'model_type' => PurchaseRequest::class, 'is_active' => 1]);
        
        $req = new ApprovalRequest();
        $req->fill([
            'status' => 'IN_REVIEW',
            'current_step' => 1,
            'rule_template_id' => $tpl->id
        ]);
        $req->approvable()->associate($pr);
        $req->save();

        $step = $req->steps()->create([
            'sequence' => 1,
            'approver_type' => 'role',
            'approver_id' => $role->id,
            'approver_snapshot_name' => null, // Initial state
            'approver_snapshot_role_slug' => 'director',
            'status' => 'PENDING'
        ]);

        // Act
        $engine = app(ApprovalEngine::class);
        $engine->approve($pr, $user->id, 'Approved!');

        // Assert
        $step->refresh();
        $this->assertEquals('APPROVED', $step->status);
        $this->assertEquals('Actual Signer', $step->approver_snapshot_name, 'Snapshot name should be updated to user name');
    }
}
