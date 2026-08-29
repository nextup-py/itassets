<?php

namespace App\Filament\Resources\Supplies\Tables;

use App\Models\Supply;
use App\Models\SupplyAdjustment;
use App\Services\SupplyAdjustmentService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use RuntimeException;

class SuppliesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->placeholder('—'),

                TextColumn::make('quantity_available')
                    ->label('Disponible')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('quantity_damaged')
                    ->label('Dañado / Revisión')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray')
                    ->sortable(),

                TextColumn::make('location.name')
                    ->label('Ubicación')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date(current_date_format())
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('asset_category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('location_id')
                    ->label('Ubicación')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('manualAdjustment')
                    ->label('Baja manual')
                    ->icon('heroicon-o-minus-circle')
                    ->color('danger')
                    ->authorize('update_supply')
                    ->visible(fn (Supply $record): bool => $record->quantity_available > 0)
                    ->form([
                        TextInput::make('quantity')
                            ->label('Cantidad a dar de baja')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(fn (Supply $record) => $record->quantity_available)
                            ->default(1),

                        Select::make('reason')
                            ->label('Motivo')
                            ->options(SupplyAdjustment::REASONS)
                            ->required(),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2),
                    ])
                    ->action(function (Supply $record, array $data): void {
                        try {
                            app(SupplyAdjustmentService::class)->register($record, $data);
                        } catch (RuntimeException $e) {
                            Notification::make()->danger()->title('No se pudo registrar la baja')->body($e->getMessage())->send();

                            return;
                        }

                        Notification::make()->success()->title('Baja registrada correctamente')->send();
                    }),
            ])
            ->defaultSort('name');
    }
}
