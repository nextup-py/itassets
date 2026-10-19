<?php

namespace App\Filament\Resources\Suppliers\RelationManagers;

use App\Filament\Resources\ServiceContracts\ServiceContractResource;
use App\Models\ServiceContract;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceContracts';

    protected static ?string $title = 'Contratos de Servicio';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contract_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (ServiceContract $record): string => $record->getTypeLabel()),

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
                    ->url(fn (ServiceContract $record) => ServiceContractResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('name');
    }
}
