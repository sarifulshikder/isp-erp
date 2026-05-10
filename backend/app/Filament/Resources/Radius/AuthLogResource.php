<?php
namespace App\Filament\Resources\Radius;
use App\Filament\Resources\Radius\AuthLogResource\Pages;
use App\Models\Radius\RadPostAuth;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuthLogResource extends Resource
{
    protected static ?string $model = RadPostAuth::class;
    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string { return 'heroicon-o-clipboard-document-list'; }
    public static function getNavigationLabel(): string { return 'Auth Log'; }
    public static function getNavigationGroup(): string { return 'FreeRADIUS'; }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')->searchable()->sortable(),
                TextColumn::make('pass')->label('Password')
                    ->formatStateUsing(fn() => '••••••••'),
                TextColumn::make('reply')->label('Result')->badge()
                    ->color(fn($state) => str_contains($state, 'Accept') ? 'success' : 'danger'),
                TextColumn::make('authdate')->label('Date/Time')->sortable(),
            ])
            ->defaultSort('authdate', 'desc')
            ->filters([
                SelectFilter::make('reply')
                    ->label('Result')
                    ->options([
                        'Access-Accept' => 'Accept',
                        'Access-Reject' => 'Reject',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthLogs::route('/'),
        ];
    }
}
