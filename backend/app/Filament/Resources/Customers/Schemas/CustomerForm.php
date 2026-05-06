<?php
namespace App\Filament\Resources\Customers\Schemas;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\Package;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make("name")->required(),
            TextInput::make("phone")->tel()->required(),
            TextInput::make("email")->label("Email address")->email(),
            Textarea::make("address")->columnSpanFull(),
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
        ]);
    }
}
