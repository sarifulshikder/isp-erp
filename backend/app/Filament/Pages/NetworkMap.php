<?php
namespace App\Filament\Pages;
use Filament\Pages\Page;
class NetworkMap extends Page
{
    protected string $view = 'filament.pages.network-map';
    protected ?string $heading = 'Fiber & Network Map';
    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-map';
    }
    public static function getNavigationLabel(): string
    {
        return 'Network Map';
    }
    public static function getNavigationGroup(): string
    {
        return 'Network';
    }
    public static function getNavigationSort(): int
    {
        return 3;
    }
}
