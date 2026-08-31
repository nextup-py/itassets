<?php

namespace App\Filament\Resources\Assets\Tables;

use App\Models\Asset;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class AssetsTable
{
    private const QUICK_STATUSES = ['stock', 'available', 'retired', 'lost'];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('')
                    ->disk('public')
                    ->height(40)
                    ->width(40)
                    ->defaultImageUrl(null)
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('asset_tag')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('brand')
                    ->label('Marca')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('serial_number')
                    ->label('N/S')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (Asset $record): string => $record->getStatusLabel())
                    ->color(fn (Asset $record): string => $record->getStatusBadgeColor())
                    ->sortable(),

                TextColumn::make('location.name')
                    ->label('Ubicación')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('warranty_expiry_date')
                    ->label('Garantía')
                    ->date(current_date_format())
                    ->placeholder('—')
                    ->color(fn ($record) => $record?->warranty_expiry_date?->isPast() ? 'danger' : null)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Asset::STATUSES),

                SelectFilter::make('location_id')
                    ->label('Ubicación')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('changeStatus')
                    ->label('Cambiar estado')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->authorize('update_asset')
                    ->form([
                        Select::make('status')
                            ->label('Nuevo estado')
                            ->required()
                            ->options(array_intersect_key(Asset::STATUSES, array_flip(self::QUICK_STATUSES))),
                    ])
                    ->action(function (Asset $record, array $data): void {
                        $record->update(['status' => $data['status']]);

                        Notification::make()->success()->title('Estado actualizado correctamente')->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('changeStatus')
                        ->label('Cambiar estado')
                        ->icon('heroicon-o-arrow-path')
                        ->authorize('update_asset')
                        ->form([
                            Select::make('status')
                                ->label('Nuevo estado')
                                ->required()
                                ->options(array_intersect_key(Asset::STATUSES, array_flip(self::QUICK_STATUSES))),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update(['status' => $data['status']]);

                            Notification::make()->success()->title('Estado actualizado correctamente')->send();
                        }),
                ]),
            ])
            ->defaultSort('asset_tag');
    }
}
