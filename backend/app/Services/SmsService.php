<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private string $gateway;
    private string $apiKey;
    private string $senderId;
    private bool $enabled;

    public function __construct()
    {
        $this->gateway = Setting::get('sms_gateway', 'ssl');
        $this->apiKey = Setting::get('sms_api_key', '');
        $this->senderId = Setting::get('sms_sender_id', '');
        $this->enabled = Setting::get('sms_enabled', '0') === '1';
    }

    public function send(string $phone, string $message): bool
    {
        if (!$this->enabled) {
            Log::info("SMS disabled. Would send to {$phone}: {$message}");
            return false;
        }

        // Format phone number with country code
        $phone = $this->formatPhone($phone);

        try {
            return match($this->gateway) {
                'ssl' => $this->sendViaSsl($phone, $message),
                'bulksmsbd' => $this->sendViaBulkSmsBd($phone, $message),
                'alphanet' => $this->sendViaAlphaNet($phone, $message),
                'custom' => $this->sendViaSmsFlow($phone, $message),
                default => false,
            };
        } catch (\Exception $e) {
            Log::error("SMS Error: " . $e->getMessage());
            return false;
        }
    }

    private function formatPhone(string $phone): string
    {
        // Remove spaces and dashes
        $phone = preg_replace('/[\s\-]/', '', $phone);
        // Add Bangladesh country code if not present
        if (str_starts_with($phone, '0')) {
            $phone = '+88' . $phone;
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+88' . $phone;
        }
        return $phone;
    }

    private function sendViaSmsFlow(string $phone, string $message): bool
    {
        $response = Http::withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://smsflow.app/api/v1/send', [
            'to' => $phone,
            'message' => $message,
        ]);

        Log::info('SMS Flow Response: ' . $response->body());
        return $response->successful();
    }

    private function sendViaSsl(string $phone, string $message): bool
    {
        $response = Http::get('https://sms.sslwireless.com/pushapi/dynamic/server.php', [
            'api_token' => $this->apiKey,
            'sid' => $this->senderId,
            'msisdn' => $phone,
            'sms' => $message,
            'csmsid' => time(),
        ]);
        return $response->successful();
    }

    private function sendViaBulkSmsBd(string $phone, string $message): bool
    {
        $response = Http::get('https://bulksmsbd.net/api/smsapi', [
            'api_key' => $this->apiKey,
            'type' => 'text',
            'number' => $phone,
            'senderid' => $this->senderId,
            'message' => $message,
        ]);
        return $response->successful();
    }

    private function sendViaAlphaNet(string $phone, string $message): bool
    {
        $response = Http::post('http://54.254.154.154/api/v1/send-sms', [
            'api_key' => $this->apiKey,
            'type' => 'text',
            'contacts' => $phone,
            'senderid' => $this->senderId,
            'msg' => $message,
        ]);
        return $response->successful();
    }

    public function sendFromTemplate(string $templateKey, string $phone, array $data): bool
    {
        $template = Setting::get($templateKey, '');
        if (!$template) return false;
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $this->send($phone, $template);
    }

    public function paymentReceived(string $phone, array $data): bool
    {
        return $this->sendFromTemplate('sms_payment_received', $phone, $data);
    }

    public function expiryReminder(string $phone, array $data): bool
    {
        return $this->sendFromTemplate('sms_expiry_reminder', $phone, $data);
    }

    public function accountSuspended(string $phone, array $data): bool
    {
        return $this->sendFromTemplate('sms_account_suspended', $phone, $data);
    }

    public function accountActivated(string $phone, array $data): bool
    {
        return $this->sendFromTemplate('sms_account_activated', $phone, $data);
    }
}
