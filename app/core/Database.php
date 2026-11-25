<?php

declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $configFile = dirname(__DIR__, 2) . '/config/database.php';
        if (!file_exists($configFile)) {
            throw new RuntimeException('Database configuration file not found.');
        }

        $config = require $configFile;
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s',
            $config['driver'] ?? 'pgsql',
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? '5432',
            $config['dbname'] ?? 'memolog'
        );

        try {
            self::$instance = new PDO($dsn, $config['user'] ?? 'postgres', $config['password'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return self::$instance;
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw new RuntimeException('Unable to connect to the PostgreSQL database. Check config/database.php.');
        }
    }
}
