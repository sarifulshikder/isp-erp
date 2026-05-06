<?php

namespace App\Filament\Resources\MikrotikDevices\Pages;

use App\Filament\Resources\MikrotikDevices\MikrotikDeviceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMikrotikDevices extends ListRecords
{
    protected static string $resource = MikrotikDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
