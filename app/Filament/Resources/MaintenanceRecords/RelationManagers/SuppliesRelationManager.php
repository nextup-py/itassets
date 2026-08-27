<?php

namespace App\Filament\Resources\MaintenanceRecords\RelationManagers;

use App\Filament\Concerns\HasRelationManagerPermissions;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SuppliesRelationManager extends RelationManager
{
    use HasRelationManagerPermissions;

    protected static string $relationship = 'supplies';

    protected static ?string $title = 'Insumos utilizados';

    protected function getPermissionName(): string
    {
        return 'supply';
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Insumo'),

                TextColumn::make('pivot.quantity_used')
                    ->label('Cantidad utilizada'),

                TextColumn::make('quantity_available')
                    ->label('Disponible actualmente'),
            ])
            ->headerActions([
                // AttachAction/DetachAction have no dedicated authorization-response
                // hook wired up by HasRelationManagerPermissions (that trait only
                // covers create/update/delete), so they're gated explicitly here —
                // same reasoning as the custom actions on Assets\Pages\ViewAsset.
                AttachAction::make()
                    ->label('Utilizar insumo')
                    ->authorize('update_supply')
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->where('quantity_available', '>', 0))
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('quantity_used')
                            ->label('Cantidad')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                    ]),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Quitar')
                    ->authorize('update_supply'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->authorize('update_supply'),
                ]),
            ]);
    }
}
