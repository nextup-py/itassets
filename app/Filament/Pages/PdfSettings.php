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
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PdfSettings extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'PDF Asignación';

    protected static \UnitEnum|string|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'PDF Asignación';

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
            'pdf_title'    => Setting::get('pdf_title', 'Documento de Asignación de Equipamiento'),
            'pdf_intro'    => Setting::get('pdf_intro', ''),
            'pdf_clauses'  => collect(Setting::get('pdf_clauses', []))->map(fn ($clause) => ['clause' => $clause])->toArray(),
            'pdf_closing'  => Setting::get('pdf_closing', ''),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Marca')
                    ->description('Nombre y logo que se muestran en el encabezado del documento.')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Nombre de la empresa')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        FileUpload::make('company_logo')
                            ->label('Logo de la empresa')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('branding')
                            ->maxSize(2048)
                            ->imagePreviewHeight('120')
                            ->helperText('JPG, PNG o WEBP, máx. 2MB.')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Contenido del documento')
                    ->description('Título, texto introductorio, cláusulas y cierre del PDF de asignación.')
                    ->schema([
                        TextInput::make('pdf_title')
                            ->label('Título del documento')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('pdf_intro')
                            ->label('Texto introductorio')
                            ->helperText('Usá :company, :date, :employee, :document, :position, :legajo como marcadores.')
                            ->rows(4)
                            ->columnSpanFull(),

                        Repeater::make('pdf_clauses')
                            ->label('Cláusulas')
                            ->schema([
                                Textarea::make('clause')
                                    ->hiddenLabel()
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),
                            ])
                            ->addActionLabel('Agregar cláusula')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (?int $index): string => 'Cláusula ' . ($index + 1))
                            ->columns(1)
                            ->columnSpanFull(),

                        Textarea::make('pdf_closing')
                            ->label('Texto de cierre')
                            ->helperText('Usá :company como marcador.')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                \Filament\Schemas\Components\Actions::make([
                    Action::make('preview')
                        ->label('Vista previa')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->action(fn () => $this->preview()),

                    Action::make('save')
                        ->label('Guardar cambios')
                        ->submit('save'),
                ])->columnSpanFull(),
            ])
            ->statePath('data')
            ->live();
    }

    public function preview(): void
    {
        $data = $this->form->getState();

        $overrides = [
            'company_name' => $data['company_name'] ?? '',
            'pdf_title' => $data['pdf_title'] ?? '',
            'pdf_intro' => $data['pdf_intro'] ?? '',
            'pdf_clauses' => collect($data['pdf_clauses'] ?? [])->pluck('clause')->toArray(),
            'pdf_closing' => $data['pdf_closing'] ?? '',
        ];

        // A freshly-picked, not-yet-saved logo arrives as a TemporaryUploadedFile,
        // not a storage path — preview falls back to the already-saved logo in
        // that case rather than trying to render an unsaved upload.
        if (is_string($data['company_logo'] ?? null)) {
            $overrides['company_logo'] = $data['company_logo'];
        }

        $token = Str::random(32);
        Cache::put("pdf_preview.{$token}", $overrides, now()->addMinutes(2));

        $this->js('window.open(' . json_encode(route('assignments.pdf-preview', $token)) . ", '_blank')");
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('company_name', $data['company_name']);
        Setting::set('company_logo', $data['company_logo']);
        Setting::set('pdf_title', $data['pdf_title']);
        Setting::set('pdf_intro', $data['pdf_intro']);
        Setting::set('pdf_clauses', collect($data['pdf_clauses'])->pluck('clause')->toArray());
        Setting::set('pdf_closing', $data['pdf_closing']);

        Notification::make()
            ->title('Configuración guardada correctamente')
            ->success()
            ->send();
    }

}
