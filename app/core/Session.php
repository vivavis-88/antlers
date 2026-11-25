<?php

declare(strict_types=1);

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLoggedIn(): bool
    {
        self::start();
        return isset($_SESSION['user']['user_id']);
    }

    public static function loginUser(array $user): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'user_id' => (int) $user['user_id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role_name' => $user['role_name'],
            'rider_id' => isset($user['rider_id']) ? (int) $user['rider_id'] : null,
        ];
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function user(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function userId(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['user_id'] : null;
    }

    public static function riderId(): ?int
    {
        $user = self::user();
        return ($user && !empty($user['rider_id'])) ? (int) $user['rider_id'] : null;
    }

    public static function role(): string
    {
        $user = self::user();
        return $user['role_name'] ?? '';
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'Administrator';
    }

    public static function isRider(): bool
    {
        return self::role() === 'Rider';
    }

    public static function setFlash(string $type, string $message): void
    {
        self::start();
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public static function getFlash(): ?array
    {
        self::start();
        if (!isset($_SESSION['flash'])) {
            return null;
        }
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
}
