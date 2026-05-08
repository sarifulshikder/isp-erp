<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class SnipeItService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.snipeit.url');
        $this->token   = config('services.snipeit.token');
    }

    private function http()
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ])->baseUrl($this->baseUrl);
    }

    public function getAssets(int $limit = 200): array
    {
        $response = $this->http()->get('/api/v1/hardware', ['limit' => $limit]);
        return $response->json() ?? [];
    }

    public function getCategories(): array
    {
        $response = $this->http()->get('/api/v1/categories', ['limit' => 100]);
        return $response->json()['rows'] ?? [];
    }

    public function getModels(): array
    {
        $response = $this->http()->get('/api/v1/models', ['limit' => 100]);
        return $response->json()['rows'] ?? [];
    }

    // category_id দিলে সেই category র default model_id return করে
    public function getModelIdByCategory(int $categoryId): ?int
    {
        $models = $this->getModels();
        foreach ($models as $model) {
            if (($model['category']['id'] ?? null) == $categoryId) {
                return (int) $model['id'];
            }
        }
        return null;
    }

    public function createAsset(array $data): array
    {
        $categoryId = (int) $data['category_id'];
        $modelId    = $this->getModelIdByCategory($categoryId);

        if (!$modelId) {
            return ['status' => 'error', 'messages' => 'No model found for this category'];
        }

        $payload = [
            'asset_tag'   => $data['asset_tag'],
            'name'        => $data['name'],
            'category_id' => $categoryId,
            'model_id'    => $modelId,
            'status_id'   => 1,
        ];

        if (!empty($data['serial']))       $payload['serial']       = $data['serial'];
        if (!empty($data['model_number'])) $payload['model_number'] = $data['model_number'];
        if (!empty($data['notes']))        $payload['notes']        = $data['notes'];

        $response = $this->http()->post('/api/v1/hardware', $payload);
        return $response->json() ?? [];
    }

    public function checkoutAsset(int $assetId, int $snipeUserId, string $note = ''): array
    {
        $response = $this->http()->post("/api/v1/hardware/{$assetId}/checkout", [
            'checkout_to_type' => 'user',
            'assigned_user'    => $snipeUserId,
            'note'             => $note,
        ]);
        return $response->json() ?? [];
    }

    public function checkinAsset(int $assetId, string $note = ''): array
    {
        $response = $this->http()->post("/api/v1/hardware/{$assetId}/checkin", [
            'note' => $note,
        ]);
        return $response->json() ?? [];
    }

    public function getAsset(int $assetId): array
    {
        $response = $this->http()->get("/api/v1/hardware/{$assetId}");
        return $response->json() ?? [];
    }

    public function findBySerial(string $serial): array
    {
        $response = $this->http()->get('/api/v1/hardware/byserial/' . $serial);
        return $response->json() ?? [];
    }
}
