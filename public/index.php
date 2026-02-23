<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Force error display at the highest level
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    /*
    |--------------------------------------------------------------------------
    | Register The Auto Loader
    |--------------------------------------------------------------------------
    */
    require __DIR__.'/../vendor/autoload.php';

    /*
    |--------------------------------------------------------------------------
    | Run The Application
    |--------------------------------------------------------------------------
    */
    $app = require_once __DIR__.'/../bootstrap/app.php';

    $kernel = $app->make(Kernel::class);

    $response = $kernel->handle(
        $request = Request::capture()
    );

    $response->send();

    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    // If anything fails, scream the error to the browser
    header('Content-Type: text/html', true, 500);
    echo "<html><body style='background:#f8d7da; color:#721c24; padding:20px; font-family:sans-serif;'>";
    echo "<h1>🚨 Laravel Startup Error 🚨</h1>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
    echo "<h2>Stack Trace:</h2>";
    echo "<pre style='background:#fff; padding:10px; border:1px solid #ced4da; overflow:auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</body></html>";
    exit;
}
