<?php
namespace App\Filament\Resources\Radius\RadiusUserResource\Pages;
use App\Filament\Resources\Radius\RadiusUserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListRadiusUsers extends ListRecords
{
    protected static string $resource = RadiusUserResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
