<?php
namespace App\Filament\Pages;

use App\Models\Setting;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.settings';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?int $navigationSort = 99;

    

    public ?array $data = [];


    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public function mount(): void
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Company Information')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        TextInput::make('company_name')->label('Company Name')->required(),
                        TextInput::make('company_phone')->label('Phone'),
                        TextInput::make('company_email')->label('Email')->email(),
                        TextInput::make('company_address')->label('Address'),
                    ])->columns(2),

                Section::make('SMS Gateway')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        Toggle::make('sms_enabled')->label('Enable SMS Notifications'),
                        Select::make('sms_gateway')
                            ->label('SMS Gateway Provider')
                            ->options([
                                'ssl' => 'SSL Wireless',
                                'bulksmsbd' => 'Bulk SMS BD',
                                'alphanet' => 'Alpha Net',
                                'custom' => 'Custom API',
                            ]),
                        TextInput::make('sms_api_key')->label('API Key')->password()->revealable(),
                        TextInput::make('sms_sender_id')->label('Sender ID'),
                        TextInput::make('sms_api_url')->label('Custom API URL (optional)'),
                    ])->columns(2),

                Section::make('SMS Templates')
                    ->icon('heroicon-o-document-text')
                    ->description('Use {name}, {amount}, {invoice_no}, {expire_date} as placeholders')
                    ->schema([
                        Textarea::make('sms_payment_received')->label('Payment Received')->rows(2),
                        Textarea::make('sms_expiry_reminder')->label('Expiry Reminder')->rows(2),
                        Textarea::make('sms_account_suspended')->label('Account Suspended')->rows(2),
                        Textarea::make('sms_account_activated')->label('Account Activated')->rows(2),
                    ]),

                Section::make('bKash Payment')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Toggle::make('bkash_enabled')->label('Enable bKash'),
                        Toggle::make('bkash_sandbox')->label('Sandbox Mode'),
                        TextInput::make('bkash_username')->label('Username'),
                        TextInput::make('bkash_password')->label('Password')->password()->revealable(),
                        TextInput::make('bkash_app_key')->label('App Key')->password()->revealable(),
                        TextInput::make('bkash_app_secret')->label('App Secret')->password()->revealable(),
                    ])->columns(2),

                Section::make('Nagad Payment')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Toggle::make('nagad_enabled')->label('Enable Nagad'),
                        Toggle::make('nagad_sandbox')->label('Sandbox Mode'),
                        TextInput::make('nagad_merchant_id')->label('Merchant ID'),
                        TextInput::make('nagad_merchant_private_key')->label('Private Key')->password()->revealable(),
                    ])->columns(2),

                Section::make('General Settings')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        TextInput::make('expiry_reminder_days')->label('Expiry Reminder (days before)')->numeric(),
                        Toggle::make('auto_suspend')->label('Auto Suspend Expired Customers'),
                        TextInput::make('currency')->label('Currency'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }
        Notification::make()
            ->title('Settings saved successfully!')
            ->success()
            ->send();
    }
}
