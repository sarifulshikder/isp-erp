<?php
namespace App\Filament\Resources\Customers\Tables;
use App\Models\MikrotikDevice;
use App\Services\MikrotikService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('package.name')->label('Package')->sortable(),
                TextColumn::make('zone.name')->label('Zone')->sortable(),
                TextColumn::make('expire_date')->date()->sortable()
                    ->color(fn ($record) => $record->expire_date < now() ? 'danger' : 'success'),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match($state) {
                        'active' => 'success',
                        'suspended' => 'warning',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('balance')->numeric()->prefix('BDT '),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->recordActions([
                Action::make('enable')
                    ->label('Enable')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'active')
                    ->action(function ($record) {
                        $record->update(['status' => 'active']);
                        Notification::make()->title('Customer enabled!')->success()->send();
                    }),
                Action::make('disable')
                    ->label('Disable')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'active')
                    ->action(function ($record) {
                        $record->update(['status' => 'suspended']);
                        Notification::make()->title('Customer suspended!')->warning()->send();
                    }),
                Action::make('pdf')
                    ->label('')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->url(fn ($record) => url('/api/invoices?customer_id=' . $record->id))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
