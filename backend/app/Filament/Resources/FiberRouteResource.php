<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FiberRouteResource\Pages;
use App\Models\FiberRoute;
use App\Models\OltDevice;
use App\Models\Splitter;
use App\Models\Customer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;

class FiberRouteResource extends Resource
{
    protected static ?string $model = FiberRoute::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-map-pin';
    }

    public static function getNavigationLabel(): string
    {
        return 'Fiber Routes';
    }

    public static function getNavigationGroup(): string
    {
        return 'Network';
    }

    public static function getNavigationSort(): int
    {
        return 3;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Route Information')->schema([
                TextInput::make('name')->required()->label('Route Name')->placeholder('Route-01'),
                Select::make('type')->options([
                    'olt_to_splitter'      => 'OLT → Splitter',
                    'splitter_to_customer' => 'Splitter → Customer',
                    'olt_to_customer'      => 'OLT → Customer',
                ])->default('olt_to_splitter')->required()->label('Route Type'),
                Select::make('olt_device_id')
                    ->label('OLT Device')
                    ->options(OltDevice::pluck('name', 'id'))
                    ->searchable()->nullable(),
                Select::make('splitter_id')
                    ->label('Splitter')
                    ->options(Splitter::pluck('name', 'id'))
                    ->searchable()->nullable(),
                Select::make('customer_id')
                    ->label('Customer')
                    ->options(Customer::pluck('name', 'id'))
                    ->searchable()->nullable(),
                Select::make('status')->options([
                    'active'   => 'Active',
                    'inactive' => 'Inactive',
                    'damaged'  => 'Damaged',
                ])->default('active')->required(),
                TextInput::make('color')
                    ->default('#FF6B35')
                    ->label('Route Color (Hex)')
                    ->placeholder('#FF6B35'),
                Textarea::make('note')->nullable()->columnSpanFull(),
            ])->columns(2),

            Section::make('Route Coordinates')->schema([
                Repeater::make('coordinates')
                    ->schema([
                        TextInput::make('0')
                            ->label('Latitude')
                            ->numeric()
                            ->placeholder('23.8103')
                            ->required(),
                        TextInput::make('1')
                            ->label('Longitude')
                            ->numeric()
                            ->placeholder('90.4125')
                            ->required(),
                    ])
                    ->columns(2)
                    ->addActionLabel('+ নতুন Point যোগ করুন')
                    ->minItems(2)
                    ->helperText('কমপক্ষে ২টা point দিতে হবে। Google Maps থেকে coordinates copy করুন।'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('type')->badge()
                    ->color(fn($state) => match($state) {
                        'olt_to_splitter'      => 'info',
                        'splitter_to_customer' => 'success',
                        'olt_to_customer'      => 'warning',
                    }),
                TextColumn::make('oltDevice.name')->label('OLT')->default('N/A'),
                TextColumn::make('splitter.name')->label('Splitter')->default('N/A'),
                TextColumn::make('customer.name')->label('Customer')->default('N/A'),
                TextColumn::make('status')->badge()
                    ->color(fn($state) => match($state) {
                        'active'   => 'success',
                        'inactive' => 'warning',
                        'damaged'  => 'danger',
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
            'index'  => Pages\ListFiberRoutes::route('/'),
            'create' => Pages\CreateFiberRoute::route('/create'),
            'edit'   => Pages\EditFiberRoute::route('/{record}/edit'),
        ];
    }
}
