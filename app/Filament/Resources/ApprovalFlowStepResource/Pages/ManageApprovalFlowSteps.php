<?php

namespace App\Filament\Resources\ApprovalFlowStepResource\Pages;

use App\Filament\Resources\ApprovalFlowStepResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageApprovalFlowSteps extends ManageRecords
{
    protected static string $resource = ApprovalFlowStepResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
