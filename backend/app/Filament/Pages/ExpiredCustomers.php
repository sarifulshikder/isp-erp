<?php
namespace App\Filament\Pages;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\SmsService;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
class ExpiredCustomers extends Page
{
    protected string $view = 'filament.pages.expired-customers';
    protected ?string $heading = '🔴 Expired Customers';
    protected static ?int $navigationSort = 3;
    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-x-circle';
    }
    public static function getNavigationLabel(): string
    {
        return 'Expired Customers';
    }
    public static function getNavigationGroup(): string
    {
        return 'Operations';
    }
    public static function getNavigationBadge(): ?string
    {
        $count = Customer::where('status', 'active')
            ->whereDate('expire_date', '<', today())
            ->count();
        return $count > 0 ? (string) $count : null;
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
    public function getViewData(): array
    {
        $customers = Customer::whereDate('expire_date', '<', today())
            ->whereIn('status', ['active', 'suspended'])
            ->with(['package', 'zone'])
            ->orderBy('expire_date')
            ->get();
        return ['customers' => $customers];
    }
    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulk_sms')
                ->label('Bulk SMS পাঠাও')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('সব Expired Customer কে SMS পাঠাবে')
                ->modalDescription('Expire হয়ে গেছে এমন সব customer কে reminder SMS যাবে।')
                ->action(function () {
                    $customers = Customer::whereDate('expire_date', '<', today())
                        ->whereIn('status', ['active', 'suspended'])
                        ->get();
                    $sms = new SmsService();
                    $sent = 0;
                    foreach ($customers as $customer) {
                        try {
                            $sms->sendFromTemplate('sms_expiry_reminder', $customer->phone, [
                                'name'        => $customer->name,
                                'expire_date' => $customer->expire_date->format('d M Y'),
                                'amount'      => $customer->package?->price ?? '',
                            ]);
                            $sent++;
                        } catch (\Exception $e) {}
                    }
                    Notification::make()
                        ->title("✅ {$sent} জন customer কে SMS পাঠানো হয়েছে")
                        ->success()->send();
                }),
            Action::make('suspend_all')
                ->label('সবাইকে Suspend করো')
                ->icon('heroicon-o-pause-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('সব Expired Customer Suspend হবে')
                ->action(function () {
                    $count = Customer::where('status', 'active')
                        ->whereDate('expire_date', '<', today())
                        ->update(['status' => 'suspended']);
                    Notification::make()
                        ->title("✅ {$count} জন customer suspend হয়েছে")
                        ->success()->send();
                }),
        ];
    }
}
