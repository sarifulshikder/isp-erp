<?php
namespace App\Filament\Resources\Invoices\Pages;
use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')->label('Back')->icon('heroicon-o-arrow-left')->color('gray')->url('/admin/invoices'),
            DeleteAction::make()->label('Delete')->icon('heroicon-o-trash'),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return '/admin/invoices';
    }
}
