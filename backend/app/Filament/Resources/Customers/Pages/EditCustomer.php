<?php
namespace App\Filament\Resources\Customers\Pages;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')->label('Back')->icon('heroicon-o-arrow-left')->color('gray')->url('/admin/customers'),
            DeleteAction::make()->label('Delete')->icon('heroicon-o-trash'),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return '/admin/customers';
    }
}
