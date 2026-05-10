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
use App\Models\MikrotikDevice;
class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic Information')->schema([
                TextInput::make('name')->required(),
                TextInput::make('phone')->tel()->required(),
                TextInput::make('email')->label('Email address')->email(),
                Textarea::make('address')->columnSpanFull(),
                Select::make('zone_id')
                    ->label('Zone / এলাকা')
                    ->options(Zone::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
            ]),
            Section::make('Connection Details')->schema([
                Select::make('connection_type')
                    ->label('Connection Type')
                    ->options([
                        'pppoe'     => '🔌 PPPoE',
                        'hotspot'   => '📶 Hotspot (MAC-based)',
                        'static_ip' => '🖥️ Static IP',
                    ])
                    ->default('pppoe')
                    ->required()
                    ->live(),
                TextInput::make('username')
                    ->required()
                    ->label(fn($get) => match($get('connection_type')) {
                        'hotspot'   => 'MAC Address (Username)',
                        'static_ip' => 'IP Address (Username)',
                        default     => 'PPPoE Username',
                    }),
                TextInput::make('password')
                    ->password()
                    ->nullable()
                    ->label('Password')
                    ->helperText('Hotspot/Static IP এর জন্য খালি রাখতে পারেন। Edit এ খালি রাখলে পুরনো password থাকবে।')
                    ->dehydrateStateUsing(fn($state, $record) =>
                        filled($state) ? $state : ($record?->password ?? '')
                    ),
                TextInput::make('mac_address')
                    ->label('MAC Address')
                    ->nullable()
                    ->placeholder('AA:BB:CC:DD:EE:FF')
                    ->visible(fn($get) => $get('connection_type') === 'hotspot')
                    ->helperText('Hotspot customer এর TV/Device MAC address'),
                Select::make('package_id')
                    ->label('Package')
                    ->options(Package::where('status', 'active')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                DatePicker::make('connection_date')->required(),
                DatePicker::make('expire_date')->required(),
                Select::make('status')
                    ->options([
                        'active'    => 'Active',
                        'inactive'  => 'Inactive',
                        'suspended' => 'Suspended',
                    ])
                    ->default('active')->required(),
                TextInput::make('mikrotik_profile'),
                TextInput::make('balance')->required()->numeric()->default(0),
            ]),
            Section::make('MikroTik Routing')->schema([
                Select::make('mikrotik_mode')
                    ->label('MikroTik Connection Mode')
                    ->options([
                        'freeradius_only' => '🔒 FreeRADIUS Only — কোনো router এ add হবে না',
                        'specific'        => '🎯 Specific Router — নির্দিষ্ট একটা router',
                        'all'             => '📡 All Routers — সব active router এ add হবে',
                    ])
                    ->default('freeradius_only')
                    ->required()
                    ->live()
                    ->helperText(fn($state) => match($state) {
                        'freeradius_only' => 'যেকোনো router থেকে RADIUS দিয়ে authenticate হবে।',
                        'specific'        => 'PPPoE user শুধু নির্বাচিত router এ add হবে।',
                        'all'             => 'সব active router এ add হবে। নতুন router যোগ করলে auto push হবে।',
                        default           => '',
                    }),
                Select::make('mikrotik_device_id')
                    ->label('Router বাছুন')
                    ->options(MikrotikDevice::where('status', 'active')->pluck('name', 'id'))
                    ->visible(fn($get) => $get('mikrotik_mode') === 'specific')
                    ->required(fn($get) => $get('mikrotik_mode') === 'specific')
                    ->searchable()
                    ->placeholder('একটি MikroTik router বাছুন'),
            ]),
            Section::make('Device Assignment')->schema([
                Select::make('assigned_inventory_id')
                    ->label('ONU / Device Assign করুন')
                    ->options(
                        Inventory::where('status', 'available')
                            ->with('category')
                            ->get()
                            ->mapWithKeys(fn($item) => [
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
