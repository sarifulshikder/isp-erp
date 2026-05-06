<?php
namespace App\Filament\Resources\Payments\Schemas;
use App\Models\Customer;
use App\Models\Invoice;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('customer_id')
                ->label('Customer')
                ->options(Customer::pluck('name', 'id'))
                ->required()
                ->searchable(),
            Select::make('invoice_id')
                ->label('Invoice')
                ->options(Invoice::where('status', 'unpaid')->pluck('invoice_no', 'id'))
                ->required()
                ->searchable(),
            TextInput::make('amount')->required()->numeric(),
            Select::make('method')
                ->options(['cash' => 'Cash', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'bank' => 'Bank'])
                ->required()->default('cash'),
            TextInput::make('transaction_id')->nullable(),
            DateTimePicker::make('paid_at')->required()->default(now()),
            TextInput::make('note')->nullable(),
        ]);
    }
}
