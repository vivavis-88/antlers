<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $directories = [
        BASE_PATH . '/app/core/',
        BASE_PATH . '/app/models/',
        BASE_PATH . '/app/controllers/',
    ];

    foreach ($directories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

Session::start();

try {
    $router = new Router();
    $router->dispatch();
} catch (Throwable $e) {
    error_log('Application error: ' . $e->getMessage());
    http_response_code(500);
    echo 'An unexpected application error occurred. Please check the server logs.';
}
