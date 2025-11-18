<?php
/**
 * BGAofis - Finance Transactions ID Fix
 *
 * Amaç:
 * - finance_transactions tablosunda id alanının '' (boş string) olması
 *   nedeniyle oluşan PRIMARY KEY çakışmalarını düzeltmek.
 * - Yeni kayıt eklenirken id alanı boş bırakılmış olsa bile otomatik UUID
 *   üretmek (MySQL trigger ile).
 *
 * Kullanım (sunucuda, backend klasöründe):
 *   php finance-transactions-id-fix.php
 */

echo "BGAofis - Finance Transactions ID Fix\n";
echo "=====================================\n\n";

try {
    require_once __DIR__ . '/vendor/autoload.php';

    // Ortam değişkenlerini yükle
    $envPath = __DIR__;
    if (file_exists($envPath . '/.env')) {
        Dotenv\Dotenv::createImmutable($envPath)->safeLoad();
    } elseif (file_exists($envPath . '/.env.production')) {
        Dotenv\Dotenv::createImmutable($envPath, '.env.production')->safeLoad();
    }

    $dbDriver = $_ENV['DB_CONNECTION'] ?? 'mysql';
    $dbHost   = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $dbName   = $_ENV['DB_DATABASE'] ?? '';
    $dbUser   = $_ENV['DB_USERNAME'] ?? '';
    $dbPass   = $_ENV['DB_PASSWORD'] ?? '';

    if ($dbDriver !== 'mysql') {
        throw new RuntimeException('This fix script only supports MySQL.');
    }

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Database connection established.\n";
    echo "Database: {$dbName}\n";
    echo "Host    : {$dbHost}\n\n";

    // Tablo var mı kontrol et
    $tableExists = $pdo->query("SHOW TABLES LIKE 'finance_transactions'")->fetchColumn();
    if (!$tableExists) {
        echo "ERROR: 'finance_transactions' table not found.\n";
        exit(1);
    }

    echo "Fixing existing rows with empty or NULL id...\n";
    $updated = $pdo->exec("UPDATE finance_transactions SET id = UUID() WHERE id IS NULL OR id = ''");
    echo " - Updated {$updated} rows.\n";

    echo "Ensuring id column has no default empty string...\n";
    try {
        $pdo->exec("ALTER TABLE finance_transactions MODIFY id CHAR(36) NOT NULL");
        echo " - Column modified: id CHAR(36) NOT NULL.\n";
    } catch (Throwable $e) {
        echo "WARNING: Could not alter id column: " . $e->getMessage() . "\n";
    }

    echo "Creating BEFORE INSERT trigger to auto-generate UUIDs...\n";
    try {
        $pdo->exec("DROP TRIGGER IF EXISTS trg_finance_transactions_set_id");
    } catch (Throwable $e) {
        // ignore
    }

    try {
        $pdo->exec("
            CREATE TRIGGER trg_finance_transactions_set_id
            BEFORE INSERT ON finance_transactions
            FOR EACH ROW
            SET NEW.id = IF(NEW.id IS NULL OR NEW.id = '', UUID(), NEW.id)
        ");
        echo " - Trigger trg_finance_transactions_set_id created.\n";
    } catch (Throwable $e) {
        echo "WARNING: Could not create trigger: " . $e->getMessage() . "\n";
    }

    echo "\nFinance transactions ID fix completed.\n";
    echo "Artık /api/finance/transactions istekleri için 'Duplicate entry \"\" for key \"PRIMARY\"' hatası alınmamalıdır.\n";
} catch (Throwable $e) {
    echo "\nFATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

