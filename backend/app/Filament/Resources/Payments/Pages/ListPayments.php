<?php
namespace App\Filament\Resources\Payments\Pages;
use App\Filament\Resources\Payments\PaymentResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Action::make('dashboard')->label('Dashboard')->icon('heroicon-o-home')->color('gray')->url('/admin'),
            CreateAction::make()->label('New Payment')->icon('heroicon-o-plus'),
        ];
    }
}
