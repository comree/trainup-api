<?php
    // Load environment variables from .env file if it exists
    $env_file = dirname(__FILE__) . '/../.env';
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos(trim($line), '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                putenv(trim($key) . '=' . trim($value));
            }
        }
    }

    // Prefer DATABASE_URL/POSTGRES_URL from Render if present
    $databaseUrl = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL');
    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbPort = getenv('DB_PORT') ?: '5432';
    $dbName = getenv('DB_NAME') ?: 'train_up';
    $dbUser = getenv('DB_USER') ?: 'postgres';
    $dbPassword = getenv('DB_PASSWORD') ?: '';

    if (!empty($databaseUrl)) {
        $parts = parse_url($databaseUrl);
        if ($parts !== false) {
            if (!empty($parts['host'])) {
                $dbHost = $parts['host'];
            }
            if (!empty($parts['port'])) {
                $dbPort = (string)$parts['port'];
            }
            if (!empty($parts['path'])) {
                $dbName = ltrim($parts['path'], '/');
            }
            if (!empty($parts['user'])) {
                $dbUser = $parts['user'];
            }
            if (isset($parts['pass'])) {
                $dbPassword = $parts['pass'];
            }
        }
    }

    define('DB_DRIVER', getenv('DB_DRIVER') ?: 'pgsql');
    define('DB_NAME', $dbName);
    define('DB_USER', $dbUser);
    define('DB_PASSWORD', $dbPassword);
    define('DB_HOST', $dbHost);
    define('DB_PORT', $dbPort);
    define('DB_SOCKET', getenv('DB_SOCKET') ?: null);
?>