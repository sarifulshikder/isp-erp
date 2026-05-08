<?php
namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\CreateAction;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-archive-box';
    }

    public static function getNavigationLabel(): string
    {
        return 'Inventory';
    }

    public static function getNavigationGroup(): string
    {
        return 'Settings';
    }

    public static function getNavigationSort(): ?int
    {
        return 11;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('category_id')
                ->label('Category')
                ->options(InventoryCategory::pluck('name', 'id'))
                ->required()
                ->searchable(),
            TextInput::make('name')
                ->label('Asset নাম')
                ->required(),
            TextInput::make('serial_number')
                ->label('Serial Number')
                ->unique(ignoreRecord: true),
            TextInput::make('brand')
                ->label('Brand'),
            TextInput::make('model')
                ->label('Model'),
            Select::make('status')
                ->label('Status')
                ->options([
                    'available' => 'Available',
                    'assigned'  => 'Assigned',
                    'damaged'   => 'Damaged',
                    'lost'      => 'Lost',
                ])
                ->default('available')
                ->required(),
            TextInput::make('purchase_price')
                ->label('ক্রয় মূল্য (BDT)')
                ->numeric()
                ->prefix('৳'),
            DatePicker::make('purchase_date')
                ->label('ক্রয় তারিখ'),
            Select::make('assigned_customer_id')
                ->label('Assign to Customer')
                ->options(Customer::pluck('name', 'id'))
                ->searchable()
                ->nullable(),
            DatePicker::make('assigned_date')
                ->label('Assign তারিখ'),
            Textarea::make('notes')
                ->label('Notes')
                ->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('নাম')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('serial_number')
                    ->label('Serial No')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('brand')
                    ->label('Brand')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'assigned'  => 'warning',
                        'damaged'   => 'danger',
                        'lost'      => 'gray',
                    }),
                TextColumn::make('customer.name')
                    ->label('Assigned To')
                    ->searchable()
                    ->default('—'),
                TextColumn::make('purchase_price')
                    ->label('মূল্য')
                    ->money('BDT')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('যোগ করা হয়েছে')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(InventoryCategory::pluck('name', 'id')),
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'assigned'  => 'Assigned',
                        'damaged'   => 'Damaged',
                        'lost'      => 'Lost',
                    ]),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInventory::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit'   => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
