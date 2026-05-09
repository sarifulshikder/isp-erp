<?php
namespace App\Filament\Resources\Payments\Pages;
use App\Filament\Resources\Payments\PaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')->label('Back')->icon('heroicon-o-arrow-left')->color('gray')->url('/admin/payments'),
            DeleteAction::make()->label('Delete')->icon('heroicon-o-trash'),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return '/admin/payments';
    }
}
