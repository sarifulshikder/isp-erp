<?php
namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('dashboard')
                ->label('Dashboard')
                ->icon('heroicon-o-home')
                ->color('gray')
                ->url('/admin'),
            CreateAction::make()
                ->label('New Customer')
                ->icon('heroicon-o-plus'),
        ];
    }
}
