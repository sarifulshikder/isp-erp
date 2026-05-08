<?php
namespace App\Filament\Resources\OltDeviceResource\Pages;
use App\Filament\Resources\OltDeviceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditOltDevice extends EditRecord
{
    protected static string $resource = OltDeviceResource::class;
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
