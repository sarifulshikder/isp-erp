<?php
namespace App\Filament\Resources\FiberRouteResource\Pages;
use App\Filament\Resources\FiberRouteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListFiberRoutes extends ListRecords
{
    protected static string $resource = FiberRouteResource::class;
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
