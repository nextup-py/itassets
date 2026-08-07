<?php

namespace App\Filament\Resources\Suppliers\RelationManagers;

use App\Filament\Resources\Licenses\LicenseResource;
use App\Models\License;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LicensesRelationManager extends RelationManager
{
    protected static string $relationship = 'licenses';

    protected static ?string $title = 'Licencias';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('license_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => License::TYPES[$state] ?? $state),

                TextColumn::make('expiry_date')
                    ->label('Vence')
                    ->date(current_date_format())
                    ->placeholder('Sin vencimiento'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (License $record) => LicenseResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('product_name');
    }
}
