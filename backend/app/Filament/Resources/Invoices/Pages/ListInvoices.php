<?php
namespace App\Filament\Resources\Invoices\Pages;
use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Action::make('dashboard')->label('Dashboard')->icon('heroicon-o-home')->color('gray')->url('/admin'),
            CreateAction::make()->label('New Invoice')->icon('heroicon-o-plus'),
        ];
    }
}
