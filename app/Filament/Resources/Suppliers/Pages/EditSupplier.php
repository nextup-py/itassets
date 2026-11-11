<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use App\Models\Supplier;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // service_contracts.supplier_id is restrictOnDelete() (unlike assets/
            // licenses/maintenance_records/supplies, which null the FK on delete
            // instead), so deleting a supplier that still has service contracts
            // would otherwise throw a raw QueryException — guarded the same way
            // EditDepartment guards against deleting a department with employees.
            DeleteAction::make()
                ->before(function (DeleteAction $action, Supplier $record): void {
                    if ($record->serviceContracts()->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('No se puede eliminar')
                            ->body('Este proveedor tiene contratos de servicio asociados. Eliminá o reasigná esos contratos primero.')
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
}
