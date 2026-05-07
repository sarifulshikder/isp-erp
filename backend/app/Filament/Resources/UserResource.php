<?php
namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function getNavigationGroup(): string
    {
        return 'Settings';
    }

    public static function getNavigationLabel(): string
    {
        return 'Users & Roles';
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label('নাম')
                ->required(),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true),
            TextInput::make('password')
                ->label('Password')
                ->password()
                ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                ->dehydrated(fn ($state) => filled($state))
                ->nullable()
                ->hint('নতুন password দিতে চাইলে লিখুন, না চাইলে ফাঁকা রাখুন'),
            Select::make('roles')
                ->label('Role')
                ->multiple()
                ->relationship('roles', 'name')
                ->preload()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label('নাম')
                ->searchable(),
            TextColumn::make('email')
                ->label('Email')
                ->searchable(),
            TextColumn::make('roles.name')
                ->label('Role')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'super_admin' => 'danger',
                    'manager' => 'warning',
                    'staff' => 'info',
                    default => 'gray',
                }),
            TextColumn::make('created_at')
                ->label('তৈরি')
                ->dateTime('d M Y')
                ->sortable(),
        ])
        ->actions([
            EditAction::make(),
            DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
