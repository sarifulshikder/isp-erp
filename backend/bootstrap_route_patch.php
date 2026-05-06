<?php
$file = '/app/bootstrap/app.php';
$content = file_get_contents($file);

$content = str_replace(
    "health: '/up',",
    "health: '/up',
        using: function() {
            Route::middleware('web')->prefix('portal')->group(base_path('routes/portal.php'));
        },",
    $content
);

// Add Route facade use
$content = str_replace(
    "use Illuminate\Foundation\Application;",
    "use Illuminate\Foundation\Application;\nuse Illuminate\Support\Facades\Route;",
    $content
);

file_put_contents($file, $content);
echo "Done!";
