<?php
namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'company_name', 'value' => 'My ISP', 'group' => 'company', 'type' => 'text', 'label' => 'Company Name'],
            ['key' => 'company_phone', 'value' => '', 'group' => 'company', 'type' => 'text', 'label' => 'Company Phone'],
            ['key' => 'company_address', 'value' => '', 'group' => 'company', 'type' => 'text', 'label' => 'Company Address'],
            ['key' => 'company_email', 'value' => '', 'group' => 'company', 'type' => 'text', 'label' => 'Company Email'],
            ['key' => 'sms_gateway', 'value' => 'ssl', 'group' => 'sms', 'type' => 'select', 'label' => 'SMS Gateway'],
            ['key' => 'sms_api_key', 'value' => '', 'group' => 'sms', 'type' => 'password', 'label' => 'SMS API Key'],
            ['key' => 'sms_sender_id', 'value' => '', 'group' => 'sms', 'type' => 'text', 'label' => 'SMS Sender ID'],
            ['key' => 'sms_api_url', 'value' => '', 'group' => 'sms', 'type' => 'text', 'label' => 'SMS API URL'],
            ['key' => 'sms_enabled', 'value' => '0', 'group' => 'sms', 'type' => 'boolean', 'label' => 'Enable SMS'],
            ['key' => 'sms_payment_received', 'value' => 'Dear {name}, your payment of BDT {amount} received. Invoice: {invoice_no}. Thank you!', 'group' => 'sms_template', 'type' => 'text', 'label' => 'Payment Received SMS'],
            ['key' => 'sms_expiry_reminder', 'value' => 'Dear {name}, your internet will expire on {expire_date}. Please renew to continue.', 'group' => 'sms_template', 'type' => 'text', 'label' => 'Expiry Reminder SMS'],
            ['key' => 'sms_account_suspended', 'value' => 'Dear {name}, your connection suspended due to non-payment. Please contact us.', 'group' => 'sms_template', 'type' => 'text', 'label' => 'Account Suspended SMS'],
            ['key' => 'sms_account_activated', 'value' => 'Dear {name}, your internet connection activated. Enjoy browsing!', 'group' => 'sms_template', 'type' => 'text', 'label' => 'Account Activated SMS'],
            ['key' => 'bkash_enabled', 'value' => '0', 'group' => 'bkash', 'type' => 'boolean', 'label' => 'Enable bKash'],
            ['key' => 'bkash_app_key', 'value' => '', 'group' => 'bkash', 'type' => 'password', 'label' => 'bKash App Key'],
            ['key' => 'bkash_app_secret', 'value' => '', 'group' => 'bkash', 'type' => 'password', 'label' => 'bKash App Secret'],
            ['key' => 'bkash_username', 'value' => '', 'group' => 'bkash', 'type' => 'text', 'label' => 'bKash Username'],
            ['key' => 'bkash_password', 'value' => '', 'group' => 'bkash', 'type' => 'password', 'label' => 'bKash Password'],
            ['key' => 'bkash_sandbox', 'value' => '1', 'group' => 'bkash', 'type' => 'boolean', 'label' => 'bKash Sandbox Mode'],
            ['key' => 'nagad_enabled', 'value' => '0', 'group' => 'nagad', 'type' => 'boolean', 'label' => 'Enable Nagad'],
            ['key' => 'nagad_merchant_id', 'value' => '', 'group' => 'nagad', 'type' => 'text', 'label' => 'Nagad Merchant ID'],
            ['key' => 'nagad_merchant_private_key', 'value' => '', 'group' => 'nagad', 'type' => 'password', 'label' => 'Nagad Private Key'],
            ['key' => 'nagad_sandbox', 'value' => '1', 'group' => 'nagad', 'type' => 'boolean', 'label' => 'Nagad Sandbox Mode'],
            ['key' => 'expiry_reminder_days', 'value' => '3', 'group' => 'general', 'type' => 'text', 'label' => 'Expiry Reminder Days Before'],
            ['key' => 'auto_suspend', 'value' => '1', 'group' => 'general', 'type' => 'boolean', 'label' => 'Auto Suspend Expired Customers'],
            ['key' => 'currency', 'value' => 'BDT', 'group' => 'general', 'type' => 'text', 'label' => 'Currency'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        $this->command->info('Settings seeded successfully!');
    }
}
