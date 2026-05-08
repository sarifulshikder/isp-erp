<?php
namespace App\Filament\Resources\Customers\Schemas;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\Package;
use App\Models\Inventory;
use App\Models\Zone;
class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic Information')->schema([
                TextInput::make("name")->required(),
                TextInput::make("phone")->tel()->required(),
                TextInput::make("email")->label("Email address")->email(),
                Textarea::make("address")->columnSpanFull(),
                Select::make("zone_id")
                    ->label("Zone / এলাকা")
                    ->options(Zone::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
            ]),
            Section::make('Connection Details')->schema([
                TextInput::make("username")->required(),
                TextInput::make("password")->password()->required(),
                Select::make("package_id")
                    ->label("Package")
                    ->options(Package::where("status", "active")->pluck("name", "id"))
                    ->required()
                    ->searchable(),
                DatePicker::make("connection_date")->required(),
                DatePicker::make("expire_date")->required(),
                Select::make("status")
                    ->options(["active" => "Active", "inactive" => "Inactive", "suspended" => "Suspended"])
                    ->default("active")->required(),
                TextInput::make("mikrotik_profile"),
                TextInput::make("balance")->required()->numeric()->default(0),
            ]),
            Section::make('Device Assignment')->schema([
                Select::make('assigned_inventory_id')
                    ->label('ONU / Device Assign করুন')
                    ->options(
                        Inventory::where('status', 'available')
                            ->with('category')
                            ->get()
                            ->mapWithKeys(fn ($item) => [
                                $item->id => ($item->category->name ?? '') . ' — ' . $item->name .
                                    ($item->serial_number ? ' (' . $item->serial_number . ')' : '')
                            ])
                    )
                    ->searchable()
                    ->nullable()
                    ->helperText('শুধুমাত্র Available devices দেখাচ্ছে')
                    ->dehydrated(false),
            ]),
            Section::make('Location (Map)')->schema([
                TextInput::make('latitude')
                    ->numeric()
                    ->label('Latitude')
                    ->placeholder('23.8103')
                    ->helperText('Google Maps থেকে latitude copy করুন'),
                TextInput::make('longitude')
                    ->numeric()
                    ->label('Longitude')
                    ->placeholder('90.4125')
                    ->helperText('Google Maps থেকে longitude copy করুন'),
            ])->columns(2),
        ]);
    }
}
