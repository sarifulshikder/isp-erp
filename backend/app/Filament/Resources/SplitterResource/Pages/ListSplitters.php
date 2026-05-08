<?php
namespace App\Filament\Resources\SplitterResource\Pages;
use App\Filament\Resources\SplitterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListSplitters extends ListRecords
{
    protected static string $resource = SplitterResource::class;
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
