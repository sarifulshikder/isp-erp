<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportTicketResource\Pages;
use App\Models\SupportTicket;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    public static function getNavigationGroup(): ?string
    {
        return "Support Management";
    }

    public static function getNavigationIcon(): ?string
    {
        return "heroicon-o-ticket";
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make("customer_id")
                            ->relationship("customer", "name")
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make("user_id")
                            ->label("Assign Technician")
                            ->relationship("technician", "name")
                            ->searchable(),

                        Forms\Components\TextInput::make("subject")
                            ->required(),

                        Forms\Components\Textarea::make("description")
                            ->required(),

                        Forms\Components\Select::make("priority")
                            ->options([
                                "low" => "Low",
                                "medium" => "Medium",
                                "high" => "High",
                            ])
                            ->default("medium"),

                        Forms\Components\Select::make("status")
                            ->options([
                                "open" => "Open",
                                "pending" => "Pending",
                                "resolved" => "Resolved",
                                "closed" => "Closed",
                            ])
                            ->default("open"),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("customer.name")
                    ->label("Customer")
                    ->searchable(),

                TextColumn::make("technician.name")
                    ->label("Assigned To"),

                TextColumn::make("subject"),

                TextColumn::make("status")
                    ->badge()
                    ->color(fn ($state) => $state === "resolved" ? "success" : "warning"),

                TextColumn::make("created_at")
                    ->dateTime()
                    ->label("Created"),
            ])
            ->recordActions([
                Action::make('location')
                    ->label('')
                    ->icon('heroicon-o-map-pin')
                    ->color('info')
                    ->url(fn ($record) => $record->customer && $record->customer->latitude
                        ? 'https://www.google.com/maps?q=' . $record->customer->latitude . ',' . $record->customer->longitude
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->customer && $record->customer->latitude),
                \Filament\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListSupportTickets::route("/"),
            "create" => Pages\CreateSupportTicket::route("/create"),
            "edit" => Pages\EditSupportTicket::route("/{record}/edit"),
        ];
    }
}
