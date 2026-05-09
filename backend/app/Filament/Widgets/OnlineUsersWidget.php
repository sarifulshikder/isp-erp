<?php
namespace App\Filament\Widgets;

use App\Models\MikrotikDevice;
use App\Services\MikrotikService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;

class OnlineUsersWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = '🟢 Online Users (Live)';
    protected static ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\Customer::query()
                    ->where('status', 'active')
                    ->whereIn('username', $this->getOnlineUsernames())
            )
            ->columns([
                TextColumn::make('name')->label('Customer')->searchable(),
                TextColumn::make('username')->label('Username')->copyable(),
                TextColumn::make('phone')->label('Phone'),
                TextColumn::make('package.name')->label('Package')->badge()->color('success'),
                TextColumn::make('expire_date')->label('Expire')->date()
                    ->color(fn ($record) => $record->expire_date < now() ? 'danger' : 'success'),
            ])
            ->emptyStateHeading('No online users')
            ->emptyStateIcon('heroicon-o-signal-slash')
            ->striped();
    }

    private function getOnlineUsernames(): array
    {
        try {
            $usernames = [];
            $devices = MikrotikDevice::where('status', 'active')->get();
            $mikrotik = new MikrotikService();

            foreach ($devices as $device) {
                if ($mikrotik->connect($device)) {
                    $users = $mikrotik->getOnlineUsers();
                    foreach ($users as $user) {
                        if (isset($user['name'])) {
                            $usernames[] = $user['name'];
                        }
                    }
                }
            }
            return array_unique($usernames);
        } catch (\Exception $e) {
            return [];
        }
    }
}
