<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BkashService
{
    private string $appKey;
    private string $appSecret;
    private string $username;
    private string $password;
    private string $baseUrl;
    private bool $sandbox;

    public function __construct()
    {
        $this->appKey    = Setting::get('bkash_app_key', '');
        $this->appSecret = Setting::get('bkash_app_secret', '');
        $this->username  = Setting::get('bkash_username', '');
        $this->password  = Setting::get('bkash_password', '');
        $this->sandbox   = (bool) Setting::get('bkash_sandbox', true);
        $this->baseUrl   = $this->sandbox
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/'
            : 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/';
    }

    // ─── Token (cached 50 min) ───────────────────────────────────────────────
    public function getToken(): ?string
    {
        return Cache::remember('bkash_token', 3000, function () {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'username'      => $this->username,
                'password'      => $this->password,
            ])->post($this->baseUrl . 'token/grant', [
                'app_key'    => $this->appKey,
                'app_secret' => $this->appSecret,
            ]);

            if ($response->successful() && isset($response['id_token'])) {
                Log::info('bKash token granted');
                return $response['id_token'];
            }

            Log::error('bKash token failed', $response->json() ?? []);
            return null;
        });
    }

    // ─── Create Payment ──────────────────────────────────────────────────────
    public function createPayment(string $invoiceId, float $amount, string $callbackUrl): array
    {
        $token = $this->getToken();
        if (!$token) {
            return ['success' => false, 'message' => 'Token generation failed'];
        }

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => $token,
            'X-APP-Key'     => $this->appKey,
        ])->post($this->baseUrl . 'create', [
            'mode'                => '0011',
            'payerReference'      => $invoiceId,
            'callbackURL'         => $callbackUrl,
            'amount'              => number_format($amount, 2, '.', ''),
            'currency'            => 'BDT',
            'intent'              => 'sale',
            'merchantInvoiceNumber' => 'INV-' . $invoiceId,
        ]);

        Log::info('bKash createPayment', $response->json() ?? []);

        if ($response->successful() && isset($response['bkashURL'])) {
            return [
                'success'    => true,
                'bkashURL'   => $response['bkashURL'],
                'paymentID'  => $response['paymentID'],
            ];
        }

        return [
            'success' => false,
            'message' => $response['statusMessage'] ?? 'Payment creation failed',
        ];
    }

    // ─── Execute Payment ─────────────────────────────────────────────────────
    public function executePayment(string $paymentId): array
    {
        $token = $this->getToken();
        if (!$token) {
            return ['success' => false, 'message' => 'Token generation failed'];
        }

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => $token,
            'X-APP-Key'     => $this->appKey,
        ])->post($this->baseUrl . 'execute', [
            'paymentID' => $paymentId,
        ]);

        Log::info('bKash executePayment', $response->json() ?? []);

        if ($response->successful() && ($response['statusCode'] ?? '') === '0000') {
            return [
                'success'         => true,
                'trxID'           => $response['trxID'],
                'paymentID'       => $response['paymentID'],
                'amount'          => $response['amount'],
                'customerMsisdn'  => $response['customerMsisdn'] ?? '',
            ];
        }

        return [
            'success' => false,
            'message' => $response['statusMessage'] ?? 'Payment execution failed',
        ];
    }

    // ─── Query Payment ───────────────────────────────────────────────────────
    public function queryPayment(string $paymentId): array
    {
        $token = $this->getToken();
        if (!$token) {
            return ['success' => false, 'message' => 'Token generation failed'];
        }

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => $token,
            'X-APP-Key'     => $this->appKey,
        ])->post($this->baseUrl . 'payment/status', [
            'paymentID' => $paymentId,
        ]);

        return $response->json() ?? [];
    }
}
