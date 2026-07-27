<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Assets\AssetResource;
use App\Models\Asset;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentAssetsWidget extends TableWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 1;

    protected ?string $pollingInterval = '60s';

    protected function getTableHeading(): string
    {
        return 'Últimos activos registrados';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Asset::latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('asset_tag')
                    ->label('Código')
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Activo')
                    ->limit(25),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->since(),
            ])
            ->recordUrl(fn (Asset $record): string => AssetResource::getUrl('view', ['record' => $record]))
            ->filters([])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
