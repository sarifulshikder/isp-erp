<?php
namespace App\Filament\Resources\FiberRouteResource\Pages;
use App\Filament\Resources\FiberRouteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditFiberRoute extends EditRecord
{
    protected static string $resource = FiberRouteResource::class;
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
