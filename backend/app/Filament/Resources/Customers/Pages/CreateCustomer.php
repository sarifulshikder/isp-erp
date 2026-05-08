<?php
namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Inventory;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function afterCreate(): void
    {
        $inventoryId = $this->data['assigned_inventory_id'] ?? null;

        if ($inventoryId) {
            Inventory::where('id', $inventoryId)->update([
                'assigned_customer_id' => $this->record->id,
                'assigned_date'        => now()->toDateString(),
                'status'               => 'assigned',
            ]);
        }
    }
}
