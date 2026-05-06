<?php
$file = '/app/config/auth.php';
$content = file_get_contents($file);

$guardPatch = "
        'customer' => [
            'driver' => 'session',
            'provider' => 'customers',
        ],";

$providerPatch = "
        'customers' => [
            'driver' => 'eloquent',
            'model' => App\\Models\\Customer::class,
        ],";

$content = str_replace(
    "'web' => [\n            'driver' => 'session',\n            'provider' => 'users',\n        ],",
    "'web' => [\n            'driver' => 'session',\n            'provider' => 'users',\n        ]," . $guardPatch,
    $content
);

$content = str_replace(
    "'users' => [\n            'driver' => 'eloquent',\n            'model' => env('AUTH_MODEL', App\\Models\\User::class),\n        ],",
    "'users' => [\n            'driver' => 'eloquent',\n            'model' => env('AUTH_MODEL', App\\Models\\User::class),\n        ]," . $providerPatch,
    $content
);

file_put_contents($file, $content);
echo "Done!";
