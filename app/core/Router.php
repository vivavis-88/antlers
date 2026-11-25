<?php

declare(strict_types=1);

class Router
{
    private array $routes = [
        'auth' => AuthController::class,
        'dashboard' => DashboardController::class,
        'deliveries' => DeliveryController::class,
        'riders' => RiderController::class,
        'customers' => CustomerController::class,
        'reports' => ReportController::class,
        'settings' => SettingController::class,
    ];

    public function dispatch(): void
    {
        $page = $_GET['page'] ?? (Session::isLoggedIn() ? 'dashboard' : 'auth');
        $action = $_GET['action'] ?? 'index';

        if (!isset($this->routes[$page])) {
            http_response_code(404);
            $page = Session::isLoggedIn() ? 'dashboard' : 'auth';
            $action = 'index';
        }

        $controllerClass = $this->routes[$page];
        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            $action = 'index';
        }

        $controller->$action();
    }
}
