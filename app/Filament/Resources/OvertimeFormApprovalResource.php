<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OvertimeFormApprovalResource\Pages;
use App\Models\OvertimeFormApproval;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OvertimeFormApprovalResource extends Resource
{
    protected static ?string $model = OvertimeFormApproval::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Logs';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('overtime_form_id')
                ->label('Overtime Form')
                ->relationship('form', 'id') // show document number if you have it
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('flow_step_id')
                ->label('Flow Step')
                ->relationship(
                    name: 'step',
                    titleAttribute: 'role_slug',
                    modifyQueryUsing: fn ($query) => $query->orderBy('step_order'),
                )
                ->getOptionLabelFromRecordUsing(
                    fn ($record) => Str::title(str_replace('_', ' ', $record->role_slug)),
                )
                ->required(),

            Forms\Components\Select::make('approver_id')
                ->label('Approver')
                ->relationship('approver', 'name')
                ->searchable()
                ->preload(),

            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->native(false)
                ->required()
                ->default('pending'),

            Forms\Components\TextInput::make('signature_path')->label('Signature Path')->nullable(),

            Forms\Components\DateTimePicker::make('signed_at')->label('Signed At'),

            Forms\Components\Textarea::make('comment')->rows(3)->maxLength(500),
        ]);
    }

    /* -----------------------------------------------------------------
     |  TABLE
     |----------------------------------------------------------------- */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->toggleable(),

                Tables\Columns\TextColumn::make('form.id')
                    ->label('Form ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('step.step_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('step.role_slug')
                    ->label('Role')
                    ->formatStateUsing(
                        fn (string $state) => Str::title(str_replace('_', ' ', $state)),
                    )
                    ->badge()
                    ->color(
                        fn (string $state): string => match ($state) {
                            'director' => 'success',
                            'gm' => 'info',
                            'dept_head' => 'warning',
                            'supervisor' => 'primary',
                            default => 'secondary',
                        },
                    )
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Approver')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(
                        fn (string $state): string => match ($state) {
                            'approved' => 'success',
                            'rejected' => 'danger',
                            default => 'warning', // pending
                        },
                    )
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('signature_path')
                    ->label('Signature Path')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('signed_at')
                    ->label('Signed At')
                    ->since()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('comment')
                    ->limit(30)
                    ->tooltip(fn (?string $state) => $state)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Logged')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->tooltip('Edit this log entry'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    /* -----------------------------------------------------------------
     |  PAGES
     |----------------------------------------------------------------- */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOvertimeFormApprovals::route('/'),
        ];
    }
}
