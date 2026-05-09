<?php
namespace App\Filament\Resources\Packages\Pages;
use App\Filament\Resources\Packages\PackageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
class EditPackage extends EditRecord
{
    protected static string $resource = PackageResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')->label('Back')->icon('heroicon-o-arrow-left')->color('gray')->url('/admin/packages'),
            DeleteAction::make()->label('Delete')->icon('heroicon-o-trash'),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return '/admin/packages';
    }
}
