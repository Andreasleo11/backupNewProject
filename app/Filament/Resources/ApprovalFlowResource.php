<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApprovalFlowResource\Pages;
use App\Models\ApprovalFlow;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApprovalFlowResource extends Resource
{
    protected static ?string $model = ApprovalFlow::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->label('Flow name')->required()->maxLength(255),

            TextInput::make('slug')->label('Flow slug')->required()->maxLength(255),

            Select::make('created_by')
                ->label('Created by')
                ->relationship('creator', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Repeater::make('steps')
                ->relationship() // <- ties into hasMany steps()
                ->orderColumn('step_order') // drag & drop ordering
                ->label('Approval Steps')
                ->schema([
                    TextInput::make('step_order')
                        ->numeric()
                        ->visible(false) // auto-managed by drag-and-drop
                        ->default(0),

                    Select::make('role_slug')
                        ->label('Role')
                        ->options([
                            'creator' => 'Creator',
                            'dept_head' => 'Dept Head',
                            'supervisor' => 'Supervisor',
                            'gm' => 'GM',
                            'director' => 'Director',
                        ])
                        ->required()
                        ->columnSpan(2),

                    Toggle::make('mandatory')->inline()->label('Mandatory?')->default(true),
                ])
                ->grid(3) // nice 3-column layout
                ->addActionLabel('Add step')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('slug')->searchable()->copyable(),
                TextColumn::make('creator.name')->label('Created by'),
                TextColumn::make('steps_count')->counts('steps')->label('# Steps'),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                // any filters you like
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
            'index' => Pages\ListApprovalFlows::route('/'),
            'create' => Pages\CreateApprovalFlow::route('/create'),
            'edit' => Pages\EditApprovalFlow::route('/{record}/edit'),
        ];
    }
}
