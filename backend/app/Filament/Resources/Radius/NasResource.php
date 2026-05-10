<?php
namespace App\Filament\Resources\Radius;
use App\Filament\Resources\Radius\NasResource\Pages;
use App\Models\Radius\Nas;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NasResource extends Resource
{
    protected static ?string $model = Nas::class;
    protected static ?int $navigationSort = 4;

    public static function getNavigationIcon(): string { return 'heroicon-o-server-stack'; }
    public static function getNavigationLabel(): string { return 'NAS Devices'; }
    public static function getNavigationGroup(): string { return 'FreeRADIUS'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('NAS Device')->schema([
                TextInput::make('nasname')->required()->label('IP Address / Hostname'),
                TextInput::make('shortname')->label('Short Name'),
                Select::make('type')->options([
                    'other' => 'Other',
                    'cisco' => 'Cisco',
                    'computone' => 'Computone',
                    'livingston' => 'Livingston',
                    'max40xx' => 'Max40xx',
                    'multitech' => 'Multitech',
                    'netserver' => 'Netserver',
                    'pathras' => 'Pathras',
                    'patton' => 'Patton',
                    'portslave' => 'Portslave',
                    'tc' => 'TC',
                    'usrhiper' => 'USR Hiper',
                ])->default('other'),
                TextInput::make('secret')->required()->label('Shared Secret'),
                TextInput::make('description')->label('Description'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nasname')->label('IP/Hostname')->searchable()->sortable(),
                TextColumn::make('shortname')->label('Short Name'),
                TextColumn::make('type')->badge(),
                TextColumn::make('secret')->label('Secret')
                    ->formatStateUsing(fn() => '••••••••'),
                TextColumn::make('description')->label('Description'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNasDevices::route('/'),
            'create' => Pages\CreateNasDevice::route('/create'),
            'edit'   => Pages\EditNasDevice::route('/{record}/edit'),
        ];
    }
}
