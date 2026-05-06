<?php
$file = '/app/bootstrap/app.php';
$content = file_get_contents($file);

// Add portal route
$content = str_replace(
    "then(function () {
            //
        })",
    "then(function () {
            //
        })",
    $content
);

// Add middleware aliases
$content = str_replace(
    '->withMiddleware(function (Middleware $middleware) {
        //
    })',
    '->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            \'customer.auth\' => \App\Http\Middleware\CustomerAuth::class,
            \'customer.guest\' => \App\Http\Middleware\CustomerGuest::class,
        ]);
    })',
    $content
);

// Add portal routes
$content = str_replace(
    '->withRouting(',
    '->withRouting(',
    $content
);

file_put_contents($file, $content);
echo "Done!";
