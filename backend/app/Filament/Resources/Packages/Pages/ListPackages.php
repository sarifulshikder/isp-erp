<?php
namespace App\Filament\Resources\Packages\Pages;
use App\Filament\Resources\Packages\PackageResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
class ListPackages extends ListRecords
{
    protected static string $resource = PackageResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Action::make('dashboard')->label('Dashboard')->icon('heroicon-o-home')->color('gray')->url('/admin'),
            CreateAction::make()->label('New Package')->icon('heroicon-o-plus'),
        ];
    }
}
