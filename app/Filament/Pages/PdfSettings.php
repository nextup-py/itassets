<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PdfSettings extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'PDF Asignación';

    protected static \UnitEnum|string|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.pdf-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Editor']) ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'company_name' => Setting::get('company_name', ''),
            'company_logo' => Setting::get('company_logo', null),
            'pdf_intro'    => Setting::get('pdf_intro', ''),
            'pdf_clauses'  => collect(Setting::get('pdf_clauses', []))->map(fn ($clause) => ['clause' => $clause])->toArray(),
            'pdf_closing'  => Setting::get('pdf_closing', ''),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->label('Nombre de la empresa')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),

                FileUpload::make('company_logo')
                    ->label('Logo de la empresa')
                    ->image()
                    ->disk('public')
                    ->directory('branding')
                    ->maxSize(2048)
                    ->imagePreviewHeight('120')
                    ->columnSpanFull(),

                Textarea::make('pdf_intro')
                    ->label('Texto introductorio')
                    ->helperText('Usá :company, :date, :employee, :document, :position como marcadores.')
                    ->rows(4)
                    ->columnSpanFull(),

                Repeater::make('pdf_clauses')
                    ->label('Cláusulas')
                    ->schema([
                        Textarea::make('clause')
                            ->label('')
                            ->rows(2)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->addActionLabel('Agregar cláusula')
                    ->reorderable()
                    ->columns(1)
                    ->columnSpanFull(),

                Textarea::make('pdf_closing')
                    ->label('Texto de cierre')
                    ->helperText('Usá :company como marcador.')
                    ->rows(3)
                    ->columnSpanFull(),

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

        Setting::set('company_name', $data['company_name']);
        Setting::set('company_logo', $data['company_logo']);
        Setting::set('pdf_intro', $data['pdf_intro']);
        Setting::set('pdf_clauses', collect($data['pdf_clauses'])->pluck('clause')->toArray());
        Setting::set('pdf_closing', $data['pdf_closing']);

        Notification::make()
            ->title('Configuración guardada correctamente')
            ->success()
            ->send();
    }

}
