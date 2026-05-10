<?php
namespace App\Filament\Resources\Radius\NasResource\Pages;
use App\Filament\Resources\Radius\NasResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditNasDevice extends EditRecord
{
    protected static string $resource = NasResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
