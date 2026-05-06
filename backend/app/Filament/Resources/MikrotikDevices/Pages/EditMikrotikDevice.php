<?php

namespace App\Filament\Resources\MikrotikDevices\Pages;

use App\Filament\Resources\MikrotikDevices\MikrotikDeviceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMikrotikDevice extends EditRecord
{
    protected static string $resource = MikrotikDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
