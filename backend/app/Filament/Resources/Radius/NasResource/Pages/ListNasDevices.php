<?php
namespace App\Filament\Resources\Radius\NasResource\Pages;
use App\Filament\Resources\Radius\NasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListNasDevices extends ListRecords
{
    protected static string $resource = NasResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
