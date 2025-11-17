<?php
/**
 * BGAofis - Finance & Workflow Tables Quick Fix
 *
 * Purpose:
 * - Create missing tables used by the finance dashboard and workflow module:
 *   finance_transactions, workflow_templates, workflow_steps,
 *   notifications, pending_notifications, audit_logs.
 * - Uses DB credentials from backend .env / .env.production
 * - Safe to run multiple times (CREATE TABLE IF NOT EXISTS).
 *
 * Usage (on server, in backend folder):
 *   php finance-tables-fix.php
 */

echo "BGAofis - Finance & Workflow Tables Quick Fix\n";
echo "============================================\n\n";

try {
    $basePath = __DIR__;
    require_once $basePath . '/vendor/autoload.php';

    // Load environment
    if (file_exists($basePath . '/.env')) {
        Dotenv\Dotenv::createImmutable($basePath)->safeLoad();
    } elseif (file_exists($basePath . '/.env.production')) {
        Dotenv\Dotenv::createImmutable($basePath, '.env.production')->safeLoad();
    }

    $dbDriver = $_ENV['DB_CONNECTION'] ?? 'mysql';
    $dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $dbName = $_ENV['DB_DATABASE'] ?? '';
    $dbUser = $_ENV['DB_USERNAME'] ?? '';
    $dbPass = $_ENV['DB_PASSWORD'] ?? '';

    if ($dbDriver !== 'mysql') {
        throw new RuntimeException('This quick fix currently supports only MySQL.');
    }

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Database connection established.\n";
    echo "Database: {$dbName}\n";
    echo "Host: {$dbHost}\n\n";

    $tables = [
        'finance_transactions' => "
            CREATE TABLE IF NOT EXISTS finance_transactions (
                id CHAR(36) PRIMARY KEY,
                case_id CHAR(36) NULL,
                type ENUM('income','expense') NOT NULL,
                amount DECIMAL(12,2) NOT NULL,
                occurred_on DATE NOT NULL,
                description VARCHAR(255) NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                deleted_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        'workflow_templates' => "
            CREATE TABLE IF NOT EXISTS workflow_templates (
                id CHAR(36) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                case_type VARCHAR(255) NOT NULL,
                tags JSON NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                deleted_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        'workflow_steps' => "
            CREATE TABLE IF NOT EXISTS workflow_steps (
                id CHAR(36) PRIMARY KEY,
                template_id CHAR(36) NOT NULL,
                title VARCHAR(255) NOT NULL,
                is_required TINYINT(1) NOT NULL DEFAULT 1,
                `order` SMALLINT UNSIGNED NOT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                deleted_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        'notifications' => "
            CREATE TABLE IF NOT EXISTS notifications (
                id CHAR(36) PRIMARY KEY,
                user_id CHAR(36) NULL,
                subject VARCHAR(255) NOT NULL,
                payload JSON NOT NULL,
                channels JSON NULL,
                status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                deleted_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        'pending_notifications' => "
            CREATE TABLE IF NOT EXISTS pending_notifications (
                id CHAR(36) PRIMARY KEY,
                payload JSON NOT NULL,
                status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                deleted_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        'audit_logs' => "
            CREATE TABLE IF NOT EXISTS audit_logs (
                id CHAR(36) PRIMARY KEY,
                user_id CHAR(36) NULL,
                entity_type VARCHAR(255) NOT NULL,
                entity_id VARCHAR(255) NOT NULL,
                action VARCHAR(255) NOT NULL,
                metadata JSON NULL,
                ip VARCHAR(45) NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                deleted_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
    ];

    echo "Ensuring required tables exist:\n";
    echo "------------------------------\n";

    foreach ($tables as $name => $sql) {
        echo "Table {$name}: ";
        try {
            $pdo->exec($sql);
            echo "OK\n";
        } catch (Throwable $e) {
            echo "ERROR - " . $e->getMessage() . "\n";
        }
    }

    echo "\nDone. You can now test the dashboard and finance screens.\n";
    echo "If everything works, you may delete this script for security.\n";
} catch (Throwable $e) {
    echo "\nFATAL ERROR: " . $e->getMessage() . "\n";
}

