<div class="filament-date-range-filter w-full">
    <div class="flex flex-wrap items-center gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Filtrar por fecha:
        </span>

        <div class="flex items-center gap-2">
            <input
                type="date"
                wire:model.live="from"
                class="block rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
            <span class="text-gray-400 text-sm">a</span>
            <input
                type="date"
                wire:model.live="to"
                class="block rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
        </div>

        <div class="flex items-center gap-1">
            <x-filament::button size="xs" color="gray" wire:click="setToday">
                Hoy
            </x-filament::button>
            <x-filament::button size="xs" color="gray" wire:click="setLast7Days">
                7 días
            </x-filament::button>
            <x-filament::button size="xs" color="gray" wire:click="setThisMonth">
                Este mes
            </x-filament::button>
            <x-filament::button size="xs" color="gray" wire:click="setThisYear">
                Este año
            </x-filament::button>
            <x-filament::button size="xs" color="warning" wire:click="clearFilter">
                Limpiar
            </x-filament::button>
        </div>
    </div>
</div>
