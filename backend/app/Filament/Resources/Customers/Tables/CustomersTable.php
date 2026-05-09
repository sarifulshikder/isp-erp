<?php
namespace App\Filament\Resources\Customers\Tables;
use App\Models\Invoice;
use App\Services\MikrotikService;
use App\Services\SmsService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                TextColumn::make('phone')->searchable()->copyable()->copyMessage('Phone copied!'),
                TextColumn::make('package.name')->label('Package')->sortable()->badge()->color('info'),
                TextColumn::make('zone.name')->label('Zone')->sortable(),
                TextColumn::make('expire_date')->label('Expire')->date()->sortable()
                    ->color(fn ($record) => $record->expire_date < now() ? 'danger' : ($record->expire_date <= now()->addDays(7) ? 'warning' : 'success')),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match($state) {
                        'active' => 'success',
                        'suspended' => 'warning',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('balance')->label('Balance')->numeric()->prefix('BDT '),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),
                SelectFilter::make('package')->relationship('package', 'name'),
                SelectFilter::make('zone')->relationship('zone', 'name'),
            ])
            ->recordActions([
                Action::make('renew')
                    ->label('Renew')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->form([
                        Select::make('days')
                            ->label('Validity')
                            ->options([
                                '30' => '30 Days',
                                '60' => '60 Days',
                                '90' => '90 Days',
                            ])
                            ->default('30')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $days = (int) $data['days'];
                        $newExpiry = $record->expire_date < now()
                            ? now()->addDays($days)
                            : \Carbon\Carbon::parse($record->expire_date)->addDays($days);
                        $record->update([
                            'expire_date' => $newExpiry,
                            'status' => 'active',
                        ]);
                        // Auto create invoice
                        if ($record->package) {
                            Invoice::create([
                                'invoice_no' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
                                'customer_id' => $record->id,
                                'package_id' => $record->package_id,
                                'amount' => $record->package->price,
                                'discount' => 0,
                                'total' => $record->package->price,
                                'issue_date' => now()->toDateString(),
                                'due_date' => $newExpiry->toDateString(),
                                'status' => 'unpaid',
                            ]);
                        }
                        Notification::make()
                            ->title('Customer renewed until ' . $newExpiry->format('d M Y'))
                            ->success()->send();
                    }),

                Action::make('sms')
                    ->label('SMS')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->color('info')
                    ->form([
                        TextInput::make('message')
                            ->label('Message')
                            ->required()
                            ->default(fn ($record) => 'Dear ' . $record->name . ', ')
                            ->maxLength(160),
                    ])
                    ->action(function ($record, array $data) {
                        $sms = new SmsService();
                        $result = $sms->send($record->phone, $data['message']);
                        if ($result) {
                            Notification::make()->title('SMS sent to ' . $record->name)->success()->send();
                        } else {
                            Notification::make()->title('SMS failed!')->danger()->send();
                        }
                    }),

                Action::make('invoices')
                    ->label('Invoices')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->url(fn ($record) => '/admin/invoices?tableFilters[customer_id][value]=' . $record->id)
                    ->openUrlInNewTab(),

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
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'suspended']);
                        Notification::make()->title('Customer suspended!')->warning()->send();
                    }),

                EditAction::make()->label('Edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('expire_date', 'asc')
            ->striped();
    }
}
