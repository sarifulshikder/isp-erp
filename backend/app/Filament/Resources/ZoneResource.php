<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ZoneResource\Pages;
use App\Models\Zone;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-map-pin';
    }

    public static function getNavigationLabel(): string
    {
        return 'Zones / এলাকা';
    }

    public static function getNavigationGroup(): string
    {
        return 'Settings';
    }

    public static function getNavigationSort(): ?int
    {
        return 12;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label('Zone নাম')
                ->required(),
            TextInput::make('area')
                ->label('এলাকা'),
            TextInput::make('contact_person')
                ->label('দায়িত্বপ্রাপ্ত ব্যক্তি'),
            TextInput::make('contact_phone')
                ->label('যোগাযোগ নম্বর')
                ->tel(),
            Textarea::make('notes')
                ->label('Notes')
                ->rows(2),
            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Zone নাম')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('area')
                    ->label('এলাকা')
                    ->searchable(),
                TextColumn::make('contact_person')
                    ->label('দায়িত্বপ্রাপ্ত'),
                TextColumn::make('contact_phone')
                    ->label('ফোন'),
                TextColumn::make('customers_count')
                    ->label('Customers')
                    ->counts('customers')
                    ->badge()
                    ->color('info'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('তৈরি')
                    ->dateTime('d M Y')
                    ->sortable(),
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
            'index'  => Pages\ListZones::route('/'),
            'create' => Pages\CreateZone::route('/create'),
            'edit'   => Pages\EditZone::route('/{record}/edit'),
        ];
    }
}
