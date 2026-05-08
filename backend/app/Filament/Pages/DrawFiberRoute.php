<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DrawFiberRoute extends Page
{
    protected string $view = 'filament.pages.draw-fiber-route';
    protected ?string $heading = 'Draw Fiber Route';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-pencil';
    }

    public static function getNavigationLabel(): string
    {
        return 'Draw Route';
    }

    public static function getNavigationGroup(): string
    {
        return 'Network';
    }

    public static function getNavigationSort(): int
    {
        return 4;
    }
}
