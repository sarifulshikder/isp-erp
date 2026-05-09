<?php
namespace App\Filament\Resources\Invoices\Tables;
use App\Models\Payment;
use App\Services\SmsService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_no')->searchable()->copyable()->weight('bold'),
                TextColumn::make('customer.name')->label('Customer')->searchable()->sortable(),
                TextColumn::make('package.name')->label('Package')->badge()->color('info'),
                TextColumn::make('total')->label('Total')->numeric()->prefix('BDT ')->sortable(),
                TextColumn::make('issue_date')->label('Issue')->date()->sortable(),
                TextColumn::make('due_date')->label('Due')->date()->sortable()
                    ->color(fn ($record) => $record->due_date < now() && $record->status === 'unpaid' ? 'danger' : 'success'),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                        'partial' => 'warning',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'paid' => 'Paid',
                        'partial' => 'Partial',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['unpaid', 'partial']))
                    ->requiresConfirmation()
                    ->form([
                        Select::make('method')
                            ->label('Payment Method')
                            ->options([
                                'cash' => 'Cash',
                                'bkash' => 'bKash',
                                'nagad' => 'Nagad',
                                'rocket' => 'Rocket',
                                'bank' => 'Bank',
                            ])
                            ->default('cash')
                            ->required(),
                        TextInput::make('transaction_id')
                            ->label('Transaction ID')
                            ->nullable(),
                    ])
                    ->action(function ($record, array $data) {
                        Payment::create([
                            'invoice_id' => $record->id,
                            'customer_id' => $record->customer_id,
                            'amount' => $record->total,
                            'method' => $data['method'],
                            'transaction_id' => $data['transaction_id'] ?? null,
                            'paid_at' => now(),
                        ]);
                        $record->update([
                            'status' => 'paid',
                            'paid_date' => now()->toDateString(),
                        ]);
                        // Send SMS
                        if ($record->customer) {
                            $sms = new SmsService();
                            $sms->paymentReceived($record->customer->phone, [
                                'name' => $record->customer->name,
                                'amount' => $record->total,
                                'invoice_no' => $record->invoice_no,
                            ]);
                        }
                        Notification::make()
                            ->title('Invoice marked as paid!')
                            ->success()->send();
                    }),

                Action::make('send_reminder')
                    ->label('Reminder')
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'unpaid')
                    ->action(function ($record) {
                        if ($record->customer) {
                            $sms = new SmsService();
                            $sms->sendFromTemplate('sms_expiry_reminder', $record->customer->phone, [
                                'name' => $record->customer->name,
                                'amount' => $record->total,
                                'invoice_no' => $record->invoice_no,
                                'expire_date' => $record->due_date,
                            ]);
                            Notification::make()->title('Reminder sent to ' . $record->customer->name)->success()->send();
                        }
                    }),

                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn ($record) => url('/api/invoices/' . $record->id . '/pdf'))
                    ->openUrlInNewTab(),

                EditAction::make()->label('Edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
