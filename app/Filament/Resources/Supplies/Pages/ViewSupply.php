<?php

namespace App\Filament\Resources\Supplies\Pages;

use App\Filament\Resources\Supplies\SupplyResource;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Supply;
use App\Models\SupplyAdjustment;
use App\Models\SupplyMovement;
use App\Services\SupplyAdjustmentService;
use App\Services\SupplyMovementService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use RuntimeException;

class ViewSupply extends ViewRecord
{
    protected static string $resource = SupplyResource::class;

    protected function getHeaderActions(): array
    {
        $movementService = app(SupplyMovementService::class);
        $adjustmentService = app(SupplyAdjustmentService::class);

        return [
            Action::make('registerMovement')
                ->label('Registrar movimiento')
                ->icon('heroicon-o-arrows-right-left')
                ->color('success')
                ->authorize('update_supply')
                ->visible(fn () => $this->record->quantity_available > 0)
                ->form([
                    Radio::make('type')
                        ->label('Tipo de movimiento')
                        ->options(SupplyMovement::TYPES)
                        ->descriptions([
                            'entrega'   => 'Asignación unidireccional: solo descuenta el stock entregado.',
                            'reemplazo' => 'Se entrega un insumo funcional y se recibe uno dañado/para revisar.',
                        ])
                        ->required()
                        ->live()
                        ->default('entrega'),

                    TextInput::make('quantity')
                        ->label('Cantidad')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(fn () => $this->record->quantity_available)
                        ->default(1),

                    Radio::make('recipient_type')
                        ->label('Destino')
                        ->options([
                            'employee'   => 'Usuario',
                            'department' => 'Área',
                        ])
                        ->required()
                        ->live()
                        ->default('employee'),

                    Select::make('employee_id')
                        ->label('Empleado')
                        ->searchable()
                        ->required()
                        ->getSearchResultsUsing(fn (string $search): array => Employee::where('is_active', true)
                            ->where('name', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id')
                            ->toArray())
                        ->getOptionLabelUsing(fn ($value): ?string => Employee::find($value)?->name)
                        ->visible(fn (Get $get) => $get('recipient_type') === 'employee'),

                    Select::make('department_id')
                        ->label('Área')
                        ->searchable()
                        ->required()
                        ->getSearchResultsUsing(fn (string $search): array => Department::where('name', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id')
                            ->toArray())
                        ->getOptionLabelUsing(fn ($value): ?string => Department::find($value)?->name)
                        ->visible(fn (Get $get) => $get('recipient_type') === 'department'),

                    Select::make('received_supply_id')
                        ->label('Insumo recibido (dañado / para revisar)')
                        ->helperText('En blanco = el mismo insumo, en su lote de dañados.')
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search): array => Supply::query()
                            ->where('id', '!=', $this->record->id)
                            ->where('name', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id')
                            ->toArray())
                        ->getOptionLabelUsing(fn ($value): ?string => Supply::find($value)?->name)
                        ->visible(fn (Get $get) => $get('type') === 'reemplazo'),

                    DatePicker::make('performed_at')
                        ->label('Fecha')
                        ->required()
                        ->default(now())
                        ->displayFormat(current_date_format()),

                    Textarea::make('notes')
                        ->label('Notas')
                        ->rows(2),
                ])
                ->action(function (array $data) use ($movementService): void {
                    try {
                        $movementService->register($this->record, $data);
                    } catch (RuntimeException $e) {
                        Notification::make()->danger()->title('No se pudo registrar el movimiento')->body($e->getMessage())->send();

                        return;
                    }

                    $this->record->refresh();

                    Notification::make()->success()->title('Movimiento registrado correctamente')->send();
                }),

            Action::make('manualAdjustment')
                ->label('Baja manual')
                ->icon('heroicon-o-minus-circle')
                ->color('danger')
                ->authorize('update_supply')
                ->visible(fn () => $this->record->quantity_available > 0)
                ->requiresConfirmation()
                ->form([
                    TextInput::make('quantity')
                        ->label('Cantidad a dar de baja')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(fn () => $this->record->quantity_available)
                        ->default(1),

                    Select::make('reason')
                        ->label('Motivo')
                        ->options(SupplyAdjustment::REASONS)
                        ->required(),

                    Textarea::make('notes')
                        ->label('Notas')
                        ->rows(2),
                ])
                ->action(function (array $data) use ($adjustmentService): void {
                    try {
                        $adjustmentService->register($this->record, ['target' => 'available', ...$data]);
                    } catch (RuntimeException $e) {
                        Notification::make()->danger()->title('No se pudo registrar la baja')->body($e->getMessage())->send();

                        return;
                    }

                    $this->record->refresh();

                    Notification::make()->success()->title('Baja registrada correctamente')->send();
                }),

            Action::make('disposeDamaged')
                ->label('Disponer de dañados')
                ->icon('heroicon-o-trash')
                ->color('gray')
                ->authorize('update_supply')
                ->visible(fn () => $this->record->quantity_damaged > 0)
                ->requiresConfirmation()
                ->form([
                    TextInput::make('quantity')
                        ->label('Cantidad a disponer')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(fn () => $this->record->quantity_damaged)
                        ->default(1),

                    Select::make('reason')
                        ->label('Motivo')
                        ->options(SupplyAdjustment::DISPOSAL_REASONS)
                        ->required(),

                    Textarea::make('notes')
                        ->label('Notas')
                        ->rows(2),
                ])
                ->action(function (array $data) use ($adjustmentService): void {
                    try {
                        $adjustmentService->register($this->record, ['target' => 'damaged', ...$data]);
                    } catch (RuntimeException $e) {
                        Notification::make()->danger()->title('No se pudo registrar la disposición')->body($e->getMessage())->send();

                        return;
                    }

                    $this->record->refresh();

                    Notification::make()->success()->title('Disposición registrada correctamente')->send();
                }),

            EditAction::make(),
        ];
    }
}
