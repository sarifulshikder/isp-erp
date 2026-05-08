<?php
namespace App\Filament\Resources\SplitterResource\Pages;
use App\Filament\Resources\SplitterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditSplitter extends EditRecord
{
    protected static string $resource = SplitterResource::class;
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
