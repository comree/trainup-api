<?php
    // Load environment variables from .env file if it exists
    $env_file = dirname(__FILE__) . '/../.env';
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') === false) {
                list($key, $value) = explode('=', $line, 2);
                putenv(trim($key) . '=' . trim($value));
            }
        }
    }

    // Database configuration from environment variables or defaults
    define('DB_NAME', getenv('DB_NAME') ?: 'train_up');
    define('DB_USER', getenv('DB_USER') ?: 'tableplus');
    define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'password');
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_SOCKET', getenv('DB_SOCKET') ?: null);
?>