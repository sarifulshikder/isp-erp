<?php

namespace App\Filament\Resources\MikrotikDevices;

use App\Filament\Resources\MikrotikDevices\Pages\CreateMikrotikDevice;
use App\Filament\Resources\MikrotikDevices\Pages\EditMikrotikDevice;
use App\Filament\Resources\MikrotikDevices\Pages\ListMikrotikDevices;
use App\Filament\Resources\MikrotikDevices\Schemas\MikrotikDeviceForm;
use App\Filament\Resources\MikrotikDevices\Tables\MikrotikDevicesTable;
use App\Models\MikrotikDevice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MikrotikDeviceResource extends Resource
{
    protected static ?string $model = MikrotikDevice::class;



    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return Heroicon::OutlinedServerStack;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Network';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getNavigationLabel(): string
    {
        return 'MikroTik Devices';
    }

    public static function form(Schema $schema): Schema
    {
        return MikrotikDeviceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MikrotikDevicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMikrotikDevices::route('/'),
            'create' => CreateMikrotikDevice::route('/create'),
            'edit' => EditMikrotikDevice::route('/{record}/edit'),
        ];
    }
}
