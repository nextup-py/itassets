<?php

namespace App\Filament\Pages;

use App\Mail\TestMail;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class MailSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Correo';

    protected static \UnitEnum|string|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Configuración de correo';

    protected string $view = 'filament.pages.mail-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('Admin') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'mail_mailer' => Setting::get('mail_mailer', 'log'),
            'mail_from_address' => Setting::get('mail_from_address', ''),
            'mail_from_name' => Setting::get('mail_from_name', config('app.name')),
            'mail_resend_key' => static::decrypt(Setting::get('mail_resend_key')),
            'mail_smtp_host' => Setting::get('mail_smtp_host', ''),
            'mail_smtp_port' => Setting::get('mail_smtp_port', 587),
            'mail_smtp_username' => Setting::get('mail_smtp_username', ''),
            'mail_smtp_password' => static::decrypt(Setting::get('mail_smtp_password')),
            'mail_smtp_encryption' => Setting::get('mail_smtp_encryption', 'tls'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Proveedor de correo')
                    ->description('Quién envía las notificaciones del sistema (asignaciones, vencimientos, etc.).')
                    ->schema([
                        Select::make('mail_mailer')
                            ->label('Proveedor')
                            ->options([
                                'log' => 'Desactivado (solo registrar en logs, no se envía nada)',
                                'resend' => 'Resend',
                                'smtp' => 'SMTP',
                            ])
                            ->default('log')
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                    ]),

                Section::make('Remitente')
                    ->schema([
                        TextInput::make('mail_from_address')
                            ->label('Correo remitente')
                            ->email()
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('mail_from_name')
                            ->label('Nombre remitente')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Resend')
                    ->schema([
                        TextInput::make('mail_resend_key')
                            ->label('API key')
                            ->password()
                            ->revealable()
                            ->required(fn (Get $get): bool => $get('mail_mailer') === 'resend')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Get $get): bool => $get('mail_mailer') === 'resend'),

                Section::make('SMTP')
                    ->schema([
                        TextInput::make('mail_smtp_host')
                            ->label('Host')
                            ->required(fn (Get $get): bool => $get('mail_mailer') === 'smtp')
                            ->columnSpan(2),

                        TextInput::make('mail_smtp_port')
                            ->label('Puerto')
                            ->numeric()
                            ->required(fn (Get $get): bool => $get('mail_mailer') === 'smtp')
                            ->columnSpan(1),

                        TextInput::make('mail_smtp_username')
                            ->label('Usuario')
                            ->columnSpan(1),

                        TextInput::make('mail_smtp_password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->columnSpan(1),

                        Select::make('mail_smtp_encryption')
                            ->label('Encriptación')
                            ->options([
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                                '' => 'Ninguna',
                            ])
                            ->default('tls')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get): bool => $get('mail_mailer') === 'smtp'),

                \Filament\Schemas\Components\Actions::make([
                    Action::make('sendTest')
                        ->label('Enviar correo de prueba')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('gray')
                        ->form([
                            TextInput::make('test_email')
                                ->label('Enviar a')
                                ->email()
                                ->required()
                                ->default(fn () => auth()->user()?->email),
                        ])
                        ->action(fn (array $data) => $this->sendTest($data['test_email'])),

                    Action::make('save')
                        ->label('Guardar cambios')
                        ->submit('save'),
                ])->columnSpanFull(),
            ])
            ->statePath('data')
            ->live();
    }

    public function sendTest(string $testEmail): void
    {
        $data = $this->form->getState();

        $this->applyMailConfig($data);

        try {
            Mail::to($testEmail)->send(new TestMail);

            Notification::make()
                ->title('Correo de prueba enviado a ' . $testEmail)
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('No se pudo enviar el correo de prueba')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function applyMailConfig(array $data): void
    {
        config(['mail.default' => $data['mail_mailer']]);
        config(['mail.from.address' => $data['mail_from_address']]);
        config(['mail.from.name' => $data['mail_from_name']]);

        match ($data['mail_mailer']) {
            'resend' => config(['mail.mailers.resend.key' => $data['mail_resend_key'] ?? null]),
            'smtp' => config([
                'mail.mailers.smtp.host' => $data['mail_smtp_host'] ?? null,
                'mail.mailers.smtp.port' => $data['mail_smtp_port'] ?? null,
                'mail.mailers.smtp.username' => $data['mail_smtp_username'] ?? null,
                'mail.mailers.smtp.password' => $data['mail_smtp_password'] ?? null,
                'mail.mailers.smtp.encryption' => ($data['mail_smtp_encryption'] ?? null) ?: null,
            ]),
            default => null,
        };
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('mail_mailer', $data['mail_mailer']);
        Setting::set('mail_from_address', $data['mail_from_address']);
        Setting::set('mail_from_name', $data['mail_from_name']);
        Setting::set('mail_resend_key', static::encrypt($data['mail_resend_key'] ?? null));
        Setting::set('mail_smtp_host', $data['mail_smtp_host'] ?? '');
        Setting::set('mail_smtp_port', $data['mail_smtp_port'] ?? '');
        Setting::set('mail_smtp_username', $data['mail_smtp_username'] ?? '');
        Setting::set('mail_smtp_password', static::encrypt($data['mail_smtp_password'] ?? null));
        Setting::set('mail_smtp_encryption', $data['mail_smtp_encryption'] ?? '');

        Notification::make()
            ->title('Configuración guardada correctamente')
            ->success()
            ->send();
    }

    protected static function encrypt(?string $value): ?string
    {
        return filled($value) ? Crypt::encryptString($value) : $value;
    }

    protected static function decrypt(?string $value): ?string
    {
        if (blank($value)) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }
}
