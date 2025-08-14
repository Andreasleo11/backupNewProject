<?php

namespace App\Filament\Resources\ApprovalFlowRuleResource\Pages;

use App\Filament\Resources\ApprovalFlowRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApprovalFlowRule extends EditRecord
{
    protected static string $resource = ApprovalFlowRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
