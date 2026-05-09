<?php
namespace App\Filament\Resources;
use App\Filament\Resources\OnuMonitorResource\Pages;
use App\Models\OnuMonitor;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

class OnuMonitorResource extends Resource
{
    protected static ?string $model = OnuMonitor::class;
    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string { return 'heroicon-o-wifi'; }
    public static function getNavigationLabel(): string { return 'ONU Monitor'; }
    public static function getNavigationGroup(): string { return 'OLT Management'; }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('onu_id')->label('ONU ID')->searchable()->sortable(),
                TextColumn::make('olt.name')->label('OLT')->sortable(),
                TextColumn::make('pon_port')->label('PON Port')->sortable(),
                TextColumn::make('description')->label('Description')->searchable(),
                TextColumn::make('mac')->label('MAC')->searchable(),
                TextColumn::make('status')->badge()
                    ->color(fn($state) => $state === 'online' ? 'success' : 'danger'),
                TextColumn::make('rx_power')->label('RX Power (dBm)')
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2) . ' dBm' : 'N/A'),
                TextColumn::make('signal_status')->label('Signal')->badge()
                    ->color(fn($state) => match($state) {
                        'normal'   => 'success',
                        'warning'  => 'warning',
                        'critical' => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('last_seen_at')->label('Last Seen')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('olt_id')
                    ->label('OLT')
                    ->relationship('olt', 'name'),
                SelectFilter::make('status')
                    ->options(['online' => 'Online', 'offline' => 'Offline']),
                SelectFilter::make('signal_status')
                    ->label('Signal')
                    ->options([
                        'normal'   => 'Normal',
                        'warning'  => 'Warning',
                        'critical' => 'Critical',
                        'unknown'  => 'Unknown',
                    ]),
                SelectFilter::make('pon_port')
                    ->label('PON Port')
                    ->options(fn() => OnuMonitor::distinct()->pluck('pon_port', 'pon_port')->toArray()),
            ])
            ->defaultSort('signal_status', 'desc')
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                Action::make('refresh')
                    ->label('🔄 Refresh Now')
                    ->color('info')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        try {
                            Artisan::call('olt:poll');
                            Notification::make()
                                ->title('✅ ONU data refresh হয়েছে!')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('❌ Refresh failed: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOnuMonitors::route('/'),
        ];
    }
}
