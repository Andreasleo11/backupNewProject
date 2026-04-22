<?php

namespace Database\Seeders;

use App\Domains\Ticketing\Entities\TicketCategory;
use Illuminate\Database\Seeder;

class TicketCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Hardware Issue',
                'description' => 'Issues related to physical devices like laptops, printers, etc.',
                'sla_hours' => 24, // 1 business day
            ],
            [
                'name' => 'Software / Application Bug',
                'description' => 'Errors or crashes in internal or official software.',
                'sla_hours' => 48, // 2 business days
            ],
            [
                'name' => 'Network & Connectivity',
                'description' => 'Internet dropping, VPN issues, or LAN disconnected.',
                'sla_hours' => 8, // Very urgent, 1 business day
            ],
            [
                'name' => 'Access & Permissions',
                'description' => 'Requests for new accounts, password resets, or system permissions.',
                'sla_hours' => 12,
            ],
            [
                'name' => 'Feature Request',
                'description' => 'Requests for new features in internal applications (ERP, HRIS).',
                'sla_hours' => 120, // 5 business days
            ],
        ];

        foreach ($categories as $categoryData) {
            TicketCategory::updateOrCreate(
                ['name' => $categoryData['name']],
                $categoryData
            );
        }
    }
}
