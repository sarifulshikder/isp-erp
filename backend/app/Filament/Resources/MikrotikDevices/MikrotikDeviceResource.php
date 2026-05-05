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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
