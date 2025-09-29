<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApprovalFlowStepResource\Pages;
use App\Models\ApprovalFlowStep;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ApprovalFlowStepResource extends Resource
{
    protected static ?string $model = ApprovalFlowStep::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            //
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable()->toggleable(),

                Tables\Columns\TextColumn::make('flow.name') // requires flow() relation
                    ->label('Flow')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('step_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('role_slug')
                    ->label('Role')
                    ->formatStateUsing(
                        fn (string $state) => str(Str::title(str_replace('_', ' ', $state))), // dept_head → Dept Head
                    )
                    ->badge() // ← replaces BadgeColumn
                    ->color(
                        fn (string $state): string => match ($state) {
                            'director' => 'success',
                            'gm' => 'info',
                            'dept_head' => 'warning',
                            default => 'primary',
                        },
                    )
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('mandatory')
                    ->label('Mandatory')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->since() // shows “2 h ago”
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                //
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageApprovalFlowSteps::route('/'),
        ];
    }
}
