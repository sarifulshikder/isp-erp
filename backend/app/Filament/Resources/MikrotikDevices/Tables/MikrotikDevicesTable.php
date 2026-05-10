<?php
namespace App\Filament\Resources\MikrotikDevices\Tables;
use App\Models\MikrotikDevice;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Zone;
use App\Services\MikrotikService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
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
                            return new HtmlString('<p style="color:red;padding:16px;">❌ Connection Failed</p>');
                        }
                        $users = $service->getOnlineUsers();
                        if (empty($users)) {
                            return new HtmlString('<p style="padding:16px;color:#6b7280;">কোনো online user নেই।</p>');
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
                        return new HtmlString($html);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('বন্ধ করুন'),
                Action::make('export')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->modalHeading(fn(MikrotikDevice $record) => '📤 Export to ' . $record->name)
                    ->modalDescription('Filter করুন — কোনো filter না দিলে সব eligible customers export হবে।')
                    ->form([
                        Select::make('zone_ids')
                            ->label('Zone (একাধিক বাছতে পারেন)')
                            ->options(Zone::where('is_active', true)->pluck('name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->nullable()
                            ->live(),
                        Select::make('package_ids')
                            ->label('Package (একাধিক বাছতে পারেন)')
                            ->options(Package::where('status', 'active')->pluck('name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->nullable()
                            ->live(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active'    => 'Active',
                                'inactive'  => 'Inactive',
                                'suspended' => 'Suspended',
                            ])
                            ->multiple()
                            ->nullable()
                            ->live(),
                        Select::make('connection_type')
                            ->label('Connection Type')
                            ->options([
                                'pppoe'     => 'PPPoE',
                                'hotspot'   => 'Hotspot',
                                'static_ip' => 'Static IP',
                            ])
                            ->multiple()
                            ->nullable()
                            ->live(),
                        Select::make('mikrotik_mode')
                            ->label('MikroTik Mode')
                            ->options([
                                'specific' => 'Specific Router',
                                'all'      => 'All Routers',
                            ])
                            ->multiple()
                            ->nullable()
                            ->live(),
                        Placeholder::make('preview')
                            ->label('📊 Export Preview')
                            ->content(function ($get, MikrotikDevice $record) {
                                $query = Customer::where(function ($q) use ($record) {
                                    $q->where('mikrotik_mode', 'all')
                                      ->orWhere(function ($q2) use ($record) {
                                          $q2->where('mikrotik_mode', 'specific')
                                             ->where('mikrotik_device_id', $record->id);
                                      });
                                });
                                if (!empty($get('zone_ids'))) {
                                    $query->whereIn('zone_id', $get('zone_ids'));
                                }
                                if (!empty($get('package_ids'))) {
                                    $query->whereIn('package_id', $get('package_ids'));
                                }
                                if (!empty($get('status'))) {
                                    $query->whereIn('status', $get('status'));
                                }
                                if (!empty($get('connection_type'))) {
                                    $query->whereIn('connection_type', $get('connection_type'));
                                }
                                if (!empty($get('mikrotik_mode'))) {
                                    $query->whereIn('mikrotik_mode', $get('mikrotik_mode'));
                                }
                                $count = $query->count();
                                $color = $count > 0 ? '#16a34a' : '#dc2626';
                                return new HtmlString(
                                    '<div style="padding:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;">'
                                    . '<span style="font-size:16px;font-weight:700;color:' . $color . ';">'
                                    . $count . ' জন customer</span>'
                                    . ' এই filter এ export হবে।'
                                    . '</div>'
                                );
                            }),
                    ])
                    ->action(function (MikrotikDevice $record, array $data) {
                        $query = Customer::where(function ($q) use ($record) {
                            $q->where('mikrotik_mode', 'all')
                              ->orWhere(function ($q2) use ($record) {
                                  $q2->where('mikrotik_mode', 'specific')
                                     ->where('mikrotik_device_id', $record->id);
                              });
                        });
                        if (!empty($data['zone_ids'])) {
                            $query->whereIn('zone_id', $data['zone_ids']);
                        }
                        if (!empty($data['package_ids'])) {
                            $query->whereIn('package_id', $data['package_ids']);
                        }
                        if (!empty($data['status'])) {
                            $query->whereIn('status', $data['status']);
                        }
                        if (!empty($data['connection_type'])) {
                            $query->whereIn('connection_type', $data['connection_type']);
                        }
                        if (!empty($data['mikrotik_mode'])) {
                            $query->whereIn('mikrotik_mode', $data['mikrotik_mode']);
                        }
                        $customers = $query->get();
                        $service = new MikrotikService();
                        if (!$service->connect($record)) {
                            Notification::make()->title('❌ Connection Failed')->danger()->send();
                            return;
                        }
                        // Existing users থেকে username list
                        $existingQuery = new \RouterOS\Query('/ppp/secret/print');
                        try {
                            $existing = $service->getClient()->query($existingQuery)->read();
                            $existingUsernames = array_map(fn($s) => strtolower($s['name'] ?? ''), $existing);
                        } catch (\Exception $e) {
                            $existingUsernames = [];
                        }
                        $results = ['exported' => 0, 'skipped' => 0, 'failed' => 0];
                        foreach ($customers as $customer) {
                            if (in_array(strtolower($customer->username), $existingUsernames)) {
                                $results['skipped']++;
                                continue;
                            }
                            $profile = $customer->mikrotik_profile ?? 'default';
                            $success = $customer->connection_type === 'hotspot'
                                ? $service->addHotspotUser($customer->username, $customer->password ?? '', $profile)
                                : $service->addPPPoEUser($customer->username, $customer->password ?? '', $profile);
                            $success ? $results['exported']++ : $results['failed']++;
                        }
                        Notification::make()
                            ->title('📤 Export সম্পন্ন')
                            ->body("Exported: {$results['exported']} | Skipped: {$results['skipped']} | Failed: {$results['failed']}")
                            ->success()->send();
                    }),
                Action::make('import')
                    ->label('Import')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading(fn(MikrotikDevice $record) => '📥 Import from ' . $record->name)
                    ->modalDescription('এই router থেকে সব PPPoE user আনা হবে। যারা software এ নেই তাদের Review Queue তে রাখা হবে।')
                    ->action(function (MikrotikDevice $record) {
                        $service = new MikrotikService();
                        $results = $service->importFromDevice($record);
                        if (isset($results['error'])) {
                            Notification::make()->title('❌ ' . $results['error'])->danger()->send();
                            return;
                        }
                        Notification::make()
                            ->title('📥 Import সম্পন্ন')
                            ->body("Review Queue এ গেছে: {$results['queued']} | Skip হয়েছে: {$results['skipped']}")
                            ->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
