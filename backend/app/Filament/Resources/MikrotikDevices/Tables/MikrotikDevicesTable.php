<?php
namespace App\Filament\Resources\MikrotikDevices\Tables;
use App\Models\MikrotikDevice;
use App\Services\MikrotikService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class MikrotikDevicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('host')->searchable(),
                TextColumn::make('port')->numeric()->sortable(),
                TextColumn::make('username')->searchable(),
                TextColumn::make('status')->badge()
                    ->color(fn($state) => $state === 'active' ? 'success' : 'danger'),
                TextColumn::make('last_seen')->dateTime()->sortable()->label('Last Seen'),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('test')
                    ->label('Test')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function (MikrotikDevice $record) {
                        $service = new MikrotikService();
                        $connected = $service->connect($record);
                        if (!$connected) {
                            Notification::make()->title('❌ Connection Failed')->danger()->send();
                            return;
                        }
                        $result = $service->testConnection();
                        Notification::make()
                            ->title('✅ Connected: ' . ($result['identity'] ?? 'OK'))
                            ->success()->send();
                    }),

                Action::make('online_users')
                    ->label('Online Users')
                    ->icon('heroicon-o-users')
                    ->color('success')
                    ->modalHeading(fn(MikrotikDevice $record) => '🟢 Online Users — ' . $record->name)
                    ->modalContent(function (MikrotikDevice $record) {
                        $service = new MikrotikService();
                        $connected = $service->connect($record);
                        if (!$connected) {
                            return new \Illuminate\Support\HtmlString('<p style="color:red;padding:16px;">❌ Connection Failed</p>');
                        }
                        $users = $service->getOnlineUsers();
                        if (empty($users)) {
                            return new \Illuminate\Support\HtmlString('<p style="padding:16px;color:#6b7280;">কোনো online user নেই।</p>');
                        }
                        $html = '<div style="overflow-x:auto;padding:8px;">';
                        $html .= '<p style="margin-bottom:8px;font-weight:600;">মোট: ' . count($users) . ' জন online</p>';
                        $html .= '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
                        $html .= '<thead><tr style="background:#F3F4F6;">';
                        $html .= '<th style="padding:8px;text-align:left;border-bottom:1px solid #E5E7EB;">Username</th>';
                        $html .= '<th style="padding:8px;text-align:left;border-bottom:1px solid #E5E7EB;">Customer</th>';
                        $html .= '<th style="padding:8px;text-align:left;border-bottom:1px solid #E5E7EB;">Phone</th>';
                        $html .= '<th style="padding:8px;text-align:left;border-bottom:1px solid #E5E7EB;">Uptime</th>';
                        $html .= '<th style="padding:8px;text-align:left;border-bottom:1px solid #E5E7EB;">IP</th>';
                        $html .= '</tr></thead><tbody>';
                        foreach ($users as $user) {
                            $html .= '<tr style="border-bottom:1px solid #F3F4F6;">';
                            $html .= '<td style="padding:8px;font-weight:500;">' . htmlspecialchars($user['username']) . '</td>';
                            $html .= '<td style="padding:8px;">' . htmlspecialchars($user['customer_name']) . '</td>';
                            $html .= '<td style="padding:8px;">' . htmlspecialchars($user['phone']) . '</td>';
                            $html .= '<td style="padding:8px;">' . htmlspecialchars($user['uptime']) . '</td>';
                            $html .= '<td style="padding:8px;">' . htmlspecialchars($user['address']) . '</td>';
                            $html .= '</tr>';
                        }
                        $html .= '</tbody></table></div>';
                        return new \Illuminate\Support\HtmlString($html);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('বন্ধ করুন'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
