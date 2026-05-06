<?php
namespace App\Filament\Resources\Invoices\Schemas;
use App\Models\Customer;
use App\Models\Package;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('customer_id')
                ->label('Customer')
                ->options(Customer::pluck('name', 'id'))
                ->required()
                ->searchable(),
            Select::make('package_id')
                ->label('Package')
                ->options(Package::where('status', 'active')->pluck('name', 'id'))
                ->required()
                ->searchable(),
            TextInput::make('invoice_no')->required(),
            TextInput::make('amount')->required()->numeric(),
            TextInput::make('discount')->numeric()->default(0),
            TextInput::make('total')->required()->numeric(),
            DatePicker::make('issue_date')->required()->default(now()),
            DatePicker::make('due_date')->required(),
            DatePicker::make('paid_date'),
            Select::make('status')
                ->options(['unpaid' => 'Unpaid', 'paid' => 'Paid', 'partial' => 'Partial', 'cancelled' => 'Cancelled'])
                ->default('unpaid')->required(),
            Textarea::make('note')->nullable(),
        ]);
    }
}
