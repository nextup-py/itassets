<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class GeneralSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Configuración general';

    protected static \UnitEnum|string|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.general-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'exchange_rate_usd_pyg' => Setting::get('exchange_rate_usd_pyg', 6500),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('exchange_rate_usd_pyg')
                    ->label('Tipo de cambio USD → Gs.')
                    ->helperText('Ej: 6500. Cada dólar se multiplica por este valor al mostrar precios en guaraníes.')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->columnSpan(2),

                \Filament\Schemas\Components\Actions::make([
                    Action::make('save')
                        ->label('Guardar cambios')
                        ->submit('save'),
                ])->columnSpanFull(),
            ])
            ->columns(2)
            ->statePath('data')
            ->live();
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('exchange_rate_usd_pyg', $data['exchange_rate_usd_pyg']);

        Notification::make()
            ->title('Configuración guardada correctamente')
            ->success()
            ->send();
    }
}
