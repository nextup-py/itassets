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
    </div>
</x-filament::page>
