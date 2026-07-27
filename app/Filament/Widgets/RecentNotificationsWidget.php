<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Notifications\DatabaseNotification;

class RecentNotificationsWidget extends TableWidget
{
    protected static ?int $sort = 8;

    protected int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    protected function getTableHeading(): string
    {
        return 'Notificaciones recientes';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DatabaseNotification::query()
                    ->where('notifiable_type', auth()->user()?->getMorphClass() ?? '')
                    ->where('notifiable_id', auth()->id() ?? 0)
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('')
                    ->dateTime('d/m H:i')
                    ->width(1),

                TextColumn::make('data')
                    ->label('Mensaje')
                    ->html()
                    ->state(fn (DatabaseNotification $record): string => $record->data['message'] ?? $record->data['title'] ?? '—')
                    ->wrap(),

                TextColumn::make('read_at')
                    ->label('')
                    ->state(fn (DatabaseNotification $record): string => $record->read_at ? '✓' : '●')
                    ->color(fn (DatabaseNotification $record): string => $record->read_at ? 'gray' : 'primary')
                    ->width(1),
            ])
            ->recordAction('markAsRead')
            ->recordActions([
                Action::make('markAsRead')
                    ->action(function (DatabaseNotification $record): void {
                        $record->markAsRead();
                        Notification::make()
                            ->title('Notificación marcada como leída')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
