<?php
namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringCustomersWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 1;
    protected static ?string $heading = 'এই সপ্তাহে Expire হবে';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Customer::query()
                    ->where('status', 'active')
                    ->whereDate('expire_date', '<=', now()->addDays(7))
                    ->whereDate('expire_date', '>=', today())
                    ->orderBy('expire_date')
            )
            ->columns([
                TextColumn::make('name')->label('Customer')->searchable(),
                TextColumn::make('phone')->label('Phone'),
                TextColumn::make('package.name')->label('Package'),
                TextColumn::make('expire_date')->label('Expire')->date()
                    ->color('danger'),
            ]);
    }
}
