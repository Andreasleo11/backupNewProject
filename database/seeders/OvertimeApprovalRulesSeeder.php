<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class OvertimeApprovalRulesSeeder extends Seeder
{
    public function run(): void
    {
        $modelType = \App\Domain\Overtime\Models\OvertimeForm::class;

        DB::transaction(function () use ($modelType) {
            // 1) Hapus rule-template lama khusus Overtime
            RuleTemplate::where('model_type', $modelType)->each(function (RuleTemplate $tpl) {
                $tpl->steps()->delete();
                $tpl->delete();
            });

            // 2) Ambil role id yg dipakai di step
            $roleNames = [
                'department-head',
                'general-manager',
                'director',
            ];

            $roles = Role::whereIn('name', $roleNames)
                ->where('guard_name', config('auth.defaults.guard', 'web'))
                ->get()
                ->keyBy('name');

            $getRoleId = function (string $name) use ($roles) {
                $role = $roles->get($name);
                if (! $role) {
                    throw new \RuntimeException("Role '{$name}' not found. Run RolesAndPermissionsSeeder first and/or check roles table.");
                }

                return $role->id;
            };

            // 3) Definisikan rule templates dari matriks Overtime lama
            $rules = [
                [
                    'code' => 'ot.dept10.karawang',
                    'name' => 'OT Dept 10 (Karawang) → GM → Director',
                    'priority' => 1,
                    'match' => [
                        'department_id_in' => [10],
                        'branch_in' => ['KARAWANG'],
                        'is_design' => false,
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'general-manager'],
                        ['seq' => 2, 'role' => 'director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'ot.dept10.other',
                    'name' => 'OT Dept 10 → Dept Head → Director',
                    'priority' => 2,
                    'match' => [
                        'department_id_in' => [10],
                        'is_design' => false,
                    ],
                    'steps' => [
                        // Supervisor omitted as role does not exist; mapped directly to dept-head
                        ['seq' => 1, 'role' => 'department-head'],
                        ['seq' => 2, 'role' => 'director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'ot.dept15',
                    'name' => 'OT Dept 15 → Dept Head → GM → Director',
                    'priority' => 3,
                    'match' => [
                        'department_id_in' => [15],
                        'is_design' => false,
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'department-head'],
                        ['seq' => 2, 'role' => 'general-manager'],
                        ['seq' => 3, 'role' => 'director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'ot.dept8.karawang',
                    'name' => 'OT Dept 8 (Karawang) → GM → Director',
                    'priority' => 4,
                    'match' => [
                        'department_id_in' => [8],
                        'branch_in' => ['KARAWANG'],
                        'is_design' => false,
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'general-manager'],
                        ['seq' => 2, 'role' => 'director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'ot.dept8.other',
                    'name' => 'OT Dept 8 → Dept Head → GM → Director',
                    'priority' => 5,
                    'match' => [
                        'department_id_in' => [8],
                        'is_design' => false,
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'department-head'],
                        ['seq' => 2, 'role' => 'general-manager'],
                        ['seq' => 3, 'role' => 'director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'ot.dept18.karawang',
                    'name' => 'OT Dept 18 (Karawang) → GM → Director',
                    'priority' => 6,
                    'match' => [
                        'department_id_in' => [18],
                        'branch_in' => ['KARAWANG'],
                        'is_design' => false,
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'general-manager'],
                        ['seq' => 2, 'role' => 'director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'ot.dept18.other',
                    'name' => 'OT Dept 18 → Dept Head → GM → Director',
                    'priority' => 7,
                    'match' => [
                        'department_id_in' => [18],
                        'is_design' => false,
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'department-head'],
                        ['seq' => 2, 'role' => 'general-manager'],
                        ['seq' => 3, 'role' => 'director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'ot.default',
                    'name' => 'OT Default Fallback → Dept Head → Director',
                    'priority' => 999,
                    'match' => [],
                    'steps' => [
                        ['seq' => 1, 'role' => 'department-head'],
                        ['seq' => 2, 'role' => 'director', 'final' => true],
                    ],
                ],
            ];

            // 4) Insert ke DB
            foreach ($rules as $rule) {
                /** @var RuleTemplate $tpl */
                $tpl = RuleTemplate::create([
                    'model_type' => $modelType,
                    'code' => $rule['code'],
                    'name' => $rule['name'],
                    'active' => true,
                    'priority' => $rule['priority'],
                    'match_expr' => $rule['match'],
                ]);

                foreach ($rule['steps'] as $step) {
                    $tpl->steps()->create([
                        'sequence' => $step['seq'],
                        'approver_type' => 'role',
                        'approver_id' => $getRoleId($step['role']),
                        'final' => (bool) ($step['final'] ?? false),
                        'parallel_group' => 0,
                    ]);
                }
            }
        });
    }
}
