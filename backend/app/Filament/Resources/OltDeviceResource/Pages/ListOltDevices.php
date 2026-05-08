<?php
namespace App\Filament\Resources\OltDeviceResource\Pages;
use App\Filament\Resources\OltDeviceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListOltDevices extends ListRecords
{
    protected static string $resource = OltDeviceResource::class;
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
