<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class PrApprovalRulesSeeder extends Seeder
{
    public function run(): void
    {
        // Sesuaikan dengan FQCN model PurchaseRequest kamu
        // $modelType = \App\Infrastructure\Persistence\Eloquent\Models\PurchaseRequest::class;
        $modelType = \App\Models\PurchaseRequest::class;

        DB::transaction(function () use ($modelType) {

            // 1) Hapus rule-template lama khusus PurchaseRequest saja
            RuleTemplate::where('model_type', $modelType)->each(function (RuleTemplate $tpl) {
                $tpl->steps()->delete();
                $tpl->delete();
            });

            // 2) Ambil role id yg dipakai di step
            $roleNames = [
                'pr-dept-head-office',
                'pr-dept-head-factory',
                'pr-head-design',
                'pr-gm-factory',
                'pr-verificator',
                'pr-purchaser',
                'pr-director',
            ];

            $roles = Role::whereIn('name', $roleNames)
                ->where('guard_name', config('auth.defaults.guard', 'web'))
                ->get()
                ->keyBy('name');

            $getRoleId = function (string $name) use ($roles) {
                $role = $roles->get($name);
                if (! $role) {
                    throw new \RuntimeException("Role '{$name}' not found. Run PrRoleMappingSeeder first and/or check roles table.");
                }

                return $role->id;
            };

            // 3) Definisikan rule templates
            // priority: makin kecil = makin diutamakan
            $rules = [

                // ========= MOULDING + is_import (lebih spesifik, priority kecil) =========

                [
                    'code' => 'pr.moulding.to-maintenance',
                    'name' => 'PR MOULDING → Maintenance',
                    'priority' => 10,
                    'match' => [
                        'from_department' => 'MOULDING',
                        'to_department' => 'Maintenance',
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'pr-dept-head-factory'],
                        ['seq' => 2, 'role' => 'pr-head-design'],
                        ['seq' => 3, 'role' => 'pr-purchaser'],
                        ['seq' => 4, 'role' => 'pr-director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'pr.moulding.to-purchasing',
                    'name' => 'PR MOULDING → Purchasing',
                    'priority' => 10,
                    'match' => [
                        'from_department' => 'MOULDING',
                        'to_department' => 'Purchasing',
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'pr-dept-head-factory'],
                        ['seq' => 2, 'role' => 'pr-purchaser'],
                        ['seq' => 3, 'role' => 'pr-director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'pr.moulding.to-computer',
                    'name' => 'PR MOULDING → Computer',
                    'priority' => 10,
                    'match' => [
                        'from_department' => 'MOULDING',
                        'to_department' => 'Computer',
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'pr-dept-head-factory'],
                        ['seq' => 2, 'role' => 'pr-purchaser'],
                        ['seq' => 3, 'role' => 'pr-verificator'],
                        ['seq' => 4, 'role' => 'pr-director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'pr.moulding.to-personalia',
                    'name' => 'PR MOULDING → Personalia',
                    'priority' => 10,
                    'match' => [
                        'from_department' => 'MOULDING',
                        'to_department' => 'Personnel',
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'pr-dept-head-factory'],
                        ['seq' => 2, 'role' => 'pr-purchaser'],
                        ['seq' => 3, 'role' => 'pr-verificator'],
                        ['seq' => 4, 'role' => 'pr-director', 'final' => true],
                    ],
                ],

                // ========= Office departments =========

                [
                    'code' => 'pr.office.to-maintenance',
                    'name' => 'PR Office → Maintenance',
                    'priority' => 100,
                    'match' => [
                        'at_office' => true,
                        'to_department' => 'Maintenance',
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'pr-dept-head-office'],
                        ['seq' => 2, 'role' => 'pr-purchaser'],
                        ['seq' => 3, 'role' => 'pr-director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'pr.office.to-purchasing',
                    'name' => 'PR Office → Purchasing',
                    'priority' => 100,
                    'match' => [
                        'at_office' => true,
                        'to_department' => 'Purchasing',
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'pr-dept-head-office'],
                        ['seq' => 2, 'role' => 'pr-purchaser'],
                        ['seq' => 3, 'role' => 'pr-director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'pr.office.to-computer',
                    'name' => 'PR Office → Computer (with verificator)',
                    'priority' => 100,
                    'match' => [
                        'at_office' => true,
                        'to_department' => 'Computer',
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'pr-dept-head-office'],
                        ['seq' => 2, 'role' => 'pr-purchaser'],
                        ['seq' => 3, 'role' => 'pr-verificator'],
                        ['seq' => 4, 'role' => 'pr-director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'pr.office.to-personalia',
                    'name' => 'PR Office → Personalia (with verificator)',
                    'priority' => 100,
                    'match' => [
                        'at_office' => true,
                        'to_department' => 'Personnel',
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'pr-dept-head-office'],
                        ['seq' => 2, 'role' => 'pr-purchaser'],
                        ['seq' => 3, 'role' => 'pr-verificator'],
                        ['seq' => 4, 'role' => 'pr-director', 'final' => true],
                    ],
                ],

                // ========= Factory departments =========

                [
                    'code' => 'pr.factory.to-maintenance',
                    'name' => 'PR Factory → Maintenance',
                    'priority' => 100,
                    'match' => [
                        'at_office' => false,
                        'to_department' => 'Maintenance',
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'pr-dept-head-factory'],
                        ['seq' => 2, 'role' => 'pr-gm-factory'],
                        ['seq' => 3, 'role' => 'pr-purchaser'],
                        ['seq' => 4, 'role' => 'pr-director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'pr.factory.to-purchasing',
                    'name' => 'PR Factory → Purchasing',
                    'priority' => 100,
                    'match' => [
                        'at_office' => false,
                        'to_department' => 'Purchasing',
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'pr-dept-head-factory'],
                        ['seq' => 2, 'role' => 'pr-gm-factory'],
                        ['seq' => 3, 'role' => 'pr-purchaser'],
                        ['seq' => 4, 'role' => 'pr-director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'pr.factory.to-computer',
                    'name' => 'PR Factory → Computer (GM + verificator)',
                    'priority' => 100,
                    'match' => [
                        'at_office' => false,
                        'to_department' => 'Computer',
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'pr-dept-head-factory'],
                        ['seq' => 2, 'role' => 'pr-gm-factory'],
                        ['seq' => 3, 'role' => 'pr-purchaser'],
                        ['seq' => 4, 'role' => 'pr-verificator'],
                        ['seq' => 5, 'role' => 'pr-director', 'final' => true],
                    ],
                ],
                [
                    'code' => 'pr.factory.to-personalia',
                    'name' => 'PR Factory → Personalia (GM + verificator)',
                    'priority' => 100,
                    'match' => [
                        'at_office' => false,
                        'to_department' => 'Personnel',
                    ],
                    'steps' => [
                        ['seq' => 1, 'role' => 'pr-dept-head-factory'],
                        ['seq' => 2, 'role' => 'pr-gm-factory'],
                        ['seq' => 3, 'role' => 'pr-purchaser'],
                        ['seq' => 4, 'role' => 'pr-verificator'],
                        ['seq' => 5, 'role' => 'pr-director', 'final' => true],
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
                    // match_expr dikast ke array di model
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
