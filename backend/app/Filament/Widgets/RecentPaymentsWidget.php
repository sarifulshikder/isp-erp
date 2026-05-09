<?php
namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 1;
    protected static ?string $heading = 'সাম্প্রতিক Payments';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()
                    ->latest('paid_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('customer.name')->label('Customer'),
                TextColumn::make('amount')->label('Amount')->prefix('BDT '),
                TextColumn::make('method')->label('Method')->badge()
                    ->color(fn ($state) => match($state) {
                        'bkash' => 'pink',
                        'nagad' => 'orange',
                        'cash' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('paid_at')->label('Date')->dateTime('d M, h:i A'),
            ]);
    }
}
