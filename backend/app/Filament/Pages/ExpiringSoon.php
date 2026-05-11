<?php
namespace App\Filament\Pages;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\SmsService;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
class ExpiringSoon extends Page
{
    protected string $view = 'filament.pages.expiring-soon';
    protected ?string $heading = '⚠️ Expiring Soon';
    protected static ?int $navigationSort = 2;
    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clock';
    }
    public static function getNavigationLabel(): string
    {
        return 'Expiring Soon';
    }
    public static function getNavigationGroup(): string
    {
        return 'Operations';
    }
    public static function getNavigationBadge(): ?string
    {
        $count = Customer::where('status', 'active')
            ->whereDate('expire_date', '<=', now()->addDays(7))
            ->whereDate('expire_date', '>=', today())
            ->count();
        return $count > 0 ? (string) $count : null;
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    public function getViewData(): array
    {
        $customers = Customer::where('status', 'active')
            ->whereDate('expire_date', '<=', now()->addDays(7))
            ->whereDate('expire_date', '>=', today())
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
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('সব Expiring Customer কে SMS পাঠাবে')
                ->modalDescription('৭ দিনের মধ্যে expire হবে এমন সব active customer কে reminder SMS যাবে।')
                ->action(function () {
                    $customers = Customer::where('status', 'active')
                        ->whereDate('expire_date', '<=', now()->addDays(7))
                        ->whereDate('expire_date', '>=', today())
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
        ];
    }
}
