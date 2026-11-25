<?php

declare(strict_types=1);

abstract class BaseController
{
    protected function requireLogin(): void
    {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Please log in to continue.');
            $this->redirect('index.php?page=auth');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireLogin();
        if (!Session::isAdmin()) {
            Session::setFlash('error', 'Access denied. Administrator privileges required.');
            $this->redirect('index.php?page=dashboard');
        }
    }

    protected function render(string $viewName, array $data = []): void
    {
        $sessionUser = Session::user();
        $typedUser = $sessionUser ? User::createTypedUser($sessionUser) : null;
        $menuItems = $typedUser ? $typedUser->getMenuItems() : [];
        $flash = Session::getFlash();
        $currentPage = $_GET['page'] ?? 'dashboard';

        extract($data, EXTR_SKIP);

        $viewFile = BASE_PATH . '/app/views/' . $viewName . '.php';
        if (!file_exists($viewFile)) {
            throw new RuntimeException("View not found: {$viewName}");
        }

        require BASE_PATH . '/app/views/layout/header.php';
        require $viewFile;
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}
