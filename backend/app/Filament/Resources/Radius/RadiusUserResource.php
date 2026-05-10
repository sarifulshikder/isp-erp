<?php
namespace App\Filament\Resources\Radius;
use App\Filament\Resources\Radius\RadiusUserResource\Pages;
use App\Models\Radius\RadCheck;
use App\Models\Customer;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RadiusUserResource extends Resource
{
    protected static ?string $model = RadCheck::class;
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string { return 'heroicon-o-users'; }
    public static function getNavigationLabel(): string { return 'Radius Users'; }
    public static function getNavigationGroup(): string { return 'FreeRADIUS'; }
    public static function getModelLabel(): string { return 'Radius User'; }
    public static function getPluralModelLabel(): string { return 'Radius Users'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Radius User')->schema([
                TextInput::make('username')->required()->label('Username'),
                TextInput::make('attribute')->default('Cleartext-Password')->required(),
                TextInput::make('op')->default(':=')->required()->label('Operator'),
                TextInput::make('value')->required()->label('Password'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('username')->searchable()->sortable(),
                TextColumn::make('attribute')->label('Attribute')->badge(),
                TextColumn::make('op')->label('Op'),
                TextColumn::make('value')->label('Password')
                    ->formatStateUsing(fn() => '••••••••'),
            ])
            ->actions([
                EditAction::make(),
                \Filament\Actions\Action::make('test')
                    ->label('Test Auth')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (RadCheck $record) {
                        $result = shell_exec("radtest {$record->username} {$record->value} localhost 0 testing123 2>&1");
                        $success = str_contains($result, 'Access-Accept');
                        \Filament\Notifications\Notification::make()
                            ->title($success ? 'Access-Accept ✅' : 'Access-Reject ❌')
                            ->color($success ? 'success' : 'danger')
                            ->send();
                    }),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRadiusUsers::route('/'),
            'create' => Pages\CreateRadiusUser::route('/create'),
            'edit'   => Pages\EditRadiusUser::route('/{record}/edit'),
        ];
    }
}
