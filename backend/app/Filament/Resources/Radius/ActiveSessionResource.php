<?php
namespace App\Filament\Resources\Radius;
use App\Filament\Resources\Radius\ActiveSessionResource\Pages;
use App\Models\Radius\RadAcct;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActiveSessionResource extends Resource
{
    protected static ?string $model = RadAcct::class;
    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string { return 'heroicon-o-signal'; }
    public static function getNavigationLabel(): string { return 'Active Sessions'; }
    public static function getNavigationGroup(): string { return 'FreeRADIUS'; }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')->searchable()->sortable(),
                TextColumn::make('nasipaddress')->label('NAS IP'),
                TextColumn::make('framedipaddress')->label('Client IP'),
                TextColumn::make('callingstationid')->label('MAC'),
                TextColumn::make('acctstarttime')->label('Connected At')->dateTime()->sortable(),
                TextColumn::make('acctsessiontime')->label('Duration')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'N/A';
                        $h = intdiv($state, 3600);
                        $m = intdiv($state % 3600, 60);
                        $s = $state % 60;
                        return "{$h}h {$m}m {$s}s";
                    }),
                TextColumn::make('acctinputoctets')->label('Download')
                    ->formatStateUsing(fn($state) => $state ? round($state / 1048576, 2) . ' MB' : '0 MB'),
                TextColumn::make('acctoutputoctets')->label('Upload')
                    ->formatStateUsing(fn($state) => $state ? round($state / 1048576, 2) . ' MB' : '0 MB'),
                TextColumn::make('acctstoptime')->label('Status')
                    ->formatStateUsing(fn($state) => $state ? 'Disconnected' : 'Online')
                    ->badge()
                    ->color(fn($state) => $state ? 'danger' : 'success'),
            ])
            ->defaultSort('acctstarttime', 'desc')
            ->filters([
                SelectFilter::make('acctstoptime')
                    ->label('Status')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'online') {
                            $query->whereNull('acctstoptime');
                        } elseif ($data['value'] === 'offline') {
                            $query->whereNotNull('acctstoptime');
                        }
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActiveSessions::route('/'),
        ];
    }
}
