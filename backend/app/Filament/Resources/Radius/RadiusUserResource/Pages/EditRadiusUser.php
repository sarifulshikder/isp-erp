<?php
namespace App\Filament\Resources\Radius\RadiusUserResource\Pages;
use App\Filament\Resources\Radius\RadiusUserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditRadiusUser extends EditRecord
{
    protected static string $resource = RadiusUserResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
