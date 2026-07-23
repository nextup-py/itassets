<x-filament::page>
    <div class="space-y-6">
        <x-filament::card>
            <h2 class="text-lg font-medium">Exportar activos</h2>
            <p class="text-sm text-gray-500 mb-4">Descargar un reporte detallado de todos los activos, con filtros por estado y categoría.</p>
            {{ $this->exportAssetsAction }}
        </x-filament::card>

        <x-filament::card>
            <h2 class="text-lg font-medium">Exportar asignaciones</h2>
            <p class="text-sm text-gray-500 mb-4">Descargar un reporte de asignaciones de activos a empleados, con filtro por estado.</p>
            {{ $this->exportAssignmentsAction }}
        </x-filament::card>

        <x-filament::card>
            <h2 class="text-lg font-medium">Resumen de inventario</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="p-4 bg-gray-50 rounded-lg text-center">
                    <div class="text-3xl font-bold text-gray-700">{{ $this->totalAssets }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total activos</div>
                </div>
                <div class="p-4 bg-green-50 rounded-lg text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $this->availableAssets }}</div>
                    <div class="text-sm text-gray-500 mt-1">Disponibles</div>
                </div>
                <div class="p-4 bg-blue-50 rounded-lg text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $this->assignedAssets }}</div>
                    <div class="text-sm text-gray-500 mt-1">Asignados</div>
                </div>
                <div class="p-4 bg-yellow-50 rounded-lg text-center">
                    <div class="text-3xl font-bold text-yellow-600">{{ $this->maintenanceAssets }}</div>
                    <div class="text-sm text-gray-500 mt-1">En mantenimiento</div>
                </div>
                <div class="p-4 bg-red-50 rounded-lg text-center">
                    <div class="text-3xl font-bold text-red-600">{{ $this->retiredAssets }}</div>
                    <div class="text-sm text-gray-500 mt-1">Dados de baja</div>
                </div>
                <div class="p-4 bg-purple-50 rounded-lg text-center">
                    <div class="text-3xl font-bold text-purple-600">{{ format_currency($this->totalValue) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Valor total</div>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-filament::page>
