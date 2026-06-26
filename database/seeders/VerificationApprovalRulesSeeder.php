<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class VerificationApprovalRulesSeeder extends Seeder
{
    public function run(): void
    {
        $modelType = VerificationReport::class;

        DB::transaction(function () use ($modelType) {
            // 1) Delete old rule-templates for VerificationReport
            RuleTemplate::where('model_type', $modelType)->each(function (RuleTemplate $tpl) {
                $tpl->steps()->delete();
                $tpl->delete();
            });

            // 2) Get role ids used in steps
            $roleNames = [
                'leader',
                'department-head',
            ];

            $roles = Role::where('guard_name', config('auth.defaults.guard', 'web'))
                ->get()
                ->keyBy(fn($role) => strtolower($role->name));

            $getRoleId = function (string $name) use ($roles) {
                $role = $roles->get(strtolower($name));
                if (!$role) {
                    throw new \RuntimeException(
                        "Role '{$name}' not found. " .
                        "Please run `RolesAndPermissionsSeeder` first, then try again."
                    );
                }

                return $role->id;
            };

            // 3) Create default rule template (empty match matches any context)
            /** @var RuleTemplate $tpl */
            $tpl = RuleTemplate::create([
                'model_type' => $modelType,
                'code' => 'verification.default',
                'name' => 'Default Verification Report Workflow',
                'active' => true,
                'priority' => 100,
                'match_expr' => [], // Empty match array matches any context
            ]);

            // Step 1: Leader
            $tpl->steps()->create([
                'sequence' => 1,
                'approver_type' => 'role',
                'approver_id' => $getRoleId('leader'),
                'final' => false,
                'parallel_group' => 0,
            ]);

            // Step 2: Department Head
            $tpl->steps()->create([
                'sequence' => 2,
                'approver_type' => 'role',
                'approver_id' => $getRoleId('department-head'),
                'final' => true,
                'parallel_group' => 0,
            ]);
        });
    }
}
