<?php

namespace App\Filament\Resources\OvertimeFormApprovalResource\Pages;

use App\Filament\Resources\OvertimeFormApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageOvertimeFormApprovals extends ManageRecords
{
    protected static string $resource = OvertimeFormApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
