<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApprovalFlowRuleResource\Pages;
use App\Models\ApprovalFlowRule;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApprovalFlowRuleResource extends Resource
{
    protected static ?string $model = ApprovalFlowRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('department_id')
                ->relationship('department', 'name')
                ->searchable()
                ->placeholder('— Any —'),
            TextInput::make('branch')->placeholder('— Any —')->maxLength(50),
            Toggle::make('is_design')->label('Is Design?')->inline(false)->nullable(),
            Select::make('approval_flow_id')
                ->relationship('flow', 'name')
                ->required()
                ->searchable(),
            TextInput::make('priority')
                ->numeric()
                ->default(10)
                ->helperText('Lower number = evaluated first'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('priority')
            ->defaultSort('priority')
            ->columns([
                TextColumn::make('priority'),
                TextColumn::make('department.name')->label('Department'),
                TextColumn::make('branch')->label('Branch')->searchable(),
                IconColumn::make('is_design')->boolean(),
                TextColumn::make('flow.name')->label('To Flow'),
                TextColumn::make('approval_flow_id')->numeric()->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApprovalFlowRules::route('/'),
            'create' => Pages\CreateApprovalFlowRule::route('/create'),
            'edit' => Pages\EditApprovalFlowRule::route('/{record}/edit'),
        ];
    }
}
