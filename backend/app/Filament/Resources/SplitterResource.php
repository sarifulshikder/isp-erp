<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SplitterResource\Pages;
use App\Models\Splitter;
use App\Models\OltDevice;
use App\Models\Zone;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;

class SplitterResource extends Resource
{
    protected static ?string $model = Splitter::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-arrow-path-rounded-square';
    }

    public static function getNavigationLabel(): string
    {
        return 'Splitters';
    }

    public static function getNavigationGroup(): string
    {
        return 'Network';
    }

    public static function getNavigationSort(): int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Splitter Information')->schema([
                TextInput::make('name')->required()->label('Splitter Name')->placeholder('Splitter-01'),
                Select::make('type')->options([
                    '1:2'  => '1:2',
                    '1:4'  => '1:4',
                    '1:8'  => '1:8',
                    '1:16' => '1:16',
                    '1:32' => '1:32',
                    '1:64' => '1:64',
                ])->default('1:8')->required()->label('Split Ratio'),
                Select::make('olt_device_id')
                    ->label('OLT Device')
                    ->options(OltDevice::pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Select::make('zone_id')
                    ->label('Zone / এলাকা')
                    ->options(Zone::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Select::make('status')->options([
                    'active'   => 'Active',
                    'inactive' => 'Inactive',
                    'faulty'   => 'Faulty',
                ])->default('active')->required(),
                TextInput::make('address')->label('Address')->nullable(),
                Textarea::make('note')->label('Note')->nullable()->columnSpanFull(),
            ])->columns(2),

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('type')->label('Split Ratio')->badge(),
                TextColumn::make('oltDevice.name')->label('OLT')->default('N/A'),
                TextColumn::make('zone.name')->label('Zone')->default('N/A'),
                TextColumn::make('latitude')->label('Lat')->default('—'),
                TextColumn::make('longitude')->label('Lng')->default('—'),
                TextColumn::make('status')->badge()
                    ->color(fn($state) => match($state) {
                        'active'   => 'success',
                        'inactive' => 'warning',
                        'faulty'   => 'danger',
                    }),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSplitters::route('/'),
            'create' => Pages\CreateSplitter::route('/create'),
            'edit'   => Pages\EditSplitter::route('/{record}/edit'),
        ];
    }
}
