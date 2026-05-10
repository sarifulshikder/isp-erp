<?php
namespace App\Filament\Resources\MikrotikImportReviews\Pages;
use App\Filament\Resources\MikrotikImportReviews\MikrotikImportReviewResource;
use Filament\Resources\Pages\ListRecords;
class ListMikrotikImportReviews extends ListRecords
{
    protected static string $resource = MikrotikImportReviewResource::class;
    protected ?string $heading = 'MikroTik Import Reviews';
}
