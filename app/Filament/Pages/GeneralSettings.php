<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class GeneralSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Configuración general';

    protected static \UnitEnum|string|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.general-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Editor']) ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'base_currency' => Setting::get('base_currency', 'USD'),
            'display_currency' => Setting::get('display_currency', ''),
            'exchange_rate' => Setting::get('exchange_rate', 1),
            'display_locale' => Setting::get('display_locale', 'en_US'),
            'timezone' => Setting::get('timezone', config('app.timezone')),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Regional')
                    ->description('Moneda y configuración regional de esta instalación.')
                    ->schema([
                        TextInput::make('base_currency')
                            ->label('Moneda base')
                            ->helperText('Código ISO 4217 en el que se cargan los montos (ej. PYG, USD, ARS).')
                            ->required()
                            ->maxLength(3)
                            ->columnSpan(1),

                        TextInput::make('display_currency')
                            ->label('Moneda de reporte (opcional)')
                            ->helperText('Si se completa y difiere de la moneda base, los montos se muestran también convertidos a esta moneda.')
                            ->maxLength(3)
                            ->columnSpan(1),

                        TextInput::make('exchange_rate')
                            ->label('Tasa de cambio (moneda base → moneda de reporte)')
                            ->helperText('Solo se usa si la moneda de reporte difiere de la moneda base.')
                            ->numeric()
                            ->minValue(0)
                            ->columnSpan(1),

                        TextInput::make('display_locale')
                            ->label('Locale de formato')
                            ->helperText('Ej: es_PY, es_AR, en_US. Define el símbolo y separadores de miles/decimales.')
                            ->required()
                            ->maxLength(10)
                            ->columnSpan(1),

                        Select::make('timezone')
                            ->label('Zona horaria')
                            ->helperText('Define en qué zona horaria se muestran las fechas/horas del panel y a qué hora corre la verificación diaria de vencimientos.')
                            ->options(array_combine(\DateTimeZone::listIdentifiers(), \DateTimeZone::listIdentifiers()))
                            ->searchable()
                            ->required()
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Actions::make([
                    Action::make('save')
                        ->label('Guardar cambios')
                        ->submit('save'),
                ])->columnSpanFull(),
            ])
            ->statePath('data')
            ->live();
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('base_currency', $data['base_currency']);
        Setting::set('display_currency', $data['display_currency']);
        Setting::set('exchange_rate', $data['exchange_rate']);
        Setting::set('display_locale', $data['display_locale']);
        Setting::set('timezone', $data['timezone']);

        Notification::make()
            ->title('Configuración guardada correctamente')
            ->success()
            ->send();
    }
}
