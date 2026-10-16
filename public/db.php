<?php
declare(strict_types=1);

/**
 * Returns a shared PDO connection.
 * Connection settings come from environment variables (see docker-compose.yml),
 * with local defaults for XAMPP/WAMP style setups.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $name = getenv('DB_NAME') ?: 'saas_admin';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';

        $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    return $pdo;
}

const PLANS = ['starter', 'growth', 'enterprise'];
const STATUSES = ['active', 'trialing', 'cancelled'];