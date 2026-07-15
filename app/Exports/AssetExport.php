<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Asset::with(['category', 'location', 'assignedTo', 'employee', 'department'])->get();
    }

    public function headings(): array
    {
        return [
            'Asset Tag',
            'Name',
            'Brand',
            'Category',
            'Status',
            'Location',
            'Assigned To',
            'IP Address',
            'Username (Operator)',
            'Purpose',
            'Operating System',
            'Department',
            'Purchase Date',
            'Purchase Cost',
            'Warranty Expiry',
            'Notes',
        ];
    }

    public function map($asset): array
    {
        return [
            $asset->asset_tag,
            $asset->name,
            $asset->brand,
            $asset->category->name ?? '-',
            ucfirst(str_replace('_', ' ', $asset->status)),
            $asset->location->name ?? '-',
            $asset->employee->name ?? $asset->assignedTo->name ?? $asset->assigned_to_nik ?? '-',
            $asset->ip_address,
            $asset->username,
            $asset->purpose,
            $asset->os,
            $asset->department->name ?? '-',
            $asset->purchase_date,
            $asset->purchase_cost,
            $asset->warranty_expiry,
            $asset->notes,
        ];
    }
}
