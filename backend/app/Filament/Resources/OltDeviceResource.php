<?php
namespace App\Filament\Resources;
use App\Filament\Resources\OltDeviceResource\Pages;
use App\Models\OltDevice;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OltDeviceResource extends Resource
{
    protected static ?string $model = OltDevice::class;
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string { return 'heroicon-o-server'; }
    public static function getNavigationLabel(): string { return 'OLT Devices'; }
    public static function getNavigationGroup(): string { return 'OLT Management'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('OLT Information')->schema([
                TextInput::make('name')->required()->label('Device Name'),
                Select::make('brand')->options([
                    'vsol' => 'VSOL', 'bdcom' => 'BDCOM',
                    'huawei' => 'Huawei', 'zte' => 'ZTE',
                ])->required(),
                TextInput::make('ip')->required()->label('IP Address'),
                TextInput::make('port')->numeric()->default(80)->required(),
                TextInput::make('username')->required(),
                TextInput::make('password')->password()->required(),
                TextInput::make('snmp_port')->numeric()->default(161)->label('SNMP Port'),
                TextInput::make('snmp_community')->default('public')->label('SNMP Community'),
                Select::make('status')->options([
                    'active' => 'Active', 'inactive' => 'Inactive',
                ])->default('active')->required(),
                Section::make('Location (Map)')->schema([
                TextInput::make('latitude')->numeric()->label('Latitude')->placeholder('23.8103'),
                TextInput::make('longitude')->numeric()->label('Longitude')->placeholder('90.4125'),
            ])->columns(2),
        ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('brand')->badge(),
                TextColumn::make('ip')->label('IP Address'),
                TextColumn::make('port'),
                TextColumn::make('onuMonitors_count')->counts('onuMonitors')->label('ONU Count'),
                TextColumn::make('status')->badge()
                    ->color(fn($state) => $state === 'active' ? 'success' : 'danger'),
                TextColumn::make('last_polled_at')->dateTime()->label('Last Polled'),
            ])
            ->actions([
                EditAction::make(),
                Action::make('poll')
                    ->label('Poll Now')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (OltDevice $record) {
                        $vsol = new \App\Services\VsolService($record);
                        $count = $vsol->pollAndSave($record);
                        \Filament\Notifications\Notification::make()
                            ->title("Polled {$count} ONUs")
                            ->success()->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOltDevices::route('/'),
            'create' => Pages\CreateOltDevice::route('/create'),
            'edit'   => Pages\EditOltDevice::route('/{record}/edit'),
        ];
    }
}
