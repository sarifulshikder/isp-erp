<?php
namespace App\Filament\Resources\MikrotikImportReviews;
use App\Filament\Resources\MikrotikImportReviews\Pages\ListMikrotikImportReviews;
use App\Models\MikrotikImportReview;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Zone;
use App\Models\MikrotikDevice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
class MikrotikImportReviewResource extends Resource
{
    protected static ?string $model = MikrotikImportReview::class;
    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return Heroicon::OutlinedInboxArrowDown;
    }
    public static function getNavigationGroup(): ?string
    {
        return 'Network';
    }
    public static function getNavigationSort(): ?int
    {
        return 2;
    }
    public static function getNavigationLabel(): string
    {
        return 'Import Reviews';
    }
    public static function getNavigationBadge(): ?string
    {
        $count = MikrotikImportReview::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mikrotikDevice.name')->label('Router')->sortable(),
                TextColumn::make('username')->searchable(),
                TextColumn::make('password'),
                TextColumn::make('profile')->placeholder('—'),
                TextColumn::make('comment')->placeholder('—')->limit(30),
                TextColumn::make('status')->badge()
                    ->color(fn($state) => match($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('customer.name')->label('Linked Customer')->placeholder('—'),
                TextColumn::make('created_at')->dateTime()->label('Imported At'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                SelectFilter::make('mikrotik_device_id')
                    ->label('Router')
                    ->relationship('mikrotikDevice', 'name'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(MikrotikImportReview $record) => $record->status === 'pending')
                    ->form([
                        Section::make('নতুন Customer তৈরি করুন')->schema([
                            TextInput::make('name')->required()->label('Full Name'),
                            TextInput::make('phone')->required(),
                            TextInput::make('email')->email()->nullable(),
                            TextInput::make('address')->nullable(),
                            TextInput::make('username')->required(),
                            TextInput::make('password')->required(),
                            Select::make('package_id')
                                ->label('Package')
                                ->options(Package::where('status', 'active')->pluck('name', 'id'))
                                ->required()->searchable(),
                            Select::make('zone_id')
                                ->label('Zone')
                                ->options(Zone::where('is_active', true)->pluck('name', 'id'))
                                ->nullable()->searchable(),
                            DatePicker::make('connection_date')->default(now()),
                            DatePicker::make('expire_date')->required(),
                            Select::make('mikrotik_mode')
                                ->label('MikroTik Mode')
                                ->options([
                                    'freeradius_only' => '🔒 FreeRADIUS Only',
                                    'specific'        => '🎯 Specific Router (এই router)',
                                    'all'             => '📡 All Routers',
                                ])
                                ->default('specific')
                                ->required(),
                        ])->columns(2),
                    ])
                    ->fillForm(fn(MikrotikImportReview $record) => [
                        'username' => $record->username,
                        'password' => $record->password,
                    ])
                    ->action(function (MikrotikImportReview $record, array $data) {
                        $customer = Customer::create([
                            'name'               => $data['name'],
                            'phone'              => $data['phone'],
                            'email'              => $data['email'] ?? null,
                            'address'            => $data['address'] ?? null,
                            'username'           => $data['username'],
                            'password'           => $data['password'],
                            'package_id'         => $data['package_id'],
                            'zone_id'            => $data['zone_id'] ?? null,
                            'connection_date'    => $data['connection_date'] ?? now(),
                            'expire_date'        => $data['expire_date'],
                            'status'             => 'active',
                            'connection_type'    => 'pppoe',
                            'mikrotik_mode'      => $data['mikrotik_mode'],
                            'mikrotik_device_id' => $data['mikrotik_mode'] === 'specific'
                                ? $record->mikrotik_device_id : null,
                            'mikrotik_profile'   => $record->profile,
                        ]);
                        $record->update([
                            'status'      => 'approved',
                            'customer_id' => $customer->id,
                        ]);
                        Notification::make()
                            ->title('✅ Customer তৈরি হয়েছে: ' . $customer->name)
                            ->success()->send();
                    }),
                Action::make('link_existing')
                    ->label('Link Customer')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->visible(fn(MikrotikImportReview $record) => $record->status === 'pending')
                    ->form([
                        Select::make('customer_id')
                            ->label('Existing Customer বাছুন')
                            ->options(Customer::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (MikrotikImportReview $record, array $data) {
                        $record->update([
                            'status'      => 'approved',
                            'customer_id' => $data['customer_id'],
                        ]);
                        Notification::make()
                            ->title('✅ Existing customer এ link হয়েছে')
                            ->success()->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(MikrotikImportReview $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (MikrotikImportReview $record) {
                        $record->update(['status' => 'rejected']);
                        Notification::make()->title('Rejected')->warning()->send();
                    }),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
    public static function getPages(): array
    {
        return [
            'index' => ListMikrotikImportReviews::route('/'),
        ];
    }
}
